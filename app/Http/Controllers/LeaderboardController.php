<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\SkorGame;
use App\Models\User;
use App\Services\AvatarBorderService;
use App\Services\LeaderboardService;
use App\Services\SocialSchemaHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(): View
    {
        SocialSchemaHelper::ensureReady();
        $isEn = app()->getLocale() === 'en';

        $leaderboardData = LeaderboardService::allCategories(10);

        // Statistik nyata komunitas langsung dari database
        $communityStats = [
            'total_users' => User::humans()->count(),
            'total_sessions' => Schema::hasTable('skor_game') ? SkorGame::count() : 0,
            'total_discussions' => (Schema::hasTable('forum') ? Forum::count() : 0)
                + (Schema::hasTable('jawaban_forum') ? JawabanForum::count() : 0),
            'highest_score' => Schema::hasTable('skor_game') ? (int) (SkorGame::max('skor_tertinggi') ?? 0) : 0,
        ];

        // Rekor & ringkasan nyata per arena minigame
        $arenaDefs = [
            [
                'slug' => 'phishing',
                'aliases' => ['phishing', 'level1'],
                'title' => 'Phishing Hunter',
                'tag' => $isEn ? 'Message Inspection' : 'Analisis Pesan',
            ],
            [
                'slug' => 'cyber-defender',
                'aliases' => ['cyber-defender', 'level2'],
                'title' => 'Cyber Defender',
                'tag' => $isEn ? 'System Defense' : 'Pertahanan Sistem',
            ],
            [
                'slug' => 'link-trap',
                'aliases' => ['link-trap', 'level3'],
                'title' => 'Link or Trap?',
                'tag' => $isEn ? 'URL Verification' : 'Inspeksi Tautan',
            ],
        ];

        $arenaStats = [];
        $allScores = Schema::hasTable('skor_game')
            ? SkorGame::with('user')->orderByDesc('skor')->orderByDesc('id')->get()
            : collect();

        foreach ($arenaDefs as $arena) {
            $subset = $allScores->filter(fn (SkorGame $s) => in_array($s->game_type, $arena['aliases'], true));
            /** @var SkorGame|null $championRow */
            $championRow = $subset->first(fn (SkorGame $s) => $s->user !== null && ! $s->user->isBot());

            $arenaStats[] = [
                'slug' => $arena['slug'],
                'title' => $arena['title'],
                'tag' => $arena['tag'],
                'play_url' => route('minigame.play', $arena['slug']),
                'total_plays' => $subset->count(),
                'highest_score' => (int) ($subset->max('skor_tertinggi') ?? 0),
                'avg_score' => $subset->count() > 0 ? (int) round($subset->avg('skor')) : 0,
                'champion' => $championRow?->user,
                'champion_score' => $championRow ? (int) $championRow->skor : 0,
            ];
        }

        // Aktivitas sesi skor terbaru
        $recentRuns = Schema::hasTable('skor_game')
            ? SkorGame::with('user')
                ->whereHas('user')
                ->orderByDesc('played_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get()
                ->filter(fn (SkorGame $s) => $s->user && ! $s->user->isBot())
                ->values()
            : collect();

        // Katalog 5 border avatar dan jumlah pemakainya
        $borderCatalog = AvatarBorderService::publicCatalog();

        // Ringkasan posisi & progres border untuk user yang sedang login
        $mySummary = null;
        if (Auth::check()) {
            /** @var User $me */
            $me = Auth::user();
            $myBorders = AvatarBorderService::evaluateForUser($me);
            $unlockedCount = collect($myBorders)->where('unlocked', true)->count();

            $myTopScore = Schema::hasTable('skor_game')
                ? (int) (SkorGame::where('user_id', $me->id)->max('skor_tertinggi') ?? 0)
                : 0;
            $myPlays = Schema::hasTable('skor_game')
                ? (int) SkorGame::where('user_id', $me->id)->count()
                : 0;

            $myRank = null;
            $myRankCategory = null;
            $fullStandings = LeaderboardService::allCategories(500);
            $priorityKeys = ['minigame_benar', 'streak', 'wa_accuracy', 'forum', 'badges'];
            $categoriesByKey = collect($fullStandings['categories'] ?? [])->keyBy('key');

            foreach ($priorityKeys as $catKey) {
                $cat = $categoriesByKey->get($catKey);
                if (! $cat || empty($cat['rows'])) {
                    continue;
                }
                foreach ($cat['rows'] as $row) {
                    if ((int) ($row['id'] ?? 0) === (int) $me->id) {
                        $myRank = (int) $row['rank'];
                        $myRankCategory = (string) ($cat['label'] ?? '');
                        break 2;
                    }
                }
            }

            $mySummary = [
                'user' => $me,
                'rank' => $myRank,
                'rank_category' => $myRankCategory,
                'top_score' => $myTopScore,
                'plays' => $myPlays,
                'unlocked_borders' => $unlockedCount,
                'total_borders' => count($myBorders),
            ];
        }

        return view('leaderboard', compact(
            'leaderboardData',
            'communityStats',
            'arenaStats',
            'recentRuns',
            'borderCatalog',
            'mySummary'
        ));
    }
}
