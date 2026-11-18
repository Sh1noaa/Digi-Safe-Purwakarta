<?php

namespace App\Http\Controllers;

use App\Models\MinigameLevel;
use App\Models\SkorGame;
use App\Models\SoalMinigame;
use App\Services\BilingualHelper;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MinigameController extends Controller
{
    public function index(): View
    {
        MinigameLevel::ensureInitialized();

        $levels = MinigameLevel::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (MinigameLevel $lvl) {
                $t = BilingualHelper::translateLevel($lvl->slug, $lvl->nama_level, (string) $lvl->deskripsi);
                $lvl->nama_level = $t['nama'];
                $lvl->deskripsi = $t['deskripsi'];

                return $lvl;
            });

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
                'icon' => 'shield',
                'accent' => 'sky',
                'thumbnail' => 'images/game-phishing.jpg',
            ],
            [
                'slug' => 'cyber-defender',
                'title' => 'Cyber Defender',
                'category' => $isEn ? 'SYSTEM DEFENSE' : 'PERTAHANAN SISTEM',
                'level' => $levels->count() . ($isEn ? ' Defense Levels' : ' Level Pertahanan'),
                'description' => $isEn
                    ? 'Protect the core system from incoming viruses, malware, and social engineering attacks at your chosen difficulty.'
                    : 'Lindungi sistem inti dari gelombang virus, malware, dan rekayasa sosial sesuai tingkat kesulitan yang dipilih.',
                'icon' => 'defender',
                'accent' => 'blue',
                'thumbnail' => 'images/game-defender.jpg',
            ],
            [
                'slug' => 'link-trap',
                'title' => 'Link or Trap?',
                'category' => $isEn ? 'URL INSPECTION' : 'INSPEKSI TAUTAN',
                'level' => $levels->count() . ($isEn ? ' Levels • Combo Multiplier' : ' Level • Combo Multiplier'),
                'description' => $isEn
                    ? 'Verify domain authenticity before the level timer runs out and build consecutive combo multipliers.'
                    : 'Identifikasi keaslian domain URL dengan batas waktu sesuai level dan kumpulkan multiplier skor beruntun.',
                'icon' => 'link',
                'accent' => 'lime',
                'thumbnail' => 'images/game-linktrap.jpg',
            ],
            [
                'slug' => 'password-checker',
                'title' => 'Password Strength Checker',
                'category' => $isEn ? 'PASSWORD LAB' : 'ANALISIS SANDI',
                'level' => $isEn ? 'Instant AI-Free Audit & Upgrader' : 'Audit Instan & Penguat Sandi',
                'description' => $isEn
                    ? 'Test any ordinary password in real-time and get stronger, memorable upgraded variations instantly.'
                    : 'Uji kata sandi biasa secara langsung dan dapatkan saran kombinasi password jauh lebih kuat dari kata yang kamu ketik.',
                'icon' => 'lock',
                'accent' => 'emerald',
                'thumbnail' => 'images/game-defender.jpg',
            ],
        ];

        $riwayat = collect();
        $stats = [
            'phishing_max' => 0,
            'defender_max' => 0,
            'link_max' => 0,
            'total_main' => 0,
            'total_benar' => 0,
            'total_salah' => 0,
            'akurasi' => 0,
        ];

        if (Auth::check()) {
            $userId = Auth::id();

            $riwayat = SkorGame::where('user_id', $userId)
                ->orderByDesc('played_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get();

            $allUserScores = SkorGame::where('user_id', $userId)->get();

            $stats['phishing_max'] = (int) $allUserScores
                ->whereIn('game_type', ['phishing', 'level1'])
                ->max('skor_tertinggi');

            $stats['defender_max'] = (int) $allUserScores
                ->whereIn('game_type', ['cyber-defender', 'level2'])
                ->max('skor_tertinggi');

            $stats['link_max'] = (int) $allUserScores
                ->whereIn('game_type', ['link-trap', 'level3'])
                ->max('skor_tertinggi');

            $stats['total_main'] = $allUserScores->count();

            $benarCount = 0;
            $salahCount = 0;
            foreach ($allUserScores as $row) {
                $b = max(1, (int) round(max(0, $row->skor) / 10));
                $s = $row->skor >= 80 ? 1 : ($row->skor >= 50 ? 2 : 3);
                $benarCount += $b;
                $salahCount += $s;
            }

            if ($stats['total_main'] > 0) {
                $stats['total_benar'] = $benarCount;
                $stats['total_salah'] = $salahCount;
                $totalSoal = max(1, $benarCount + $salahCount);
                $stats['akurasi'] = (int) round(($benarCount / $totalSoal) * 100);
            }
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
            ->limit(3)
            ->get();

        $leaderboardData = LeaderboardService::allCategories();

        return view('minigame.index', compact('games', 'levels', 'riwayat', 'stats', 'leaderboard', 'leaderboardData'));
    }

    public function passwordChecker(): View
    {
        return view('minigame.password-checker');
    }

    public function play(string $type): View
    {
        MinigameLevel::ensureInitialized();

        $levels = MinigameLevel::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        if ($levels->isEmpty()) {
            $levels = MinigameLevel::orderBy('urutan')->orderBy('id')->get();
        }

        $levels = $levels->map(function (MinigameLevel $lvl) {
            $t = BilingualHelper::translateLevel($lvl->slug, $lvl->nama_level, (string) $lvl->deskripsi);
            $lvl->nama_level = $t['nama'];
            $lvl->deskripsi = $t['deskripsi'];

            return $lvl;
        });

        // ambil & terjemahkan soal dari bank soal admin sesuai bahasa aktif
        $dbSoals = SoalMinigame::all()
            ->filter(fn (SoalMinigame $s) => $s->game_slug === $type)
            ->map(function (SoalMinigame $s) {
                $t = BilingualHelper::translateDbQuestion((int) $s->id, (string) $s->pertanyaan, $s->opsi, $s->kunci_huruf);

                return [
                    'id' => $s->id,
                    'pertanyaan' => $t['pertanyaan'],
                    'opsi' => $t['opsi'],
                    'kunci' => $s->kunci_huruf,
                    'kunci_teks' => $t['kunci_teks'],
                    'level' => $s->level_normalize,
                ];
            })
            ->values();

        return match ($type) {
            'phishing' => view('minigame.phishing', compact('levels', 'dbSoals')),
            'cyber-defender' => view('minigame.cyber-defender', compact('levels', 'dbSoals')),
            'link-trap' => view('minigame.link-trap', compact('levels', 'dbSoals')),
            default => abort(404),
        };
    }

    public function saveScore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_type' => ['required', 'string', 'in:phishing,cyber-defender,link-trap'],
            'skor' => ['required', 'integer', 'min:0'],
            'benar' => ['nullable', 'integer', 'min:0'],
            'salah' => ['nullable', 'integer', 'min:0'],
            'avg_time_sec' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! Auth::check()) {
            return response()->json([
                'saved' => false,
                'message' => BilingualHelper::isEnglish()
                    ? 'Log in to save scores and unlock achievement badges.'
                    : 'Login untuk menyimpan skor dan membuka lencana achievement ke akun Anda.',
                'unlocked_badges' => [],
            ]);
        }

        $user = Auth::user();
        $userId = $user->id;
        $skorBaru = (int) $validated['skor'];
        $benar = (int) ($validated['benar'] ?? 0);
        $salah = (int) ($validated['salah'] ?? 0);
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
            'saved' => true,
            'message' => ! empty($unlockedBadges)
                ? ($isEn ? 'Score saved! New badges unlocked: ' : 'Skor tersimpan! Lencana baru terbuka: ') . implode(', ', $unlockedBadges)
                : ($isEn ? 'Score saved successfully!' : 'Skor berhasil disimpan!'),
            'unlocked_badges' => $unlockedBadges,
            'data' => [
                'id' => $record->id,
                'game_name' => $record->nama_game,
                'skor' => $record->skor,
                'skor_tertinggi' => $record->skor_tertinggi,
                'benar' => $benar,
                'salah' => $salah,
                'waktu' => $record->played_at->format('d M Y, H:i'),
            ],
        ]);
    }
}
