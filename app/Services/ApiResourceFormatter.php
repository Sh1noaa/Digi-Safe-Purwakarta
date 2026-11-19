<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\ModulEdukasi;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Support\Str;

class ApiResourceFormatter
{
    /**
     * Format data pengguna sesuai konfigurasi privasi dan status kepemilikan profil.
     */
    public static function formatUser(User $user, ?User $viewer = null, bool $includeStats = false): array
    {
        $isOwnProfile = $viewer !== null && (int) $viewer->id === (int) $user->id;
        $privacy = $user->privacy_config;
        $isPrivateForVisitor = ! $isOwnProfile && $privacy['is_private_profile'];

        $hasLoadedScores = $user->relationLoaded('skorGames');
        $totalSkor = $includeStats
            ? (int) ($user->total_skor_game ?? ($hasLoadedScores ? $user->skorGames->sum('skor') : $user->skorGames()->sum('skor')))
            : (int) ($user->total_skor_game ?? 0);
        $topSkor = $includeStats
            ? (int) ($user->top_skor_game ?? ($hasLoadedScores ? $user->skorGames->max('skor_tertinggi') : $user->skorGames()->max('skor_tertinggi')))
            : 0;
        $totalMain = $includeStats
            ? (int) ($user->skor_games_count ?? ($hasLoadedScores ? $user->skorGames->count() : $user->skorGames()->count()))
            : 0;
        $totalThread = $includeStats ? (int) ($user->forums_count ?? $user->forums()->count()) : 0;
        $totalJawaban = $includeStats ? (int) ($user->jawaban_forums_count ?? $user->jawabanForums()->count()) : 0;
        $totalUlasan = $includeStats ? (int) ($user->testimonis_count ?? $user->testimonis()->count()) : 0;

        $data = [
            'id' => (int) $user->id,
            'username' => $user->username,
            'nama_lengkap' => $user->nama_lengkap ?: $user->username,
            'inisial' => $user->inisial,
            'role' => $user->role ?: 'user',
            'specialty_role' => $isPrivateForVisitor ? null : (trim((string) ($user->specialty_role ?? '')) ?: null),
            'role_label' => $isPrivateForVisitor
                ? (app()->getLocale() === 'en' ? 'Private Account' : 'Akun Privat')
                : $user->display_role,
            'is_owner' => $user->isOwner(),
            'is_admin' => $user->isAdmin(),
            'has_admin_access' => $user->hasAdminAccess(),
            'avatar_url' => $user->avatar_url,
            'avatar_border' => $user->avatar_border ?: null,
            'avatar_border_url' => $user->avatar_border_url,
            'banner_url' => $user->banner_url,
            'pastel_class' => $user->pastel_class,
            'is_private_profile' => (bool) $privacy['is_private_profile'],
            'is_private_for_visitor' => $isPrivateForVisitor,
            'is_own_profile' => $isOwnProfile,
            'locale' => $user->locale ?: 'id',
            'theme' => $user->theme ?: 'light',
        ];

        if ($isPrivateForVisitor) {
            return array_merge($data, [
                'email' => null,
                'bio' => null,
                'badges' => [],
                'privacy_settings' => $privacy,
                'stats' => null,
            ]);
        }

        $showEmail = $isOwnProfile && ! $privacy['hide_email'];
        $showLocation = $isOwnProfile || ! $privacy['hide_location'];
        $showBadges = $isOwnProfile || ! $privacy['hide_badges'];
        $showMinigameStats = $isOwnProfile || ! $privacy['hide_minigame_stats'];
        $showActivity = $isOwnProfile || ! $privacy['hide_activity'];

        return array_merge($data, [
            'email' => ($isOwnProfile || $showEmail) ? $user->email : null,
            'email_hidden' => ! $isOwnProfile && $privacy['hide_email'],
            'bio' => $showLocation ? ($user->bio ?: 'Purwakarta, Jawa Barat, Indonesia') : null,
            'location_hidden' => ! $isOwnProfile && $privacy['hide_location'],
            'badges' => $showBadges ? $user->badges_list : [],
            'badges_hidden' => ! $isOwnProfile && $privacy['hide_badges'],
            'privacy_settings' => $isOwnProfile ? $privacy : null,
            'created_at' => $user->created_at?->toIso8601String(),
            'stats' => [
                'total_skor' => $showMinigameStats ? $totalSkor : null,
                'top_skor' => $showMinigameStats ? $topSkor : null,
                'total_main' => $showMinigameStats ? $totalMain : null,
                'total_ulasan' => $showActivity ? $totalUlasan : null,
                'total_diskusi' => $showActivity ? ($totalThread + $totalJawaban) : null,
                'total_thread' => $showActivity ? $totalThread : null,
                'total_jawaban' => $showActivity ? $totalJawaban : null,
                'minigame_hidden' => ! $isOwnProfile && $privacy['hide_minigame_stats'],
                'activity_hidden' => ! $isOwnProfile && $privacy['hide_activity'],
            ],
        ]);
    }

