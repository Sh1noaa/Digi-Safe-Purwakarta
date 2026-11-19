<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\JawabanForumVote;
use App\Models\User;
use App\Services\ApiResourceFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ForumApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $sort = $request->query('sort', 'terbaru');
        $query = Forum::where('is_hidden', false)
            ->with(['user', 'jawaban.user', 'jawaban.votes'])
            ->withCount('jawaban')
            ->orderByDesc('is_starred');

        if ($sort === 'membantu') {
            $query->orderByDesc('jawaban_count')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at')->orderByDesc('id');
        }

        $currentUserId = Auth::id();
        $forums = $query->get()
            ->map(fn (Forum $item) => ApiResourceFormatter::formatForum($item, $currentUserId))
            ->values();

        return response()->json([
            'success' => true,
            'timestamp' => now()->timestamp,
            'forums' => $forums,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'pertanyaan' => ['required', 'string', 'max:3000'],
        ]);

        $user = Auth::user();

        $forum = Forum::create([
            'user_id' => $user->id,
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'pertanyaan' => $validated['pertanyaan'],
            'status' => 'terbuka',
            'is_hidden' => false,
            'is_starred' => false,
            'views' => 1,
            'created_at' => now(),
        ]);

        $forum->load(['user', 'jawaban.user', 'jawaban.votes']);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Discussion published successfully!' : 'Diskusi baru berhasil diterbitkan!',
            'forum' => ApiResourceFormatter::formatForum($forum, $user->id),
        ], 201);
    }

    public function storeJawaban(Request $request, Forum $forum): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $validated = $request->validate([
            'jawaban' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        JawabanForum::create([
            'forum_id' => $forum->id,
            'user_id' => $user->id,
            'jawaban' => $validated['jawaban'],
            'is_admin' => $user->hasAdminAccess() ? 1 : 0,
            'created_at' => now(),
        ]);

        if ($forum->status === 'terbuka') {
            $forum->update(['status' => 'terjawab']);
        }

        $forum->load(['user', 'jawaban.user', 'jawaban.votes']);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Your reply has been posted!' : 'Pendapat Anda berhasil dikirim!',
            'forum' => ApiResourceFormatter::formatForum($forum, $user->id),
        ], 201);
    }

    public function voteJawaban(Request $request, JawabanForum $jawaban): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $validated = $request->validate([
            'vote_type' => ['required', 'string', 'in:up,down'],
        ]);

        $userId = (int) Auth::id();
        $voteType = $validated['vote_type'];

        $existing = JawabanForumVote::where('jawaban_id', $jawaban->id)
            ->where('user_id', $userId)
            ->first();

        $action = 'added';
        if ($existing) {
            if ($existing->vote_type === $voteType) {
                $existing->delete();
                $action = 'removed';
            } else {
                $existing->update(['vote_type' => $voteType]);
                $action = 'switched';
            }
        } else {
            JawabanForumVote::create([
                'jawaban_id' => $jawaban->id,
                'user_id' => $userId,
                'vote_type' => $voteType,
            ]);
        }

        $forum = Forum::with(['user', 'jawaban.user', 'jawaban.votes'])
            ->findOrFail($jawaban->forum_id);

        return response()->json([
            'success' => true,
            'action' => $action,
            'forum' => ApiResourceFormatter::formatForum($forum, $userId),
        ]);
    }

    public function update(Request $request, Forum $forum): JsonResponse
    {
        JawabanForumVote::ensureTable();

        /** @var User $user */
        $user = Auth::user();
        if ((int) $forum->user_id !== (int) $user->id && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en' ? 'You are not allowed to edit this discussion.' : 'Anda tidak memiliki izin mengubah diskusi ini.',
            ], 403);
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'pertanyaan' => ['required', 'string', 'max:3000'],
        ]);

        $forum->update([
            'judul' => trim($validated['judul']),
            'kategori' => trim($validated['kategori']),
            'pertanyaan' => trim($validated['pertanyaan']),
        ]);

        $forum->load(['user', 'jawaban.user', 'jawaban.votes']);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Discussion updated successfully.' : 'Diskusi berhasil diperbarui.',
            'forum' => ApiResourceFormatter::formatForum($forum, $user->id),
        ]);
    }

    public function destroy(Forum $forum): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ((int) $forum->user_id !== (int) $user->id && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en' ? 'You are not allowed to delete this discussion.' : 'Anda tidak memiliki izin menghapus diskusi ini.',
            ], 403);
        }

        $jawabanIds = $forum->jawaban()->pluck('id');
        if ($jawabanIds->isNotEmpty() && Schema::hasTable('jawaban_forum_votes')) {
            $col = Schema::hasColumn('jawaban_forum_votes', 'jawaban_id') ? 'jawaban_id' : 'jawaban_forum_id';
            DB::table('jawaban_forum_votes')->whereIn($col, $jawabanIds)->delete();
        }
        $forum->jawaban()->delete();
        $deletedId = (int) $forum->id;
        $forum->delete();

        return response()->json([
            'success' => true,
            'deleted_id' => $deletedId,
            'message' => app()->getLocale() === 'en' ? 'Discussion deleted.' : 'Diskusi berhasil dihapus.',
        ]);
    }

    public function destroyJawaban(JawabanForum $jawaban): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $forum = Forum::find($jawaban->forum_id);
        $isForumAuthor = $forum && (int) $forum->user_id === (int) $user->id;

        if ((int) $jawaban->user_id !== (int) $user->id && ! $isForumAuthor && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en' ? 'You are not allowed to delete this reply.' : 'Anda tidak memiliki izin menghapus balasan ini.',
            ], 403);
        }

        $forumId = (int) $jawaban->forum_id;
        if (Schema::hasTable('jawaban_forum_votes')) {
            $col = Schema::hasColumn('jawaban_forum_votes', 'jawaban_id') ? 'jawaban_id' : 'jawaban_forum_id';
            DB::table('jawaban_forum_votes')->where($col, $jawaban->id)->delete();
        }
        $jawaban->delete();

        $updatedForum = Forum::with(['user', 'jawaban.user', 'jawaban.votes'])->find($forumId);

        return response()->json([
            'success' => true,
            'forum_id' => $forumId,
            'forum' => $updatedForum ? ApiResourceFormatter::formatForum($updatedForum, $user->id) : null,
            'message' => app()->getLocale() === 'en' ? 'Reply deleted.' : 'Balasan berhasil dihapus.',
        ]);
    }
}

