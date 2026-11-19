<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\SkorGame;
use App\Models\Testimoni;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Support\Facades\Schema;

class AvatarBorderService
{
    public const DEFINITIONS = [
        'aqua_crest' => [
            'key' => 'aqua_crest',
            'tier' => 1,
            'name_id' => 'Aqua Crest',
            'name_en' => 'Aqua Crest',
            'subtitle_id' => 'Langkah Pertama',
            'subtitle_en' => 'First Wave',
            'file' => 'images/borders/aqua-crest.png',
            'req_id' => 'Selesaikan minimal 1 sesi Minigame.',
            'req_en' => 'Complete at least 1 Minigame session.',
        ],
        'astral_orbit' => [
            'key' => 'astral_orbit',
            'tier' => 2,
            'name_id' => 'Astral Orbit',
            'name_en' => 'Astral Orbit',
            'subtitle_id' => 'Suara Komunitas',
            'subtitle_en' => 'Community Voice',
            'file' => 'images/borders/astral-orbit.png',
            'req_id' => 'Kirim minimal 1 diskusi/balasan Forum atau 1 ulasan Testimoni.',
            'req_en' => 'Post at least 1 Forum topic/reply or 1 community review.',
        ],
        'cosmic_ring' => [
            'key' => 'cosmic_ring',
            'tier' => 3,
            'name_id' => 'Cosmic Horizon',
            'name_en' => 'Cosmic Horizon',
            'subtitle_id' => 'Penjaga Siber',
            'subtitle_en' => 'Cyber Guardian',
            'file' => 'images/borders/cosmic-ring.png',
            'req_id' => 'Raih skor tertinggi Minigame minimal 80.',
            'req_en' => 'Reach a Minigame high score of at least 80.',
        ],
        'crimson_flare' => [
            'key' => 'crimson_flare',
            'tier' => 4,
            'name_id' => 'Crimson Sentinel',
            'name_en' => 'Crimson Sentinel',
            'subtitle_id' => 'Elit Keamanan',
            'subtitle_en' => 'Security Elite',
            'file' => 'images/borders/crimson-flare.png',
            'req_id' => 'Raih skor tertinggi minimal 100 dan mainkan minimal 3 sesi Minigame.',
            'req_en' => 'Reach a high score of 100+ and play at least 3 Minigame sessions.',
        ],
        'celestial_tide' => [
            'key' => 'celestial_tide',
            'tier' => 5,
            'name_id' => 'Obsidian Astrolabe',
            'name_en' => 'Obsidian Astrolabe',
            'subtitle_id' => 'Legenda Digi-Safe',
            'subtitle_en' => 'Digi-Safe Legend',
            'file' => 'images/borders/celestial-tide.png',
            'req_id' => 'Kumpulkan minimal 3 lencana prestasi atau 3 pengikut (Followers).',
            'req_en' => 'Collect at least 3 achievement badges or 3 Followers.',
        ],
    ];

    /**
     * Daftar kunci border yang sah.
     *
     * @return array<int, string>
     */
    public static function validKeys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * Kembalikan URL publik PNG transparan untuk border yang dipilih, atau null bila tidak memakai border.
     */
    public static function imageUrl(?string $key): ?string
    {
        if (! $key || ! isset(self::DEFINITIONS[$key])) {
            return null;
        }

        $relative = self::DEFINITIONS[$key]['file'];
        if (! is_file(public_path($relative))) {
            return null;
        }

        return asset($relative);
    }

    /**
     * Ambil nama tampilan border sesuai bahasa aktif.
     */
    public static function label(?string $key): ?string
    {
        if (! $key || ! isset(self::DEFINITIONS[$key])) {
            return null;
        }

        $isEn = app()->getLocale() === 'en';

        return $isEn ? self::DEFINITIONS[$key]['name_en'] : self::DEFINITIONS[$key]['name_id'];
    }

    /**
     * Hitung metrik nyata user dari database untuk mengevaluasi syarat unlock 5 border.
     *
     * @return array<string, int>
     */
    public static function collectUserMetrics(User $user): array
    {
        $minigamePlays = 0;
        $bestScore = 0;
        if (Schema::hasTable('skor_game')) {
            $minigamePlays = (int) SkorGame::where('user_id', $user->id)->count();
            $bestScore = (int) (SkorGame::where('user_id', $user->id)->max('skor_tertinggi') ?? 0);
        }

        $forumCount = Schema::hasTable('forum') ? (int) Forum::where('user_id', $user->id)->count() : 0;
        $replyCount = Schema::hasTable('jawaban_forum') ? (int) JawabanForum::where('user_id', $user->id)->count() : 0;
        $testiCount = Schema::hasTable('testimoni') ? (int) Testimoni::where('user_id', $user->id)->count() : 0;

        $followersCount = Schema::hasTable('user_follows')
            ? (int) UserFollow::where('following_id', $user->id)->count()
            : 0;

        $badgesCount = count(array_filter(
            $user->badges_list,
            fn (string $b) => $b !== 'New Player'
        ));

        return [
            'minigame_plays' => $minigamePlays,
            'best_score' => $bestScore,
            'community_posts' => $forumCount + $replyCount + $testiCount,
            'badges_count' => $badgesCount,
            'followers_count' => $followersCount,
        ];
    }