    /**
     * Format satu kartu modul edukasi agar konsisten dengan web bento grid.
     */
    public static function formatModule(ModulEdukasi $modul, int $idx = 0): array
    {
        $variants = ['solid', 'chart', 'glass', 'glass-dark', 'solid', 'featured'];
        $icons = ['journal', 'shield', 'check', 'globe', 'users', 'spark'];

        $fullKonten = (string) ($modul->konten ?: '');
        $translated = BilingualHelper::translateModule(
            (int) $modul->id,
            (string) $modul->judul,
            Str::limit(strip_tags($fullKonten), 115),
            $fullKonten,
            (string) ($modul->kategori ?: 'Literasi Siber')
        );

        return [
            'id' => (int) $modul->id,
            'title' => $translated['title'],
            'description' => $translated['description'],
            'konten' => $translated['konten'],
            'type' => $modul->effective_type,
            'url' => $modul->effective_url ?: '',
            'gambar_url' => $modul->gambar_url,
            'stat' => (string) (($idx + 1) * 4 + 4),
            'tag' => $translated['tag'],
            'kategori' => $modul->kategori ?: 'Literasi Siber',
            'variant' => $variants[$idx % count($variants)],
            'icon' => $icons[$idx % count($icons)],
            'created_at' => $modul->created_at?->toIso8601String(),
        ];
    }

