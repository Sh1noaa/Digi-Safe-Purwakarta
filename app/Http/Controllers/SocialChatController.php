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
        $me = (int) Auth::id();

        // 1. Ambil seluruh relasi pertemanan user dalam 1 query (dengan eager load requester & addressee)
        $allFriendships = Friendship::with(['requester', 'addressee'])
            ->where('requester_id', $me)
            ->orWhere('addressee_id', $me)
            ->orderByDesc('updated_at')
            ->get();

        $relationMap = [];
        $incomingReqsRaw = collect();
        $friends = collect();

        foreach ($allFriendships as $f) {
            $isRequester = (int) $f->requester_id === $me;
            $otherId = $isRequester ? (int) $f->addressee_id : (int) $f->requester_id;

            if ($f->status === 'accepted') {
                $relationMap[$otherId] = 'accepted';
                $other = $isRequester ? $f->addressee : $f->requester;
                if ($other) {
                    $friends->push($this->formatCompactUser($other));
                }
            } elseif ($f->status === 'pending') {
                $relationMap[$otherId] = $isRequester ? 'pending_sent' : 'pending_received';
                if (! $isRequester && $f->requester) {
                    $incomingReqsRaw->push($f);
                }
            } else {
                $relationMap[$otherId] = $relationMap[$otherId] ?? 'none';
            }
        }

        // 2. Hitung pesan belum dibaca per pengirim dalam 1 query agregat
        $unreadBySender = ChatMessage::where('receiver_id', $me)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        // 3. Ambil pesan terakhir untuk setiap lawan bicara dalam 1 query tanpa N+1
        $latestMessageIds = ChatMessage::where('sender_id', $me)
            ->orWhere('receiver_id', $me)
            ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as partner_id, MAX(id) as max_id', [$me])
            ->groupBy('partner_id')
            ->pluck('max_id', 'partner_id');

        $latestMessagesByPartner = [];
        if ($latestMessageIds->isNotEmpty()) {
            $msgs = ChatMessage::whereIn('id', $latestMessageIds->values()->all())->get()->keyBy('id');
            foreach ($latestMessageIds as $pid => $msgId) {
                if (isset($msgs[$msgId])) {
                    $latestMessagesByPartner[(int) $pid] = $msgs[$msgId];
                }
            }
        }

        // 4. Gabungkan seluruh partner unik dan ambil profilnya dalam 1 query
        $partnerIds = collect(array_keys($latestMessagesByPartner))
            ->merge(array_keys($relationMap))
            ->unique()
            ->filter(fn ($id) => (int) $id !== $me)
            ->values();

        $partners = $partnerIds->isNotEmpty()
            ? User::whereIn('id', $partnerIds)->get()->keyBy('id')
            : collect();

        $threads = [];
        foreach ($partnerIds as $pid) {
            /** @var User|null $partner */
            $partner = $partners->get($pid);
            if (! $partner) {
                continue;
            }

            $relStatus = $relationMap[(int) $pid] ?? 'none';
            $lastMsg = $latestMessagesByPartner[(int) $pid] ?? null;

            if ($lastMsg || $relStatus === 'accepted') {
                $unread = (int) ($unreadBySender[$pid] ?? 0);
                $lastMeta = $this->formatChatDateMeta($lastMsg?->created_at);

                $threads[] = [
                    'user' => $this->formatCompactUser($partner),
                    'relation' => $relStatus,
                    'last_message' => $lastMsg ? $lastMsg->message : null,
                    'last_sender_me' => $lastMsg ? ((int) $lastMsg->sender_id === $me) : false,
                    'last_time' => $lastMeta['list_label'],
                    'last_full_date' => $lastMeta['full_datetime'],
                    'sort_ts' => $lastMsg?->created_at?->timestamp ?? 0,
                    'unread_count' => $unread,
                ];
            }
        }

        usort($threads, fn ($a, $b) => $b['sort_ts'] <=> $a['sort_ts']);

        // Format permintaan masuk menggunakan data pesan terakhir yang sudah di-batch
        $incomingReqs = $incomingReqsRaw->map(function (Friendship $f) use ($latestMessagesByPartner) {
            $firstMsg = $latestMessagesByPartner[(int) $f->requester_id] ?? null;

            return [
                'friendship_id' => $f->id,
                'user' => $this->formatCompactUser($f->requester),
                'message_preview' => $firstMsg?->message,
                'time' => $f->updated_at?->timezone('Asia/Jakarta')->format('d M, H:i') . ' WIB',
            ];
        })->values();

        $unreadMsgs = (int) $unreadBySender->sum();

        return response()->json([
            'threads' => $threads,
            'requests' => $incomingReqs,
            'friends' => $friends->values(),
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
            $escaped = str_replace(['\\', '%'], ['\\\\', '\\%'], $q);
            $usersQuery->where(function ($sub) use ($escaped) {
                $sub->where('nama_lengkap', 'like', "%{$escaped}%")
                    ->orWhere('username', 'like', "%{$escaped}%")
                    ->orWhere('specialty_role', 'like', "%{$escaped}%");
            });
        }

        $matchedUsers = $usersQuery->orderByDesc('id')->limit(24)->get();
        $matchedIds = $matchedUsers->pluck('id')->all();

        // Batch load relasi pertemanan dalam 1 query agar tidak terjadi N+1
        $relationMap = [];
        if (! empty($matchedIds)) {
            $friendships = Friendship::where(function ($q) use ($me, $matchedIds) {
                $q->where('requester_id', $me)->whereIn('addressee_id', $matchedIds);
            })->orWhere(function ($q) use ($me, $matchedIds) {
                $q->where('addressee_id', $me)->whereIn('requester_id', $matchedIds);
            })->get();

            foreach ($friendships as $f) {
                $isReq = (int) $f->requester_id === (int) $me;
                $otherId = $isReq ? (int) $f->addressee_id : (int) $f->requester_id;
                if ($f->status === 'accepted') {
                    $relationMap[$otherId] = 'accepted';
                } elseif ($f->status === 'pending') {
                    $relationMap[$otherId] = $isReq ? 'pending_outgoing' : 'pending_incoming';
                } elseif ($f->status === 'declined') {
                    $relationMap[$otherId] = 'declined';
                }
            }
        }

        $users = $matchedUsers->map(function (User $u) use ($relationMap) {
            return [
                'user' => $this->formatCompactUser($u),
                'relation' => $relationMap[(int) $u->id] ?? 'none',
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
        })->orderByDesc('id')->limit(120)->get()->reverse()->values()->map(function (ChatMessage $m) use ($me) {
            $meta = $this->formatChatDateMeta($m->created_at);

            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_mine' => (int) $m->sender_id === (int) $me,
                'message' => $m->message,
                'time' => $meta['time'],
                'date_key' => $meta['date_key'],
                'date_label' => $meta['date_label'],
                'full_datetime' => $meta['full_datetime'],
            ];
        });

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

        $meta = $this->formatChatDateMeta($msg->created_at);

        return response()->json([
            'relation' => $status,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'is_mine' => true,
                'message' => $msg->message,
                'time' => $meta['time'],
                'date_key' => $meta['date_key'],
                'date_label' => $meta['date_label'],
                'full_datetime' => $meta['full_datetime'],
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

    private function formatChatDateMeta(?\Illuminate\Support\Carbon $timestamp): array
    {
        if (! $timestamp) {
            return [
                'time' => '',
                'date_key' => '',
                'date_label' => '',
                'full_datetime' => '',
                'list_label' => '',
            ];
        }

        $isEn = app()->getLocale() === 'en';
        $dt = $timestamp->copy()->timezone('Asia/Jakarta');
        $now = now('Asia/Jakarta');

        $monthsId = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $monthsShortId = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $day = $dt->day;
        $monthFull = $isEn ? $dt->format('F') : ($monthsId[$dt->month] ?? $dt->format('F'));
        $monthShort = $isEn ? $dt->format('M') : ($monthsShortId[$dt->month] ?? $dt->format('M'));
        $year = $dt->year;
        $timeStr = $dt->format('H:i') . ' WIB';
        $exactDate = "{$day} {$monthFull} {$year}";
        $shortDate = "{$day} {$monthShort} {$year}";

        if ($dt->isSameDay($now)) {
            $dateLabel = $isEn ? "Today, {$exactDate}" : "Hari ini, {$exactDate}";
            $listLabel = $dt->format('H:i');
        } elseif ($dt->isSameDay($now->copy()->subDay())) {
            $dateLabel = $isEn ? "Yesterday, {$exactDate}" : "Kemarin, {$exactDate}";
            $listLabel = $isEn ? 'Yesterday' : 'Kemarin';
        } else {
            $dateLabel = $exactDate;
            $listLabel = $shortDate;
        }

        return [
            'time' => $timeStr,
            'date_key' => $dt->format('Y-m-d'),
            'date_label' => $dateLabel,
            'full_datetime' => "{$exactDate}, {$timeStr}",
            'list_label' => $listLabel,
        ];
    }

    private function formatCompactUser(User $u): array
    {
        $isSelf = Auth::check() && (int) Auth::id() === (int) $u->id;
        $isPrivate = ! $isSelf && (bool) ($u->privacy_config['is_private_profile'] ?? false);
        $isEn = app()->getLocale() === 'en';

        return [
            'id' => (int) $u->id,
            'nama' => $u->nama_lengkap ?: $u->username,
            'username' => $u->username,
            'inisial' => $u->inisial,
            'avatar_url' => $u->avatar_url,
            'avatar_border_url' => $u->avatar_border_url,
            'pastel_class' => $u->pastel_class,
            'specialty_role' => $isPrivate ? null : (trim((string) ($u->specialty_role ?? '')) ?: null),
            'role_label' => $isPrivate ? ($isEn ? 'Private Account' : 'Akun Privat') : $u->display_role,
            'is_private_profile' => $isPrivate,
            'is_bot' => $u->isBot(),
            'profile_url' => route('profile.show', $u->id),
        ];
    }
}