    /**
     * Evaluasi seluruh 5 border beserta status terkunci/terbuka dan progres real untuk user.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function evaluateForUser(User $user): array
    {
        $metrics = self::collectUserMetrics($user);
        $isEn = app()->getLocale() === 'en';
        $isOwner = $user->isOwner();
        $equipped = $user->avatar_border ?: null;

        $results = [];
        foreach (self::DEFINITIONS as $key => $def) {
            [$unlocked, $percent, $progressLabel] = self::checkUnlock($key, $metrics, $isOwner, $isEn);

            $results[] = [
                'key' => $key,
                'tier' => $def['tier'],
                'name' => $isEn ? $def['name_en'] : $def['name_id'],
                'subtitle' => $isEn ? $def['subtitle_en'] : $def['subtitle_id'],
                'image_url' => asset($def['file']),
                'requirement' => $isEn ? $def['req_en'] : $def['req_id'],
                'unlocked' => $unlocked,
                'progress_percent' => $percent,
                'progress_label' => $progressLabel,
                'is_equipped' => $equipped === $key,
            ];
        }

        return $results;
    }

    /**
     * Verifikasi sisi server apakah user berhak memasang border tertentu.
     */
    public static function isUnlockedForUser(User $user, string $key): bool
    {
        if (! isset(self::DEFINITIONS[$key])) {
            return false;
        }

        $metrics = self::collectUserMetrics($user);
        [$unlocked] = self::checkUnlock($key, $metrics, $user->isOwner(), false);

        return $unlocked;
    }

    /**
     * @param array<string, int> $m
     * @return array{0: bool, 1: int, 2: string}
     */
    private static function checkUnlock(string $key, array $m, bool $isOwner, bool $isEn): array
    {
        return match ($key) {
            'aqua_crest' => [
                $m['minigame_plays'] >= 1,
                min(100, (int) round(min(1, $m['minigame_plays']) * 100)),
                $isEn
                    ? min(1, $m['minigame_plays']) . ' / 1 minigame played'
                    : min(1, $m['minigame_plays']) . ' / 1 sesi minigame',
            ],
            'astral_orbit' => [
                $m['community_posts'] >= 1,
                min(100, (int) round(min(1, $m['community_posts']) * 100)),
                $isEn
                    ? min(1, $m['community_posts']) . ' / 1 forum or review'
                    : min(1, $m['community_posts']) . ' / 1 kontribusi forum atau ulasan',
            ],
            'cosmic_ring' => [
                $m['best_score'] >= 80,
                min(100, (int) round((min(80, $m['best_score']) / 80) * 100)),
                $isEn
                    ? 'Best score ' . min(80, $m['best_score']) . ' / 80'
                    : 'Skor terbaik ' . min(80, $m['best_score']) . ' / 80',
            ],
            'crimson_flare' => [
                $m['best_score'] >= 100 && $m['minigame_plays'] >= 3,
                min(100, (int) round(((min(100, $m['best_score']) / 100) * 0.5 + (min(3, $m['minigame_plays']) / 3) * 0.5) * 100)),
                $isEn
                    ? 'Score ' . min(100, $m['best_score']) . '/100, ' . min(3, $m['minigame_plays']) . '/3 games'
                    : 'Skor ' . min(100, $m['best_score']) . '/100, ' . min(3, $m['minigame_plays']) . '/3 sesi',
            ],
            'celestial_tide' => [
                $isOwner || $m['badges_count'] >= 3 || $m['followers_count'] >= 3,
                ($isOwner || $m['badges_count'] >= 3 || $m['followers_count'] >= 3)
                    ? 100
                    : min(99, (int) round((max($m['badges_count'], $m['followers_count']) / 3) * 100)),
                $isOwner
                    ? ($isEn ? 'Unlocked for Owner' : 'Terbuka khusus Owner')
                    : ($isEn
                        ? min(3, $m['badges_count']) . '/3 badges or ' . min(3, $m['followers_count']) . '/3 followers'
                        : min(3, $m['badges_count']) . '/3 lencana atau ' . min(3, $m['followers_count']) . '/3 pengikut'),
            ],
            default => [false, 0, '0%'],
        };
    }

    /**
     * Katalog publik untuk ditampilkan di halaman Leaderboard (tanpa bergantung pada user tertentu).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function publicCatalog(): array
    {
        $isEn = app()->getLocale() === 'en';
        $items = [];
        foreach (self::DEFINITIONS as $key => $def) {
            $equippedCount = 0;
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar_border')) {
                $equippedCount = (int) User::where('avatar_border', $key)->count();
            }
            $items[] = [
                'key' => $key,
                'tier' => $def['tier'],
                'name' => $isEn ? $def['name_en'] : $def['name_id'],
                'subtitle' => $isEn ? $def['subtitle_en'] : $def['subtitle_id'],
                'image_url' => asset($def['file']),
                'requirement' => $isEn ? $def['req_en'] : $def['req_id'],
                'equipped_count' => $equippedCount,
            ];
        }

        return $items;
    }
}
