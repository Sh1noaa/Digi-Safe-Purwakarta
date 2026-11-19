<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\EdukasiController as AdminWebEdukasiController;
use App\Http\Controllers\Controller;
use App\Models\EdukasiRequest;
use App\Models\Forum;
use App\Models\MinigameLevel;
use App\Models\ModulEdukasi;
use App\Models\SkorGame;
use App\Models\SoalMinigame;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use App\Services\ApiResourceFormatter;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminApiController extends Controller
{
    public function summary(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();

        return response()->json([
            'success' => true,
            'stats' => [
                'users_count' => Schema::hasTable('users') ? User::humans()->count() : 0,
                'modules_count' => Schema::hasTable('modul_edukasi') ? ModulEdukasi::count() : 0,
                'pending_requests_count' => Schema::hasTable('edukasi_requests') ? EdukasiRequest::where('status', 'pending')->count() : 0,
                'questions_count' => Schema::hasTable('soal_minigame') ? SoalMinigame::count() : 0,
                'levels_count' => Schema::hasTable('minigame_levels') ? MinigameLevel::count() : 0,
                'forums_count' => Schema::hasTable('forum') ? Forum::count() : 0,
                'plays_count' => Schema::hasTable('skor_game') ? SkorGame::count() : 0,
            ],
            'analytics' => AdminAnalyticsService::build(),
        ]);
    }

    public function overview(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        MinigameLevel::ensureInitialized();

        $modules = ModulEdukasi::orderByDesc('id')->get()
            ->values()
            ->map(fn (ModulEdukasi $m, int $idx) => ApiResourceFormatter::formatModule($m, $idx))
            ->values();

        $requests = EdukasiRequest::with('user')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest('id')
            ->get()
            ->map(fn (EdukasiRequest $r) => [
                'id' => (int) $r->id,
                'user_id' => (int) $r->user_id,
                'user_name' => $r->user?->nama_lengkap ?: ($r->user?->username ?: 'Warga'),
                'username' => $r->user?->username ?: '',
                'user_avatar' => $r->user?->avatar_url,
                'tema' => $r->tema,
                'kategori' => $r->kategori,
                'preferred_type' => $r->preferred_type ?: 'teks',
                'deskripsi' => $r->deskripsi,
                'status' => $r->status,
                'admin_note' => $r->admin_note,
                'created_at' => $r->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
            ])
            ->values();

        $levels = MinigameLevel::orderBy('urutan')->orderBy('id')->get()
            ->map(fn (MinigameLevel $lvl) => [
                'id' => (int) $lvl->id,
                'slug' => $lvl->slug,
                'nama_level' => $lvl->nama_level,
                'badge_label' => $lvl->badge_label,
                'deskripsi' => $lvl->deskripsi,
                'waktu_detik' => (int) $lvl->waktu_detik,
                'poin_benar' => (int) $lvl->poin_benar,
                'penalti_salah' => (int) $lvl->penalti_salah,
                'hp_awal' => (int) $lvl->hp_awal,
                'urutan' => (int) $lvl->urutan,
                'warna' => $lvl->warna,
                'is_active' => (bool) $lvl->is_active,
            ])
            ->values();

        $questions = SoalMinigame::orderByDesc('id')->get()
            ->map(fn (SoalMinigame $s) => [
                'id' => (int) $s->id,
                'pertanyaan' => $s->pertanyaan,
                'opsi' => $s->opsi,
                'kunci' => $s->kunci_huruf,
                'game_level' => $s->game_slug,
                'tingkat' => $s->level_normalize,
            ])
            ->values();

        $forums = Forum::with(['user', 'jawaban.user', 'jawaban.votes'])
            ->orderByDesc('is_starred')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Forum $f) => ApiResourceFormatter::formatForum($f, Auth::id()))
            ->values();

        $users = User::humans()
            ->withCount(['skorGames', 'forums', 'jawabanForums', 'testimonis'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->withMax('skorGames as top_skor_game', 'skor_tertinggi')
            ->orderByRaw("CASE WHEN role = 'owner' THEN 1 WHEN role = 'admin' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get()
            ->map(fn (User $u) => ApiResourceFormatter::formatUser($u, Auth::user(), true))
            ->values();

        return response()->json([
            'success' => true,
            'stats' => [
                'users_count' => $users->count(),
                'modules_count' => $modules->count(),
                'pending_requests_count' => $requests->where('status', 'pending')->count(),
                'questions_count' => $questions->count(),
                'levels_count' => $levels->count(),
                'forums_count' => $forums->count(),
                'plays_count' => SkorGame::count(),
            ],
            'analytics' => AdminAnalyticsService::build(),
            'modules' => $modules,
            'edukasi_requests' => $requests,
            'levels' => $levels,
            'questions' => $questions,
            'forums' => $forums,
            'users' => $users,
        ]);
    }

    public function storeEdukasi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url:http,https', 'max:500'],
        ]);

        $modul = ModulEdukasi::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'type' => $validated['type'],
            'konten' => $validated['type'] === 'teks'
                ? $validated['konten']
                : ($request->input('konten') ?: 'Referensi literasi keamanan digital terverifikasi.'),
            'url' => $validated['type'] === 'link' ? $validated['url'] : null,
            'link_url' => $validated['type'] === 'link' ? ($validated['url'] ?? '') : '',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul edukasi baru berhasil diterbitkan dan langsung sinkron ke web & APK!',
            'module' => ApiResourceFormatter::formatModule($modul, 0),
        ], 201);
    }

    public function updateEdukasi(Request $request, ModulEdukasi $edukasi): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url:http,https', 'max:500'],
        ]);

        $edukasi->update([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'type' => $validated['type'],
            'konten' => $validated['type'] === 'teks'
                ? $validated['konten']
                : ($request->input('konten') ?: $edukasi->konten),
            'url' => $validated['type'] === 'link' ? $validated['url'] : null,
            'link_url' => $validated['type'] === 'link' ? ($validated['url'] ?? '') : '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul edukasi berhasil diperbarui.',
            'module' => ApiResourceFormatter::formatModule($edukasi->fresh(), 0),
        ]);
    }

    public function deleteEdukasi(ModulEdukasi $edukasi): JsonResponse
    {
        $edukasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Modul edukasi berhasil dihapus.',
        ]);
    }

    public function approveEdukasiRequest(Request $request, EdukasiRequest $edukasiRequest): JsonResponse
    {
        return $this->respondEdukasiDecision($request, $edukasiRequest, 'approved');
    }

    public function rejectEdukasiRequest(Request $request, EdukasiRequest $edukasiRequest): JsonResponse
    {
        return $this->respondEdukasiDecision($request, $edukasiRequest, 'rejected');
    }

    private function respondEdukasiDecision(Request $request, EdukasiRequest $edukasiRequest, string $decision): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        AdminWebEdukasiController::applyEvaluationDecision(
            $edukasiRequest,
            $decision,
            trim((string) ($validated['admin_note'] ?? '')),
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => $decision === 'approved'
                ? 'Request disetujui! Bot Digika otomatis mengirim pesan persetujuan ke user.'
                : 'Request ditolak. Bot Digika otomatis mengirim pemberitahuan ke user.',
        ]);
    }

    public function storeMinigameQuestion(Request $request): JsonResponse
    {
        MinigameLevel::ensureInitialized();
        $validSlugs = MinigameLevel::pluck('slug')->all();

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
            'pilihan_a' => ['required', 'string', 'max:255'],
            'pilihan_b' => ['required', 'string', 'max:255'],
            'pilihan_c' => ['required', 'string', 'max:255'],
            'pilihan_d' => ['required', 'string', 'max:255'],
            'jawaban_benar' => ['required', 'in:A,B,C,D'],
            'game_level' => ['required', 'in:phishing,cyber-defender,link-trap'],
            'tingkat' => ['required', 'string', Rule::in($validSlugs ?: ['easy', 'medium', 'hard', 'expert', 'master'])],
        ]);

        $kategoriLabel = match ($validated['game_level']) {
            'cyber-defender' => 'Cyber Defender',
            'link-trap' => 'Link or Trap',
            default => 'Phishing Hunter',
        };

        $soal = SoalMinigame::create([
            'pertanyaan' => $validated['pertanyaan'],
            'pilihan_json' => json_encode([
                'A' => $validated['pilihan_a'],
                'B' => $validated['pilihan_b'],
                'C' => $validated['pilihan_c'],
                'D' => $validated['pilihan_d'],
            ]),
            'jawaban_benar' => $validated['jawaban_benar'],
            'kategori' => $kategoriLabel,
            'tingkat' => $validated['tingkat'],
            'game_level' => $validated['game_level'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soal minigame baru berhasil ditambahkan.',
            'question' => [
                'id' => (int) $soal->id,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => $soal->opsi,
                'kunci' => $soal->kunci_huruf,
                'game_level' => $soal->game_slug,
                'tingkat' => $soal->level_normalize,
            ],
        ], 201);
    }

    public function deleteMinigameQuestion(SoalMinigame $minigame): JsonResponse
    {
        $minigame->delete();

        return response()->json([
            'success' => true,
            'message' => 'Soal minigame berhasil dihapus.',
        ]);
    }

    public function storeMinigameLevel(Request $request): JsonResponse
    {
        MinigameLevel::ensureInitialized();

        $validated = $request->validate([
            'nama_level' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:50'],
            'badge_label' => ['required', 'string', 'max:60'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'waktu_detik' => ['required', 'integer', 'min:3', 'max:120'],
            'poin_benar' => ['required', 'integer', 'min:5', 'max:200'],
            'penalti_salah' => ['required', 'integer', 'min:0', 'max:100'],
            'hp_awal' => ['required', 'integer', 'min:1', 'max:10'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
            'warna' => ['required', 'in:emerald,sky,amber,purple,red,lime'],
        ]);

        $slug = Str::slug($validated['slug'] ?: $validated['nama_level']) ?: ('level-' . (MinigameLevel::count() + 1));
        $baseSlug = $slug;
        $counter = 2;
        while (MinigameLevel::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $level = MinigameLevel::create([
            'slug' => $slug,
            'nama_level' => $validated['nama_level'],
            'badge_label' => strtoupper($validated['badge_label']),
            'deskripsi' => $validated['deskripsi'],
            'waktu_detik' => (int) $validated['waktu_detik'],
            'poin_benar' => (int) $validated['poin_benar'],
            'penalti_salah' => (int) $validated['penalti_salah'],
            'hp_awal' => (int) $validated['hp_awal'],
            'urutan' => (int) $validated['urutan'],
            'warna' => $validated['warna'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level "' . $level->nama_level . '" berhasil ditambahkan.',
            'level' => $level,
        ], 201);
    }

    public function deleteMinigameLevel(MinigameLevel $level): JsonResponse
    {
        if (MinigameLevel::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal harus ada 1 level aktif di sistem minigame.',
            ], 422);
        }

        $level->delete();

        return response()->json([
            'success' => true,
            'message' => 'Level minigame berhasil dihapus.',
        ]);
    }

    public function toggleForumStar(Forum $forum): JsonResponse
    {
        $forum->update(['is_starred' => ! $forum->is_starred]);
        $forum->load(['user', 'jawaban.user', 'jawaban.votes']);

        return response()->json([
            'success' => true,
            'message' => $forum->is_starred ? 'Topik forum disematkan.' : 'Sematkan topik dilepas.',
            'forum' => ApiResourceFormatter::formatForum($forum, Auth::id()),
        ]);
    }

    public function deleteForum(Forum $forum): JsonResponse
    {
        $jawabanIds = $forum->jawaban()->pluck('id');
        if ($jawabanIds->isNotEmpty() && Schema::hasTable('jawaban_forum_votes')) {
            $col = Schema::hasColumn('jawaban_forum_votes', 'jawaban_id') ? 'jawaban_id' : 'jawaban_forum_id';
            DB::table('jawaban_forum_votes')->whereIn($col, $jawabanIds)->delete();
        }
        $forum->jawaban()->delete();
        $forum->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topik forum beserta seluruh balasan berhasil dihapus.',
        ]);
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        if ($user->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Owner utama bersifat permanen dan tidak dapat diubah.',
            ], 403);
        }

        $currentUser = Auth::user();
        if (! $currentUser->isOwner() && $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Owner yang dapat mengubah hak akses Admin lain.',
            ], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'success' => true,
            'message' => 'Hak akses ' . ($user->nama_lengkap ?: $user->username) . ' berhasil diubah menjadi ' . strtoupper($validated['role']) . '.',
            'user' => ApiResourceFormatter::formatUser($user->fresh(), $currentUser, true),
        ]);
    }
}
