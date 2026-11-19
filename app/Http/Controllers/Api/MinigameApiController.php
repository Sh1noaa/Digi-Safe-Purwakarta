<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MinigameLevel;
use App\Models\SkorGame;
use App\Models\SoalMinigame;
use App\Services\BilingualHelper;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MinigameApiController extends Controller
{
    public function index(): JsonResponse
    {
        MinigameLevel::ensureInitialized();

        $levels = MinigameLevel::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (MinigameLevel $lvl) {
                $t = BilingualHelper::translateLevel($lvl->slug, $lvl->nama_level, (string) $lvl->deskripsi);

                return [
                    'id' => (int) $lvl->id,
                    'slug' => $lvl->slug,
                    'nama_level' => $t['nama'],
                    'badge_label' => $lvl->badge_label,
                    'deskripsi' => $t['deskripsi'],
                    'waktu_detik' => (int) $lvl->waktu_detik,
                    'poin_benar' => (int) $lvl->poin_benar,
                    'penalti_salah' => (int) $lvl->penalti_salah,
                    'hp_awal' => (int) $lvl->hp_awal,
                    'urutan' => (int) $lvl->urutan,
                    'warna' => $lvl->warna,
                ];
            })
            ->values();

        $isEn = BilingualHelper::isEnglish();
        $levelSummary = $levels->pluck('nama_level')->implode(' • ');

        $games = [
            [
                'slug' => 'phishing',
                'title' => 'Phishing Hunter',
                'category' => $isEn ? 'MESSAGE ANALYSIS' : 'ANALISIS PESAN',
                'level' => $levelSummary ?: 'Easy • Medium • Hard • Expert • Master',
                'description' => $isEn
                    ? 'Inspect sample emails and chat messages across levels to distinguish official alerts from phishing traps.'
                    : 'Analisis contoh email dan pesan chat untuk membedakan informasi resmi atau jebakan phishing di berbagai level.',
            ],
            [
                'slug' => 'cyber-defender',
                'title' => 'Cyber Defender',
                'category' => $isEn ? 'SYSTEM DEFENSE' : 'PERTAHANAN SISTEM',
                'level' => $levels->count() . ($isEn ? ' Defense Levels' : ' Level Pertahanan'),
                'description' => $isEn
                    ? 'Protect the core system from incoming viruses, malware, and social engineering attacks at your chosen difficulty.'
                    : 'Lindungi sistem inti dari gelombang virus, malware, dan rekayasa sosial sesuai tingkat kesulitan yang dipilih.',
            ],
            [
                'slug' => 'link-trap',
                'title' => 'Link or Trap?',
                'category' => $isEn ? 'URL INSPECTION' : 'INSPEKSI TAUTAN',
                'level' => $levels->count() . ($isEn ? ' Levels • Combo Multiplier' : ' Level • Combo Multiplier'),
                'description' => $isEn
                    ? 'Verify domain authenticity before the level timer runs out and build consecutive combo multipliers.'
                    : 'Identifikasi keaslian domain URL dengan batas waktu sesuai level dan kumpulkan multiplier skor beruntun.',
            ],
        ];

        $stats = [
            'phishing_max' => 0,
            'defender_max' => 0,
            'link_max' => 0,
            'total_main' => 0,
            'total_benar' => 0,
            'total_salah' => 0,
            'akurasi' => 0,
        ];
        $riwayat = [];

        if (Auth::check()) {
            $userId = Auth::id();
            $allUserScores = SkorGame::where('user_id', $userId)->get();

            $stats['phishing_max'] = (int) $allUserScores->whereIn('game_type', ['phishing', 'level1'])->max('skor_tertinggi');
            $stats['defender_max'] = (int) $allUserScores->whereIn('game_type', ['cyber-defender', 'level2'])->max('skor_tertinggi');
            $stats['link_max'] = (int) $allUserScores->whereIn('game_type', ['link-trap', 'level3'])->max('skor_tertinggi');
            $stats['total_main'] = $allUserScores->count();

            $benarCount = 0;
            $salahCount = 0;
            foreach ($allUserScores as $row) {
                $benarCount += max(1, (int) round(max(0, $row->skor) / 10));
                $salahCount += $row->skor >= 80 ? 1 : ($row->skor >= 50 ? 2 : 3);
            }

            if ($stats['total_main'] > 0) {
                $stats['total_benar'] = $benarCount;
                $stats['total_salah'] = $salahCount;
                $stats['akurasi'] = (int) round(($benarCount / max(1, $benarCount + $salahCount)) * 100);
            }

            $riwayat = SkorGame::where('user_id', $userId)
                ->orderByDesc('played_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(fn (SkorGame $item) => [
                    'id' => (int) $item->id,
                    'game_type' => $item->game_type,
                    'game_name' => $item->nama_game,
                    'skor' => (int) $item->skor,
                    'skor_tertinggi' => (int) $item->skor_tertinggi,
                    'played_at' => $item->played_at ? $item->played_at->format('d M Y, H:i') : '-',
                ])
                ->values()
                ->all();
        }

        $leaderboard = DB::table('skor_game')
            ->join('users', 'skor_game.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.username',
                'users.nama_lengkap',
                'users.avatar',
                DB::raw('MAX(skor_game.skor_tertinggi) as top_score'),
                DB::raw('SUM(skor_game.skor) as total_score'),
                DB::raw('COUNT(skor_game.id) as matches_played')
            )
            ->groupBy('users.id', 'users.username', 'users.nama_lengkap', 'users.avatar')
            ->orderByDesc('top_score')
            ->orderByDesc('total_score')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $avatar = $row->avatar;
                $avatarUrl = null;
                if (! empty($avatar)) {
                    $av = ltrim((string) $avatar, '/');
                    if (str_starts_with($av, 'http://') || str_starts_with($av, 'https://')) {
                        $avatarUrl = $av;
                    } elseif (file_exists(public_path($av))) {
                        $avatarUrl = asset($av);
                    } elseif (file_exists(storage_path('app/public/' . $av))) {
                        $avatarUrl = asset('storage/' . $av);
                    }
                }
                $displayName = $row->nama_lengkap ?: $row->username;

                return [
                    'id' => (int) $row->id,
                    'username' => $row->username,
                    'nama_lengkap' => $displayName,
                    'inisial' => mb_strtoupper(mb_substr(trim($displayName), 0, 1)),
                    'avatar_url' => $avatarUrl,
                    'top_score' => (int) $row->top_score,
                    'total_score' => (int) $row->total_score,
                    'matches_played' => (int) $row->matches_played,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'locale' => app()->getLocale(),
            'games' => $games,
            'levels' => $levels,
            'stats' => $stats,
            'riwayat' => $riwayat,
            'leaderboard' => $leaderboard,
        ]);
    }

    public function questions(string $type): JsonResponse
    {
        if (! in_array($type, ['phishing', 'cyber-defender', 'link-trap'], true)) {
            return response()->json(['success' => false, 'message' => 'Tipe minigame tidak ditemukan.'], 404);
        }

        MinigameLevel::ensureInitialized();

        $levels = MinigameLevel::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (MinigameLevel $lvl) {
                $t = BilingualHelper::translateLevel($lvl->slug, $lvl->nama_level, (string) $lvl->deskripsi);

                return [
                    'id' => (int) $lvl->id,
                    'slug' => $lvl->slug,
                    'nama_level' => $t['nama'],
                    'badge_label' => $lvl->badge_label,
                    'deskripsi' => $t['deskripsi'],
                    'waktu_detik' => (int) $lvl->waktu_detik,
                    'poin_benar' => (int) $lvl->poin_benar,
                    'penalti_salah' => (int) $lvl->penalti_salah,
                    'hp_awal' => (int) $lvl->hp_awal,
                    'urutan' => (int) $lvl->urutan,
                    'warna' => $lvl->warna,
                ];
            })
            ->values();

        $dbSoals = SoalMinigame::all()
            ->filter(fn (SoalMinigame $s) => $s->game_slug === $type)
            ->map(function (SoalMinigame $s) {
                $t = BilingualHelper::translateDbQuestion((int) $s->id, (string) $s->pertanyaan, $s->opsi, $s->kunci_huruf);

                return [
                    'id' => (int) $s->id,
                    'pertanyaan' => $t['pertanyaan'],
                    'opsi' => $t['opsi'],
                    'kunci' => $s->kunci_huruf,
                    'kunci_teks' => $t['kunci_teks'],
                    'level' => $s->level_normalize,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'game_type' => $type,
            'locale' => app()->getLocale(),
            'levels' => $levels,
            'db_questions' => $dbSoals,
        ]);
    }

    public function saveScore(Request $request): JsonResponse
    {
        SocialSchemaHelper::ensureReady();

        $validated = $request->validate([
            'game_type' => ['required', 'string', 'in:phishing,cyber-defender,link-trap'],
            'skor' => ['required', 'integer', 'min:0', 'max:5000'],
            'benar' => ['nullable', 'integer', 'min:0', 'max:100'],
            'salah' => ['nullable', 'integer', 'min:0', 'max:100'],
            'avg_time_sec' => ['nullable', 'numeric', 'min:0', 'max:300'],
        ]);

        $user = Auth::user();
        $userId = $user->id;
        $benar = (int) ($validated['benar'] ?? 0);
        $salah = (int) ($validated['salah'] ?? 0);
        $skorBaru = (int) $validated['skor'];
        if ($benar > 0) {
            $skorBaru = min($skorBaru, max(100, $benar * 250));
        } elseif ($salah > 0) {
            $skorBaru = 0;
        }
        $avgTime = (float) ($validated['avg_time_sec'] ?? 99);

        $prevHighest = (int) SkorGame::where('user_id', $userId)
            ->where('game_type', $validated['game_type'])
            ->max('skor_tertinggi');

        $skorTertinggi = max($prevHighest, $skorBaru);

        $record = SkorGame::create([
            'user_id' => $userId,
            'game_type' => $validated['game_type'],
            'skor' => $skorBaru,
            'skor_tertinggi' => $skorTertinggi,
            'benar' => $benar,
            'salah' => $salah,
            'played_at' => now(),
        ]);

        $candidateBadges = [];
        if ($skorBaru > 0 && $benar > 0 && $salah === 0) {
            $candidateBadges[] = 'Tanpa Salah (Flawless)';
        }
        if ($skorBaru >= 40 && $avgTime > 0 && $avgTime < 10) {
            $candidateBadges[] = 'Speed Runner (<10d)';
        }
        if ($skorBaru >= 60) {
            $candidateBadges[] = match ($validated['game_type']) {
                'cyber-defender' => 'Cyber Defender',
                'link-trap' => 'Link Inspector',
                default => 'Phishing Hunter',
            };
        }
        if ($skorBaru >= 100) {
            $candidateBadges[] = 'Master Siber';
        }

        $unlockedBadges = $user->awardBadges($candidateBadges);
        $isEn = BilingualHelper::isEnglish();

        return response()->json([
            'success' => true,
            'saved' => true,
            'message' => ! empty($unlockedBadges)
                ? ($isEn ? 'Score saved! New badges unlocked: ' : 'Skor tersimpan! Lencana baru terbuka: ') . implode(', ', $unlockedBadges)
                : ($isEn ? 'Score saved successfully!' : 'Skor berhasil disimpan!'),
            'unlocked_badges' => $unlockedBadges,
            'data' => [
                'id' => (int) $record->id,
                'game_name' => $record->nama_game,
                'skor' => (int) $record->skor,
                'skor_tertinggi' => (int) $record->skor_tertinggi,
                'benar' => $benar,
                'salah' => $salah,
                'waktu' => $record->played_at->format('d M Y, H:i'),
            ],
        ]);
    }
}

