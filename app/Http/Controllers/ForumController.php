<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\JawabanForumVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        JawabanForumVote::ensureTable();

        $sort = $request->query('sort', 'terbaru');
        $forums = $this->fetchForumsCollection($sort);
        $forumsData = $this->formatForumsCollection($forums, Auth::id());

        return view('forum.index', compact('forums', 'forumsData', 'sort'));
    }

    /**
     * Endpoint JSON untuk sinkronisasi real-time daftar forum, balasan, vote 👍/👎, dan status Most Helpful.
     */
    public function liveData(Request $request): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $sort = $request->query('sort', 'terbaru');
        $forums = $this->fetchForumsCollection($sort);
        $forumsData = $this->formatForumsCollection($forums, Auth::id());

        return response()->json([
            'success' => true,
            'timestamp' => now()->timestamp,
            'forums' => $forumsData,
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
        $forum->jawaban_count = 0;

        return response()->json([
            'success' => true,
            'message' => __('ui.forum.publish_discussion') . ' — ' . (app()->getLocale() === 'en' ? 'Published successfully!' : 'Diskusi baru berhasil diterbitkan!'),
            'forum' => $this->formatForumItem($forum, $user->id),
        ]);
    }

    public function update(Request $request, Forum $forum): JsonResponse
    {
        $user = Auth::user();
        if ((int) $forum->user_id !== (int) $user->id && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'You can only edit your own discussion.'
                    : 'Anda hanya dapat mengedit diskusi milik Anda sendiri.',
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
        $forum->jawaban_count = $forum->jawaban->count();

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en'
                ? 'Discussion updated successfully.'
                : 'Diskusi berhasil diperbarui.',
            'forum' => $this->formatForumItem($forum, $user->id),
        ]);
    }

    public function destroy(Forum $forum): JsonResponse
    {
        $user = Auth::user();
        if ((int) $forum->user_id !== (int) $user->id && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'You can only delete your own discussion.'
                    : 'Anda hanya dapat menghapus diskusi milik Anda sendiri.',
            ], 403);
        }

        $jawabanIds = $forum->jawaban()->pluck('id');
        if ($jawabanIds->isNotEmpty()) {
            JawabanForumVote::whereIn('jawaban_id', $jawabanIds)->delete();
        }
        $forum->jawaban()->delete();
        $forum->delete();

        return response()->json([
            'success' => true,
            'deleted_id' => $forum->id,
            'message' => app()->getLocale() === 'en'
                ? 'Discussion deleted successfully.'
                : 'Diskusi berhasil dihapus.',
        ]);
    }

    public function destroyJawaban(JawabanForum $jawaban): JsonResponse
    {
        $user = Auth::user();
        if ((int) $jawaban->user_id !== (int) $user->id && ! $user->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'You can only delete your own reply.'
                    : 'Anda hanya dapat menghapus balasan milik Anda sendiri.',
            ], 403);
        }

        $forum = $jawaban->forum;
        JawabanForumVote::where('jawaban_id', $jawaban->id)->delete();
        $jawaban->delete();

        $formattedForum = null;
        if ($forum) {
            $forum->load(['user', 'jawaban.user', 'jawaban.votes']);
            $forum->jawaban_count = $forum->jawaban->count();
            $formattedForum = $this->formatForumItem($forum, $user->id);
        }

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en'
                ? 'Reply deleted successfully.'
                : 'Balasan berhasil dihapus.',
            'forum' => $formattedForum,
        ]);
    }

    public function storeJawaban(Request $request, Forum $forum): JsonResponse
    {
        JawabanForumVote::ensureTable();

        $validated = $request->validate([
            'jawaban' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        $jawaban = JawabanForum::create([
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
        $forum->jawaban_count = $forum->jawaban->count();
        $formattedForum = $this->formatForumItem($forum, $user->id);
        $formattedJawaban = collect($formattedForum['jawaban'])->firstWhere('id', $jawaban->id);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Your reply has been posted!' : 'Pendapat Anda berhasil dikirim!',
            'jawaban' => $formattedJawaban,
            'forum' => $formattedForum,
        ]);
    }

    /**
     * Simpan atau toggle vote 👍 (up) / 👎 (down) — maksimal 1 vote per user pada tiap pesan balasan.
     */
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
        // vote jawaban
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
            ->withCount('jawaban')
            ->findOrFail($jawaban->forum_id);

        $formattedForum = $this->formatForumItem($forum, $userId);

        return response()->json([
            'success' => true,
            'action' => $action,
            'forum' => $formattedForum,
        ]);
    }

    private function fetchForumsCollection(string $sort): Collection
    {
        $query = Forum::where('is_hidden', false)
            ->with(['user', 'jawaban.user', 'jawaban.votes'])
            ->withCount('jawaban')
            ->orderByDesc('is_starred');

        if ($sort === 'membantu') {
            $query->orderByDesc('jawaban_count')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at')->orderByDesc('id');
        }

        return $query->get();
    }

    private function formatForumsCollection(Collection $forums, ?int $currentUserId): Collection
    {
        return $forums->map(fn (Forum $item) => $this->formatForumItem($item, $currentUserId))->values();
    }

    private function formatForumItem(Forum $item, ?int $currentUserId): array
    {
        $authorName = $item->user?->nama_lengkap ?: ($item->user?->username ?: __('ui.forum.citizen_purwakarta'));

        // Hitung vote tiap pesan dan tentukan pesan mana yang menjadi "Most Helpful"
        $bestJawabanId = null;
        $bestUpCount = 0;
        $bestNetScore = 0;

        $totalUp = 0;
        $totalDown = 0;

        $scoredReplies = $item->jawaban->map(function (JawabanForum $ans) use ($currentUserId, &$totalUp, &$totalDown, &$bestJawabanId, &$bestUpCount, &$bestNetScore) {
            $upCount = $ans->votes->where('vote_type', 'up')->count();
            $downCount = $ans->votes->where('vote_type', 'down')->count();
            $netScore = $upCount - $downCount;
            $userVote = $currentUserId
                ? ($ans->votes->firstWhere('user_id', $currentUserId)?->vote_type ?? null)
                : null;

            $totalUp += $upCount;
            $totalDown += $downCount;

            // Pesan terbanyak 👍 (dengan net score positif / lebih banyak baik dari buruk) menjadi Most Helpful
            if ($upCount > 0 && $netScore > 0) {
                if (
                    $bestJawabanId === null
                    || $upCount > $bestUpCount
                    || ($upCount === $bestUpCount && $netScore > $bestNetScore)
                ) {
                    $bestJawabanId = $ans->id;
                    $bestUpCount = $upCount;
                    $bestNetScore = $netScore;
                }
            }

            return [
                'model' => $ans,
                'up_count' => $upCount,
                'down_count' => $downCount,
                'net_score' => $netScore,
                'user_vote' => $userVote,
            ];
        });

        $currentUserIsAdmin = $currentUserId !== null && (bool) Auth::user()?->hasAdminAccess();

        $formattedJawaban = $scoredReplies->map(function (array $entry) use ($bestJawabanId, $currentUserId, $currentUserIsAdmin) {
            /** @var JawabanForum $ans */
            $ans = $entry['model'];
            $ansName = $ans->user?->nama_lengkap ?: ($ans->user?->username ?: __('ui.forum.community_member'));
            $isOwner = (bool) $ans->user?->isOwner();

            return [
                'id' => $ans->id,
                'jawaban' => $ans->jawaban,
                'is_owner' => $isOwner,
                'is_admin' => ! $isOwner && (bool) ($ans->is_admin || $ans->user?->isAdmin()),
                'can_manage' => $currentUserId !== null && ((int) $ans->user_id === (int) $currentUserId || $currentUserIsAdmin),
                'waktu' => $ans->created_at ? $ans->created_at->diffForHumans() : __('ui.forum.just_now'),
                'user_name' => $ansName,
                'user_initial' => mb_strtoupper(mb_substr(trim($ansName), 0, 1)),
                'user_avatar' => $ans->user?->avatar_url,
                'user_border_url' => $ans->user?->avatar_border_url,
                'user_pastel' => $ans->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
                'user_profile_url' => $ans->user ? route('profile.show', $ans->user->id) : '#',
                'up_count' => $entry['up_count'],
                'down_count' => $entry['down_count'],
                'net_score' => $entry['net_score'],
                'user_vote' => $entry['user_vote'],
                'is_most_helpful' => $bestJawabanId !== null && $ans->id === $bestJawabanId,
            ];
        })->values();

        return [
            'id' => $item->id,
            'judul' => $item->judul,
            'pertanyaan' => $item->pertanyaan,
            'kategori' => $item->kategori ?: 'Umum',
            'status' => $item->status ?: 'terbuka',
            'is_starred' => (bool) $item->is_starred,
            'can_manage' => $currentUserId !== null && ((int) $item->user_id === (int) $currentUserId || $currentUserIsAdmin),
            'views' => (int) $item->views,
            'created_ts' => $item->created_at ? $item->created_at->timestamp : 0,
            'waktu' => $item->created_at ? $item->created_at->diffForHumans() : __('ui.forum.just_now'),
            'author_name' => $authorName,
            'author_initial' => mb_strtoupper(mb_substr(trim($authorName), 0, 1)),
            'author_avatar' => $item->user?->avatar_url,
            'author_border_url' => $item->user?->avatar_border_url,
            'author_pastel' => $item->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
            'author_profile_url' => $item->user ? route('profile.show', $item->user->id) : '#',
            'author_is_owner' => (bool) $item->user?->isOwner(),
            'author_is_admin' => (bool) $item->user?->isAdmin(),
            'jawaban_count' => (int) ($item->jawaban_count ?? $item->jawaban->count()),
            'total_up' => $totalUp,
            'total_down' => $totalDown,
            'helpful_score' => $totalUp - $totalDown,
            'has_most_helpful' => $bestJawabanId !== null,
            'most_helpful_up' => $bestUpCount,
            'jawaban' => $formattedJawaban,
        ];
    }
}
