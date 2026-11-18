<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\JawabanForumVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(): View
    {
        // daftar thread forum diurutkan dari yang disematkan
        $forums = Forum::with('user')
            ->withCount('jawaban')
            ->orderByDesc('is_starred')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.forum.index', compact('forums'));
    }

    public function toggleStar(Request $request, Forum $forum): RedirectResponse|JsonResponse
    {
        // ubah status sematan bintang pada thread forum
        $forum->is_starred = ! $forum->is_starred;
        $forum->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_starred' => (bool) $forum->is_starred,
                'message' => $forum->is_starred
                    ? 'Diskusi berhasil disematkan ke urutan teratas.'
                    : 'Sematan diskusi telah dilepas.',
            ]);
        }

        return redirect()
            ->route('admin.forum.index')
            ->with('success', $forum->is_starred
                ? 'Diskusi berhasil disematkan ke urutan teratas.'
                : 'Sematan diskusi telah dilepas.');
    }

    public function destroy(Forum $forum): RedirectResponse
    {
        // hapus diskusi beserta seluruh jawaban dan votingnya
        JawabanForumVote::ensureTable();
        $jawabanIds = JawabanForum::where('forum_id', $forum->id)->pluck('id');
        if ($jawabanIds->isNotEmpty()) {
            JawabanForumVote::whereIn('jawaban_id', $jawabanIds)->delete();
        }
        JawabanForum::where('forum_id', $forum->id)->delete();
        $forum->delete();

        return redirect()
            ->route('admin.forum.index')
            ->with('success', 'Thread diskusi telah dihapus.');
    }
}