    /**
     * Format satu thread forum beserta seluruh balasan dan status vote / Most Helpful.
     */
    public static function formatForum(Forum $item, ?int $currentUserId): array
    {
        $isEn = app()->getLocale() === 'en';
        $authorName = $item->user?->nama_lengkap ?: ($item->user?->username ?: ($isEn ? 'Purwakarta Citizen' : 'Warga Purwakarta'));

        $bestJawabanId = null;
        $bestUpCount = 0;
        $bestNetScore = 0;
        $totalUp = 0;
        $totalDown = 0;

        $scoredReplies = $item->jawaban->map(function (JawabanForum $ans) use (
            $currentUserId,
            &$totalUp,
            &$totalDown,
            &$bestJawabanId,
            &$bestUpCount,
            &$bestNetScore
        ) {
            $upCount = $ans->votes->where('vote_type', 'up')->count();
            $downCount = $ans->votes->where('vote_type', 'down')->count();
            $netScore = $upCount - $downCount;
            $userVote = $currentUserId
                ? ($ans->votes->firstWhere('user_id', $currentUserId)?->vote_type ?? null)
                : null;

            $totalUp += $upCount;
            $totalDown += $downCount;

            if ($upCount > 0 && $netScore > 0) {
                if (
                    $bestJawabanId === null
                    || $upCount > $bestUpCount
                    || ($upCount === $bestUpCount && $netScore > $bestNetScore)
                ) {
                    $bestJawabanId = $ans->id;
                    $bestUpCount = $upCount;
                    $bestNetScore = $netScore;
                }
            }

            return [
                'model' => $ans,
                'up_count' => $upCount,
                'down_count' => $downCount,
                'net_score' => $netScore,
                'user_vote' => $userVote,
            ];
        });

        $viewerIsAdmin = false;
        if ($currentUserId) {
            $viewerUser = User::find($currentUserId);
            $viewerIsAdmin = $viewerUser ? $viewerUser->hasAdminAccess() : false;
        }

        $formattedJawaban = $scoredReplies->map(function (array $entry) use ($bestJawabanId, $isEn, $currentUserId, $viewerIsAdmin) {
            /** @var JawabanForum $ans */
            $ans = $entry['model'];
            $ansName = $ans->user?->nama_lengkap ?: ($ans->user?->username ?: ($isEn ? 'Community Member' : 'Anggota Komunitas'));
            $isOwner = (bool) $ans->user?->isOwner();

            return [
                'id' => (int) $ans->id,
                'user_id' => $ans->user_id ? (int) $ans->user_id : null,
                'jawaban' => $ans->jawaban,
                'is_owner' => $isOwner,
                'is_admin' => ! $isOwner && (bool) ($ans->is_admin || $ans->user?->isAdmin()),
                'waktu' => $ans->created_at ? $ans->created_at->diffForHumans() : ($isEn ? 'Just now' : 'Baru saja'),
                'user_name' => $ansName,
                'user_initial' => mb_strtoupper(mb_substr(trim($ansName), 0, 1)),
                'user_avatar' => $ans->user?->avatar_url,
                'user_border_url' => $ans->user?->avatar_border_url,
                'user_pastel' => $ans->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
                'up_count' => $entry['up_count'],
                'down_count' => $entry['down_count'],
                'net_score' => $entry['net_score'],
                'user_vote' => $entry['user_vote'],
                'is_most_helpful' => $bestJawabanId !== null && $ans->id === $bestJawabanId,
                'can_manage' => $currentUserId !== null && ((int) $ans->user_id === (int) $currentUserId || $viewerIsAdmin),
            ];
        })->values()->all();

        $isOwnerAuthor = (bool) $item->user?->isOwner();

        return [
            'id' => (int) $item->id,
            'user_id' => $item->user_id ? (int) $item->user_id : null,
            'judul' => $item->judul,
            'pertanyaan' => $item->pertanyaan,
            'kategori' => $item->kategori ?: 'umum',
            'status' => $item->status ?: 'terbuka',
            'is_starred' => (bool) $item->is_starred,
            'views' => (int) ($item->views ?? 1),
            'waktu' => $item->created_at ? $item->created_at->diffForHumans() : ($isEn ? 'Just now' : 'Baru saja'),
            'author_name' => $authorName,
            'author_initial' => mb_strtoupper(mb_substr(trim($authorName), 0, 1)),
            'author_avatar' => $item->user?->avatar_url,
            'author_border_url' => $item->user?->avatar_border_url,
            'author_is_owner' => $isOwnerAuthor,
            'author_is_admin' => ! $isOwnerAuthor && (bool) $item->user?->isAdmin(),
            'author_pastel' => $item->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
            'can_manage' => $currentUserId !== null && ((int) $item->user_id === (int) $currentUserId || $viewerIsAdmin),
            'jawaban_count' => count($formattedJawaban),
            'total_up' => $totalUp,
            'total_down' => $totalDown,
            'has_most_helpful' => $bestJawabanId !== null,
            'jawaban' => $formattedJawaban,
        ];
    }

    /**
     * Format satu item testimoni.
     */
    public static function formatTestimoni(Testimoni $t): array
    {
        $isEn = app()->getLocale() === 'en';

        return [
            'id' => (int) $t->id,
            'user_id' => (! $t->is_anonymous && $t->user_id) ? (int) $t->user_id : null,
            'nama' => $t->nama_tampil,
            'inisial' => $t->inisial_tampil,
            'avatar' => $t->avatar_tampil,
            'border_url' => (! $t->is_anonymous && $t->user) ? $t->user->avatar_border_url : null,
            'pastel_class' => $t->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
            'is_owner' => (! $t->is_anonymous && (bool) $t->user?->isOwner()),
            'is_anonymous' => (bool) $t->is_anonymous,
            'rating' => (int) ($t->rating ?: 5),
            'judul' => $t->judul,
            'cerita' => $t->cerita,
            'waktu' => $t->created_at ? $t->created_at->diffForHumans() : ($isEn ? 'Just now' : 'Baru saja'),
        ];
    }
}

