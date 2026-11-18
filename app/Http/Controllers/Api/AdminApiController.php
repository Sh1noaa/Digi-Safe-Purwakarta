<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ModulEdukasi;
use App\Models\SkorGame;
use App\Models\SoalMinigame;
use App\Models\User;
use App\Services\ApiResourceFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminApiController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => [
                'users_count' => User::count(),
                'modules_count' => ModulEdukasi::count(),
                'questions_count' => SoalMinigame::count(),
                'forums_count' => Forum::count(),
                'plays_count' => SkorGame::count(),
            ],
        ]);
    }

    public function storeEdukasi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url', 'max:500'],
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

    public function deleteEdukasi(ModulEdukasi $edukasi): JsonResponse
    {
        $edukasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Modul edukasi berhasil dihapus.',
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
}

