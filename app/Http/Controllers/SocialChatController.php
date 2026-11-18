<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Friendship;
use App\Models\User;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialChatController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        $unreadMsgs = ChatMessage::where('receiver_id', $me)
            ->where('is_read', false)
            ->count();

        $pendingReqs = Friendship::where('addressee_id', $me)
            ->where('status', 'pending')
            ->count();

        $latestUnread = ChatMessage::with('sender:id,nama_lengkap,username,email,avatar')
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->latest('id')
            ->first();

        return response()->json([
            'unread_messages' => $unreadMsgs,
            'pending_requests' => $pendingReqs,
            'total_badge' => $unreadMsgs + $pendingReqs,
            'latest_alert' => $latestUnread && $latestUnread->sender ? [
                'message_id' => (int) $latestUnread->id,
                'sender_id' => $latestUnread->sender->id,
                'sender_name' => $latestUnread->sender->nama_lengkap ?: $latestUnread->sender->username,
                'sender_avatar' => $latestUnread->sender->avatar_url,
                'is_bot' => $latestUnread->sender->isBot(),
                'preview' => mb_strimwidth($latestUnread->message, 0, 56, '...'),
            ] : null,
        ]);
    }

    public function summary(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        // Daftar seluruh user selain diri sendiri yang terlibat di chat atau pertemanan
        $partnerIds = ChatMessage::where('sender_id', $me)
            ->pluck('receiver_id')
            ->merge(ChatMessage::where('receiver_id', $me)->pluck('sender_id'))
            ->merge(Friendship::where('requester_id', $me)->pluck('addressee_id'))
            ->merge(Friendship::where('addressee_id', $me)->pluck('requester_id'))
            ->unique()
            ->filter(fn ($id) => (int) $id !== (int) $me)
            ->values();

        $partners = User::whereIn('id', $partnerIds)->get()->keyBy('id');
        $threads = [];

        foreach ($partnerIds as $pid) {
            /** @var User|null $partner */
            $partner = $partners->get($pid);
            if (! $partner) {
                continue;
            }

            $rel = Friendship::relationFor($me, (int) $pid);
            $lastMsg = ChatMessage::where(function ($q) use ($me, $pid) {
                $q->where('sender_id', $me)->where('receiver_id', $pid);
            })->orWhere(function ($q) use ($me, $pid) {
                $q->where('sender_id', $pid)->where('receiver_id', $me);
            })->latest('id')->first();

            // Tampilkan di daftar obrolan jika sudah ada pesan atau sudah berteman
            if ($lastMsg || $rel['status'] === 'accepted') {
                $unread = ChatMessage::where('sender_id', $pid)
                    ->where('receiver_id', $me)
                    ->where('is_read', false)
                    ->count();

                $threads[] = [
                    'user' => $this->formatCompactUser($partner),
                    'relation' => $rel['status'],
                    'last_message' => $lastMsg ? $lastMsg->message : null,
                    'last_sender_me' => $lastMsg ? ((int) $lastMsg->sender_id === (int) $me) : false,
                    'last_time' => $lastMsg?->created_at?->timezone('Asia/Jakarta')->format('H:i') . ($lastMsg ? ' WIB' : ''),
                    'sort_ts' => $lastMsg?->created_at?->timestamp ?? 0,
                    'unread_count' => $unread,
                ];
            }
        }

        usort($threads, fn ($a, $b) => $b['sort_ts'] <=> $a['sort_ts']);

        // Permintaan masuk (pertemanan maupun chat pertama dari user yang belum berteman)
        $incomingReqs = Friendship::with('requester')
            ->where('addressee_id', $me)
            ->where('status', 'pending')
            ->latest('updated_at')
            ->get()
            ->map(function (Friendship $f) use ($me) {
                if (! $f->requester) {
                    return null;
                }
                $firstMsg = ChatMessage::where('sender_id', $f->requester_id)
                    ->where('receiver_id', $me)
                    ->latest('id')
                    ->first();

                return [
                    'friendship_id' => $f->id,
                    'user' => $this->formatCompactUser($f->requester),
                    'message_preview' => $firstMsg?->message,
                    'time' => $f->updated_at?->timezone('Asia/Jakarta')->format('d M, H:i') . ' WIB',
                ];
            })
            ->filter()
            ->values();

        // Daftar teman yang sudah dikonfirmasi
        $friends = Friendship::with(['requester', 'addressee'])
            ->where('status', 'accepted')
            ->where(function ($q) use ($me) {
                $q->where('requester_id', $me)->orWhere('addressee_id', $me);
            })
            ->get()
            ->map(function (Friendship $f) use ($me) {
                $other = (int) $f->requester_id === (int) $me ? $f->addressee : $f->requester;

                return $other ? $this->formatCompactUser($other) : null;
            })
            ->filter()
            ->values();

        $unreadMsgs = ChatMessage::where('receiver_id', $me)->where('is_read', false)->count();

        return response()->json([
            'threads' => $threads,
            'requests' => $incomingReqs,
            'friends' => $friends,
            'unread_messages' => $unreadMsgs,
            'pending_requests' => $incomingReqs->count(),
            'total_badge' => $unreadMsgs + $incomingReqs->count(),
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();
        $q = trim((string) $request->query('q', ''));

        $usersQuery = User::humans()->where('id', '!=', $me);
        if ($q !== '') {
            $usersQuery->where(function ($sub) use ($q) {
                $sub->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('specialty_role', 'like', "%{$q}%");
            });
        }

        $users = $usersQuery->orderByDesc('id')->limit(24)->get()->map(function (User $u) use ($me) {
            $rel = Friendship::relationFor($me, (int) $u->id);

            return [
                'user' => $this->formatCompactUser($u),
                'relation' => $rel['status'],
            ];
        })->values();

        return response()->json(['users' => $users]);
    }

    public function messages(User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        if ((int) $user->id === (int) $me) {
            return response()->json(['message' => 'Tidak dapat membuka obrolan dengan akun sendiri.'], 422);
        }

        ChatMessage::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $rel = Friendship::relationFor($me, (int) $user->id);

        $messages = ChatMessage::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me);
        })->orderBy('id')->get()->map(fn (ChatMessage $m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'is_mine' => (int) $m->sender_id === (int) $me,
            'message' => $m->message,
            'time' => $m->created_at?->timezone('Asia/Jakarta')->format('H:i') . ' WIB',
        ]);

        return response()->json([
            'user' => $this->formatCompactUser($user),
            'relation' => $rel['status'],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        if ((int) $user->id === (int) $me) {
            return response()->json(['message' => 'Tidak dapat mengirim pesan ke akun sendiri.'], 422);
        }

        if ($user->isBot()) {
            return response()->json(['message' => 'Bot Digika adalah sistem notifikasi otomatis dan tidak menerima balasan langsung.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $rel = Friendship::relationFor($me, (int) $user->id);
        $status = $rel['status'];

        if ($status === 'declined') {
            return response()->json(['message' => 'Permintaan obrolan ditolak oleh pengguna ini.'], 403);
        }

        if ($status === 'pending_incoming') {
            return response()->json(['message' => 'Silakan konfirmasi permintaan pertemanan terlebih dahulu untuk membalas.'], 403);
        }

        if ($status === 'none') {
            Friendship::create([
                'requester_id' => $me,
                'addressee_id' => $user->id,
                'status' => 'pending',
            ]);
            $status = 'pending_outgoing';
        } elseif ($status === 'pending_outgoing') {
            $sentCount = ChatMessage::where('sender_id', $me)->where('receiver_id', $user->id)->count();
            if ($sentCount >= 3) {
                return response()->json(['message' => 'Menunggu persetujuan pengguna sebelum mengirim pesan lanjutan.'], 403);
            }
        }

        $msg = ChatMessage::create([
            'sender_id' => $me,
            'receiver_id' => $user->id,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        return response()->json([
            'relation' => $status,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'is_mine' => true,
                'message' => $msg->message,
                'time' => $msg->created_at?->timezone('Asia/Jakarta')->format('H:i') . ' WIB',
            ],
        ]);
    }

    public function sendFriendRequest(User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        if ((int) $user->id === (int) $me) {
            return response()->json(['message' => 'Tidak bisa menambahkan diri sendiri.'], 422);
        }

        if ($user->isBot()) {
            return response()->json(['message' => 'Bot Digika adalah asisten otomatis sistem.'], 422);
        }

        $existing = Friendship::between($me, (int) $user->id);
        if ($existing) {
            if ($existing->status === 'pending' && (int) $existing->addressee_id === (int) $me) {
                $existing->update(['status' => 'accepted']);

                return response()->json(['status' => 'accepted', 'message' => 'Permintaan pertemanan dikonfirmasi.']);
            }
            if ($existing->status === 'declined' && (int) $existing->addressee_id === (int) $me) {
                // Jika pihak yang dulu menolak kini yang mengajak berteman, izinkan ulang
                $existing->update([
                    'requester_id' => $me,
                    'addressee_id' => $user->id,
                    'status' => 'pending',
                ]);

                return response()->json(['status' => 'pending_outgoing', 'message' => 'Permintaan pertemanan dikirim.']);
            }

            $rel = Friendship::relationFor($me, (int) $user->id);

            return response()->json(['status' => $rel['status'], 'message' => 'Status pertemanan sudah tercatat.']);
        }

        Friendship::create([
            'requester_id' => $me,
            'addressee_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'pending_outgoing', 'message' => 'Permintaan pertemanan dikirim.']);
    }

    public function acceptFriendRequest(User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        $f = Friendship::where('requester_id', $user->id)
            ->where('addressee_id', $me)
            ->where('status', 'pending')
            ->first();

        if (! $f) {
            return response()->json(['message' => 'Permintaan pertemanan tidak ditemukan.'], 404);
        }

        $f->update(['status' => 'accepted']);

        return response()->json(['status' => 'accepted', 'message' => 'Pertemanan dikonfirmasi! Sekarang kalian dapat mengobrol bebas.']);
    }

    public function declineFriendRequest(User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        $f = Friendship::where('requester_id', $user->id)
            ->where('addressee_id', $me)
            ->where('status', 'pending')
            ->first();

        if (! $f) {
            return response()->json(['message' => 'Permintaan tidak ditemukan.'], 404);
        }

        $f->update(['status' => 'declined']);

        ChatMessage::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'declined', 'message' => 'Permintaan obrolan ditolak.']);
    }

    public function removeFriendship(User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $me = Auth::id();

        $f = Friendship::between($me, (int) $user->id);
        if ($f && $f->status !== 'declined') {
            $f->delete();
        }

        return response()->json(['status' => 'none', 'message' => 'Pertemanan dihapus.']);
    }

    private function formatCompactUser(User $u): array
    {
        return [
            'id' => (int) $u->id,
            'nama' => $u->nama_lengkap ?: $u->username,
            'username' => $u->username,
            'inisial' => $u->inisial,
            'avatar_url' => $u->avatar_url,
            'pastel_class' => $u->pastel_class,
            'specialty_role' => trim((string) ($u->specialty_role ?? '')) ?: null,
            'role_label' => $u->display_role,
            'is_bot' => $u->isBot(),
            'profile_url' => route('profile.show', $u->id),
        ];
    }
}

