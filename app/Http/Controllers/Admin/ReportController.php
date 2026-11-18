<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\JawabanForumVote;
use App\Models\MinigameLevel;
use App\Models\ModulEdukasi;
use App\Models\SkorGame;
use App\Models\SoalMinigame;
use App\Models\Testimoni;
use App\Models\User;
use App\Services\SimpleXlsxExporter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function downloadXlsx(): Response
    {
        MinigameLevel::ensureInitialized();
        JawabanForumVote::ensureTable();

        $users = User::humans()->with(['skorGames'])
            ->withCount(['forums', 'jawabanForums', 'testimonis'])
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'user')")
            ->orderBy('id')
            ->get();

        $allScores = SkorGame::with('user')->orderByDesc('played_at')->orderByDesc('id')->get();
        $moduls = ModulEdukasi::orderBy('id')->get();
        $forums = Forum::with(['user', 'jawaban.votes'])->withCount('jawaban')->orderByDesc('is_starred')->orderByDesc('created_at')->get();
        $totalTestimoni = Testimoni::count();
        $totalSoal = SoalMinigame::count();
        $totalLevels = MinigameLevel::count();

        // Hitung progres baca modul jika ada di tabel progres_baca
        $progresBacaPerUser = DB::table('progres_baca')
            ->select('user_id', DB::raw('COUNT(*) as total_modul'), DB::raw('SUM(CASE WHEN selesai = 1 THEN 1 ELSE 0 END) as selesai_count'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Hitung detail user badges dari tabel user_badges
        $userBadgeRecords = DB::table('user_badges')
            ->join('users', 'user_badges.user_id', '=', 'users.id')
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('users.id as user_id', 'users.nama_lengkap', 'users.username', 'users.email', 'badges.nama_badge', 'badges.kriteria_skor', 'user_badges.diperoleh_pada')
            ->orderByDesc('user_badges.diperoleh_pada')
            ->get();

        // Hitung voting forum per user & global
        $totalUpVotes = JawabanForumVote::where('vote_type', 'up')->count();
        $totalDownVotes = JawabanForumVote::where('vote_type', 'down')->count();

        $upVotesPerUser = DB::table('jawaban_forum_votes')
            ->join('jawaban_forum', 'jawaban_forum_votes.jawaban_id', '=', 'jawaban_forum.id')
            ->where('jawaban_forum_votes.vote_type', 'up')
            ->select('jawaban_forum.user_id', DB::raw('COUNT(*) as helpful_count'))
            ->groupBy('jawaban_forum.user_id')
            ->pluck('helpful_count', 'user_id');

        // Statistik metrik & pencapaian titik milestone
        $totalUsers = $users->count();
        $ownerCount = $users->where('role', 'owner')->count();
        $adminCount = $users->where('role', 'admin')->count();
        $memberCount = $users->where('role', 'user')->count();

        $playedGameUsers = 0;
        $reached60Users = 0;
        $reached100Users = 0;
        $hasAchievementUsers = 0;
        $activeForumUsers = 0;

        $userRows = [];
        $milestoneAchieversRows = [];

        foreach ($users as $index => $user) {
            $topScore = (int) ($user->skorGames->max('skor_tertinggi') ?? 0);
            $sumScore = (int) $user->skorGames->sum('skor');
            $totalMain = $user->skorGames->count();
            $badges = array_values(array_filter($user->badges_list, fn ($b) => $b !== 'New Player'));
            $allBadgesLabel = count($user->badges_list) > 0 ? implode(', ', $user->badges_list) : '-';
            $forumTotal = (int) $user->forums_count + (int) $user->jawaban_forums_count;
            $helpfulReceived = (int) ($upVotesPerUser[$user->id] ?? 0);
            $modulSelesai = (int) ($progresBacaPerUser[$user->id]->selesai_count ?? 0);

            if ($totalMain > 0) {
                $playedGameUsers++;
            }
            if ($topScore >= 60) {
                $reached60Users++;
            }
            if ($topScore >= 100) {
                $reached100Users++;
            }
            if (count($badges) > 0) {
                $hasAchievementUsers++;
            }
            if ($forumTotal > 0) {
                $activeForumUsers++;
            }

            // Tentukan titik pencapaian (milestone) tertinggi pengguna
            if ($topScore >= 100) {
                $milestone = 'Master Siber (Skor 100+)';
            } elseif ($topScore >= 60) {
                $milestone = 'Mahir Siber (Skor 60-99)';
            } elseif ($totalMain > 0) {
                $milestone = 'Aktif Berlatih (Skor < 60)';
            } elseif ($forumTotal > 0) {
                $milestone = 'Kontributor Forum';
            } else {
                $milestone = 'Terdaftar (Belum Mulai)';
            }

            $displayName = $user->nama_lengkap ?: $user->username;
            $joinedAt = $user->created_at ? $user->created_at->format('d-m-Y') : '-';

            $userRows[] = [
                $index + 1,
                $displayName,
                '@' . $user->username,
                $user->email,
                strtoupper((string) $user->role),
                $joinedAt,
                $totalMain,
                $topScore,
                $sumScore,
                count($badges),
                $allBadgesLabel,
                $forumTotal,
                $helpfulReceived,
                $modulSelesai,
                $milestone,
            ];

            // Jika user sudah mencapai titik milestone signifikan, masukkan ke sheet pencapaian khusus
            if ($topScore >= 60 || count($badges) > 0 || $forumTotal > 0 || $modulSelesai > 0) {
                $phishingMax = (int) ($user->skorGames->whereIn('game_type', ['phishing', 'level1'])->max('skor_tertinggi') ?? 0);
                $defenderMax = (int) ($user->skorGames->whereIn('game_type', ['cyber-defender', 'level2'])->max('skor_tertinggi') ?? 0);
                $linkMax = (int) ($user->skorGames->whereIn('game_type', ['link-trap', 'level3'])->max('skor_tertinggi') ?? 0);

                $milestoneAchieversRows[] = [
                    count($milestoneAchieversRows) + 1,
                    $displayName,
                    '@' . $user->username,
                    $milestone,
                    $phishingMax,
                    $defenderMax,
                    $linkMax,
                    $topScore,
                    count($badges) > 0 ? implode(', ', $badges) : 'Belum ada lencana game',
                    $helpfulReceived,
                ];
            }
        }

        $pct = fn (int $part) => $totalUsers > 0 ? round(($part / $totalUsers) * 100, 1) . '%' : '0%';
        $avgGameScore = $allScores->count() > 0 ? round($allScores->avg('skor'), 1) : 0;

        $exporter = new SimpleXlsxExporter();
        $exportedAt = now()->format('d M Y, H:i') . ' WIB';
        $exporterName = Auth::user()->nama_lengkap ?: Auth::user()->username;

        // Sheet 1: Ringkasan Eksekutif Digi-Safe
        $exporter->addSheet(
            'Ringkasan Digi-Safe',
            'LAPORAN RESMI PLATFORM DIGI-SAFE PURWAKARTA',
            'Diekspor pada ' . $exportedAt . ' oleh ' . $exporterName,
            [
                [
                    'heading' => 'A. Statistik Umum Pengguna & Konten Platform',
                    'headers' => ['No', 'Indikator / Kategori', 'Jumlah', 'Keterangan'],
                    'alignments' => ['center', 'left', 'right', 'left'],
                    'rows' => [
                        [1, 'Total Pengguna Terdaftar di Digi-Safe', $totalUsers, "Owner: {$ownerCount} | Admin: {$adminCount} | Warga/User: {$memberCount}"],
                        [2, 'Total Modul Edukasi Aktif', $moduls->count(), 'Tersedia untuk seluruh pengguna platform'],
                        [3, 'Total Level & Bank Soal Minigame', $totalLevels, "{$totalLevels} level kesulitan aktif & {$totalSoal} soal di bank soal"],
                        [4, 'Total Sesi Minigame Dimainkan', $allScores->count(), "Rata-rata skor permainan: {$avgGameScore} poin"],
                        [5, 'Total Diskusi & Jawaban Forum', $forums->count(), JawabanForum::count() . " balasan & {$totalUpVotes} penilaian membantu (Thumbs Up)"],
                        [6, 'Total Penilaian Buruk (Thumbs Down) Forum', $totalDownVotes, 'Akumulasi feedback perbaikan jawaban di forum'],
                        [7, 'Total Testimoni Warga', $totalTestimoni, 'Ulasan pengalaman literasi keamanan digital'],
                    ],
                ],
                [
                    'heading' => 'B. Ringkasan Pencapaian Titik (Milestone) Pengguna',
                    'headers' => ['No', 'Titik Pencapaian (Milestone)', 'Jumlah Pengguna', 'Persentase', 'Kriteria Titik'],
                    'alignments' => ['center', 'left', 'right', 'center', 'left'],
                    'rows' => [
                        [1, 'Aktif Bermain Minigame', $playedGameUsers, $pct($playedGameUsers), 'Menyelesaikan minimal 1 sesi permainan minigame'],
                        [2, 'Lulus Standar Keamanan Siber', $reached60Users, $pct($reached60Users), 'Mencapai skor tertinggi minimal 60 poin'],
                        [3, 'Peringkat Master Siber', $reached100Users, $pct($reached100Users), 'Mencapai skor tertinggi minimal 100 poin'],
                        [4, 'Peraih Lencana Achievement', $hasAchievementUsers, $pct($hasAchievementUsers), 'Membuka minimal 1 lencana pencapaian minigame'],
                        [5, 'Kontributor Aktif Forum', $activeForumUsers, $pct($activeForumUsers), 'Membuat thread diskusi atau menjawab pertanyaan warga'],
                    ],
                ],
            ]
        );

        // Sheet 2: Data Lengkap Pengguna & Pencapaian
        $exporter->addSheet(
            'Pengguna & Pencapaian',
            'DATA SELURUH PENGGUNA & PENCAPAIAN MILESTONE',
            'Total ' . $totalUsers . ' pengguna terdaftar • Diekspor pada ' . $exportedAt,
            [
                [
                    'headers' => [
                        'No',
                        'Nama Lengkap',
                        'Username',
                        'Email',
                        'Role',
                        'Tgl Bergabung',
                        'Total Main',
                        'Skor Tertinggi',
                        'Akumulasi Skor',
                        'Jml Lencana',
                        'Daftar Lencana',
                        'Aktivitas Forum',
                        'Thumbs Up Diterima',
                        'Modul Selesai',
                        'Titik Pencapaian',
                    ],
                    'alignments' => [
                        'center', 'left', 'left', 'left', 'center', 'center',
                        'right', 'right', 'right', 'center', 'left', 'center', 'center', 'center', 'left',
                    ],
                    'rows' => $userRows,
                ],
            ]
        );

        // Sheet 3: Pengguna yang Sudah Mencapai Titik Tertentu
        $badgeHistoryRows = [];
        foreach ($userBadgeRecords as $idx => $ub) {
            $badgeHistoryRows[] = [
                $idx + 1,
                $ub->nama_lengkap ?: $ub->username,
                $ub->email,
                $ub->nama_badge,
                (int) $ub->kriteria_skor,
                $ub->diperoleh_pada ? date('d-m-Y H:i', strtotime((string) $ub->diperoleh_pada)) : '-',
            ];
        }

        $exporter->addSheet(
            'Detail Pencapaian Titik',
            'DAFTAR PENGGUNA YANG TELAH MENCAPAI TITIK TERTENTU',
            'Rincian skor tertinggi per simulasi minigame & lencana achievement • ' . $exportedAt,
            [
                [
                    'heading' => 'A. Pengguna dengan Pencapaian Milestone Aktif',
                    'headers' => [
                        'No',
                        'Nama Pengguna',
                        'Username',
                        'Titik Pencapaian Tertinggi',
                        'Phishing Hunter',
                        'Cyber Defender',
                        'Link or Trap',
                        'Skor Tertinggi',
                        'Lencana Achievement Diraih',
                        'Vote Membantu',
                    ],
                    'alignments' => ['center', 'left', 'left', 'left', 'right', 'right', 'right', 'right', 'left', 'center'],
                    'rows' => ! empty($milestoneAchieversRows)
                        ? $milestoneAchieversRows
                        : [[1, 'Belum ada pengguna yang mencapai titik milestone.', '-', '-', 0, 0, 0, 0, '-', 0]],
                ],
                [
                    'heading' => 'B. Riwayat Perolehan Lencana (Achievement Badges)',
                    'headers' => ['No', 'Nama Pengguna', 'Email', 'Nama Lencana', 'Kriteria Skor', 'Waktu Diraih'],
                    'alignments' => ['center', 'left', 'left', 'left', 'right', 'center'],
                    'rows' => ! empty($badgeHistoryRows)
                        ? $badgeHistoryRows
                        : [[1, 'Belum ada riwayat lencana tercatat di tabel user_badges.', '-', '-', 0, '-']],
                ],
            ]
        );

        // Sheet 4: Aktivitas Forum & Modul Edukasi
        $forumRows = [];
        foreach ($forums as $idx => $f) {
            $upSum = 0;
            $downSum = 0;
            foreach ($f->jawaban as $ans) {
                $upSum += $ans->votes->where('vote_type', 'up')->count();
                $downSum += $ans->votes->where('vote_type', 'down')->count();
            }

            $forumRows[] = [
                $idx + 1,
                $f->judul,
                $f->kategori ?: 'Umum',
                $f->user ? ($f->user->nama_lengkap ?: $f->user->username) : 'Anonim',
                strtoupper((string) ($f->status ?: 'terbuka')),
                (int) $f->jawaban_count,
                $upSum,
                $downSum,
                $f->created_at ? $f->created_at->format('d-m-Y H:i') : '-',
            ];
        }

        $modulRows = [];
        foreach ($moduls as $idx => $m) {
            $modulRows[] = [
                $idx + 1,
                $m->judul,
                $m->kategori ?: 'Umum',
                $m->effective_type === 'teks' ? 'Teks Manual' : 'Link Eksternal',
                $m->created_at ? $m->created_at->format('d-m-Y') : '-',
            ];
        }

        $exporter->addSheet(
            'Forum & Edukasi',
            'LAPORAN AKTIVITAS FORUM KOMUNITAS & MODUL EDUKASI',
            'Data topik diskusi, respons membantu, dan daftar modul • ' . $exportedAt,
            [
                [
                    'heading' => 'A. Ringkasan Topik Forum Komunitas',
                    'headers' => ['No', 'Judul Diskusi', 'Kategori', 'Penulis', 'Status', 'Jml Balasan', 'Thumbs Up', 'Thumbs Down', 'Tanggal Dibuat'],
                    'alignments' => ['center', 'left', 'center', 'left', 'center', 'right', 'right', 'right', 'center'],
                    'rows' => ! empty($forumRows) ? $forumRows : [[1, 'Belum ada topik diskusi', '-', '-', '-', 0, 0, 0, '-']],
                ],
                [
                    'heading' => 'B. Daftar Modul Edukasi Terdaftar',
                    'headers' => ['No', 'Judul Modul', 'Kategori', 'Tipe Konten', 'Tanggal Dibuat'],
                    'alignments' => ['center', 'left', 'left', 'center', 'center'],
                    'rows' => ! empty($modulRows) ? $modulRows : [[1, 'Belum ada modul edukasi', '-', '-', '-']],
                ],
            ]
        );

        $binary = $exporter->buildBinary();
        $filename = 'laporan-digi-safe-purwakarta-' . now()->format('Y-m-d_His') . '.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            'Content-Length' => (string) strlen($binary),
        ]);
    }
}

