<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\SkorGame;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaderboardService
{
    public static function allCategories(int $limit = 8): array
    {
        SocialSchemaHelper::ensureReady();
        $isEn = app()->getLocale() === 'en';

        $users = User::humans()->get();
        $scoresByUser = Schema::hasTable('skor_game') ? SkorGame::all()->groupBy('user_id') : collect();
        $answersByUser = Schema::hasTable('jawaban_forum') ? JawabanForum::all()->groupBy('user_id') : collect();
        $threadsByUser = Schema::hasTable('forum') ? Forum::all()->groupBy('user_id') : collect();
        $streaksByUser = Schema::hasTable('user_daily_streaks')
            ? DB::table('user_daily_streaks')->get()->keyBy('user_id')
            : collect();

        $upvotesByAnswerId = collect();
        if (Schema::hasTable('jawaban_forum_votes')) {
            $voteCol = Schema::hasColumn('jawaban_forum_votes', 'jawaban_id') ? 'jawaban_id' : 'jawaban_forum_id';
            $upvotesByAnswerId = DB::table('jawaban_forum_votes')
                ->where('vote_type', 'up')
                ->select($voteCol, DB::raw('COUNT(*) as total'))
                ->groupBy($voteCol)
                ->pluck('total', $voteCol);
        }

        $profiles = [];

        foreach ($users as $user) {
            $uScores = $scoresByUser->get($user->id, collect());
            $totalBenar = 0;
            $totalSalah = 0;
            $topScore = 0;
            $totalScore = 0;

            foreach ($uScores as $row) {
                $b = ((int) ($row->benar ?? 0) > 0 || (int) ($row->salah ?? 0) > 0)
                    ? (int) $row->benar
                    : max(1, (int) round(max(0, $row->skor) / 10));
                $s = ((int) ($row->benar ?? 0) > 0 || (int) ($row->salah ?? 0) > 0)
                    ? (int) $row->salah
                    : ($row->skor >= 80 ? 1 : ($row->skor >= 50 ? 2 : 3));

                $totalBenar += $b;
                $totalSalah += $s;
                $totalScore += (int) $row->skor;
                if ((int) $row->skor_tertinggi > $topScore) {
                    $topScore = (int) $row->skor_tertinggi;
                }
            }

            $totalAttempts = max(1, $totalBenar + $totalSalah);
            $accuracy = $uScores->isNotEmpty() ? (int) round(($totalBenar / $totalAttempts) * 100) : 0;

            $waScores = $uScores->filter(fn ($row) => ($row->game_type ?? '') === 'wa-simulator');
            $waBenar = 0;
            $waSalah = 0;
            foreach ($waScores as $wRow) {
                $waBenar += (int) ($wRow->benar ?? 0);
                $waSalah += (int) ($wRow->salah ?? 0);
            }
            $waTotalEvents = max(1, $waBenar + $waSalah);
            $waAccuracy = $waScores->isNotEmpty()
                ? (int) round(($waBenar / $waTotalEvents) * 100)
                : 0;

            $uStreak = $streaksByUser->get($user->id);
            $currentStreak = (int) ($uStreak->current_streak ?? 0);
            $longestStreak = max($currentStreak, (int) ($uStreak->longest_streak ?? 0));
            $streakTotalCompleted = (int) ($uStreak->total_completed ?? 0);

            $uAnswers = $answersByUser->get($user->id, collect());
            $uThreads = $threadsByUser->get($user->id, collect());
            $helpfulVotes = 0;
            foreach ($uAnswers as $ans) {
                $helpfulVotes += (int) ($upvotesByAnswerId->get($ans->id) ?? 0);
            }

            $badges = $user->badges_list;
            $privacy = $user->privacy_config;

            $profiles[$user->id] = [
                'id' => (int) $user->id,
                'nama' => $user->nama_lengkap ?: $user->username,
                'username' => $user->username,
                'inisial' => $user->inisial,
                'avatar_url' => $user->avatar_url,
                'avatar_border_url' => $user->avatar_border_url,
                'banner_url' => $user->banner_url,
                'pastel_class' => $user->pastel_class,
                'specialty_role' => $privacy['is_private_profile'] ? null : (trim((string) ($user->specialty_role ?? '')) ?: $user->role_label),
                'bio' => $privacy['is_private_profile'] ? ($isEn ? 'Private Digi-Safe account.' : 'Akun Digi-Safe dalam mode privat.') : ($user->bio ?: 'Warga Siber Purwakarta'),
                'profile_url' => route('profile.show', $user->id),
                'matches_played' => $uScores->count(),
                'top_score' => $topScore,
                'total_score' => $totalScore,
                'total_benar' => $totalBenar,
                'total_salah' => $totalSalah,
                'accuracy' => $accuracy,
                'wa_sessions' => $waScores->count(),
                'wa_benar' => $waBenar,
                'wa_salah' => $waSalah,
                'wa_accuracy' => $waAccuracy,
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'streak_total' => $streakTotalCompleted,
                'total_jawaban' => $uAnswers->count(),
                'total_threads' => $uThreads->count(),
                'helpful_votes' => $helpfulVotes,
                'badges_count' => count($badges),
                'badges' => $badges,
            ];
        }

        // 1. Kategori Minigame Benar Terbanyak
        $benarRows = collect($profiles)
            ->filter(fn ($p) => $p['matches_played'] > 0)
            ->sort(function ($a, $b) {
                return [$b['total_benar'], $b['accuracy'], $b['top_score']]
                    <=> [$a['total_benar'], $a['accuracy'], $a['top_score']];
            })
            ->take($limit)
            ->values()
            ->map(function ($p, $idx) use ($isEn) {
                $stars = max(1, min(5, (int) round($p['accuracy'] / 20)));
                return array_merge($p, [
                    'rank' => $idx + 1,
                    'metric_main' => $p['total_benar'] . ($isEn ? ' Correct' : ' Benar'),
                    'metric_mobile' => $p['total_benar'] . ' pts',
                    'stars' => $stars,
                    'subtitle' => ($p['specialty_role'] ?: 'Warga Siber') . ' • ' . $p['accuracy'] . '% ' . ($isEn ? 'accuracy' : 'akurat'),
                ]);
            })->all();

        // 2. Kategori Streak Terbanyak (Tantangan Harian)
        $streakRows = collect($profiles)
            ->filter(fn ($p) => $p['longest_streak'] > 0 || $p['current_streak'] > 0)
            ->sort(function ($a, $b) {
                return [$b['longest_streak'], $b['current_streak'], $b['streak_total']]
                    <=> [$a['longest_streak'], $a['current_streak'], $a['streak_total']];
            })
            ->take($limit)
            ->values()
            ->map(function ($p, $idx) use ($isEn) {
                $stars = max(1, min(5, (int) ceil($p['longest_streak'] / 2)));
                $activeText = $p['current_streak'] > 0
                    ? ($isEn ? "Active {$p['current_streak']}d" : "Aktif {$p['current_streak']} hari")
                    : ($isEn ? 'Resting' : 'Istirahat');
                return array_merge($p, [
                    'rank' => $idx + 1,
                    'metric_main' => $p['longest_streak'] . ($isEn ? ' Days' : ' Hari'),
                    'metric_mobile' => $p['longest_streak'] . ($isEn ? 'd' : ' hr'),
                    'stars' => $stars,
                    'subtitle' => $activeText . ' • ' . $p['streak_total'] . ($isEn ? ' daily solved' : ' tantangan selesai'),
                ]);
            })->all();

        // 3. Kategori Tingkat Akurasi Kebenaran Simulasi WA
        $waRows = collect($profiles)
            ->filter(fn ($p) => $p['wa_sessions'] > 0)
            ->sort(function ($a, $b) {
                return [$b['wa_accuracy'], $b['wa_benar'], $b['wa_sessions']]
                    <=> [$a['wa_accuracy'], $a['wa_benar'], $a['wa_sessions']];
            })
            ->take($limit)
            ->values()
            ->map(function ($p, $idx) use ($isEn) {
                $stars = max(1, min(5, (int) round($p['wa_accuracy'] / 20)));
                return array_merge($p, [
                    'rank' => $idx + 1,
                    'metric_main' => $p['wa_accuracy'] . '%',
                    'metric_mobile' => $p['wa_accuracy'] . '%',
                    'stars' => $stars,
                    'subtitle' => $p['wa_sessions'] . ($isEn ? ' WA chats • ' : ' chat WA • ') . $p['wa_benar'] . ($isEn ? ' safe' : ' aman'),
                ]);
            })->all();

        // 4. Kategori Penjawab Forum Terbanyak
        $forumRows = collect($profiles)
            ->filter(fn ($p) => ($p['total_jawaban'] + $p['total_threads']) > 0)
            ->sort(function ($a, $b) {
                return [$b['total_jawaban'], $b['helpful_votes'], $b['total_threads']]
                    <=> [$a['total_jawaban'], $a['helpful_votes'], $a['total_threads']];
            })
            ->take($limit)
            ->values()
            ->map(function ($p, $idx) use ($isEn) {
                $stars = max(1, min(5, $p['total_jawaban'] >= 5 ? 5 : max(2, $p['total_jawaban'] + 1)));
                return array_merge($p, [
                    'rank' => $idx + 1,
                    'metric_main' => $p['total_jawaban'] . ($isEn ? ' Replies' : ' Jawaban'),
                    'metric_mobile' => $p['total_jawaban'] . ($isEn ? ' rep' : ' jwb'),
                    'stars' => $stars,
                    'subtitle' => ($p['specialty_role'] ?: 'Warga Siber') . ' • ' . $p['helpful_votes'] . ($isEn ? ' helpful votes' : ' membantu'),
                ]);
            })->all();

        // 5. Kategori Lencana Terbanyak
        $badgeRows = collect($profiles)
            ->filter(fn ($p) => $p['badges_count'] > 0)
            ->sort(function ($a, $b) {
                return [$b['badges_count'], $b['top_score'], $b['total_score']]
                    <=> [$a['badges_count'], $a['top_score'], $a['total_score']];
            })
            ->take($limit)
            ->values()
            ->map(function ($p, $idx) use ($isEn) {
                $stars = max(1, min(5, $p['badges_count']));
                return array_merge($p, [
                    'rank' => $idx + 1,
                    'metric_main' => $p['badges_count'] . ($isEn ? ' Badges' : ' Lencana'),
                    'metric_mobile' => $p['badges_count'] . ($isEn ? ' bdg' : ' lcn'),
                    'stars' => $stars,
                    'subtitle' => ($p['specialty_role'] ?: 'Warga Siber') . ' • Top ' . $p['top_score'] . ' pts',
                ]);
            })->all();

        return [
            'categories' => [
                [
                    'key' => 'minigame_benar',
                    'label' => $isEn ? 'Correct Minigame' : 'Minigame Benar',
                    'short' => $isEn ? 'Correct' : 'Benar',
                    'dial_deg' => -60,
                    'rows' => $benarRows,
                ],
                [
                    'key' => 'streak',
                    'label' => $isEn ? 'Highest Streak' : 'Streak Terbanyak',
                    'short' => 'Streak',
                    'dial_deg' => -30,
                    'rows' => $streakRows,
                ],
                [
                    'key' => 'wa_accuracy',
                    'label' => $isEn ? 'WA Accuracy' : 'Akurasi WA',
                    'short' => 'WA %',
                    'dial_deg' => 0,
                    'rows' => $waRows,
                ],
                [
                    'key' => 'forum',
                    'label' => $isEn ? 'Forum Responders' : 'Penjawab Forum',
                    'short' => 'Forum',
                    'dial_deg' => 30,
                    'rows' => $forumRows,
                ],
                [
                    'key' => 'badges',
                    'label' => $isEn ? 'Most Badges' : 'Lencana Terbanyak',
                    'short' => $isEn ? 'Badges' : 'Lencana',
                    'dial_deg' => 60,
                    'rows' => $badgeRows,
                ],
            ],
        ];
    }
}
