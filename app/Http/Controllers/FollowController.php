<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFollow;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(Request $request, User $user): JsonResponse|RedirectResponse
    {
        SocialSchemaHelper::ensureReady();
        $meId = (int) Auth::id();

        if ($meId === (int) $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Tidak dapat mengikuti akun sendiri.'], 422);
            }

            return back()->with('error', 'Tidak dapat mengikuti akun sendiri.');
        }

        if ($user->isBot()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Akun sistem tidak dapat diikuti.'], 422);
            }

            return back()->with('error', 'Akun sistem tidak dapat diikuti.');
        }

        $existing = UserFollow::where('follower_id', $meId)
            ->where('following_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            UserFollow::create([
                'follower_id' => $meId,
                'following_id' => $user->id,
            ]);
            $following = true;
        }

        $targetFollowersCount = UserFollow::where('following_id', $user->id)->count();
        $myFollowingCount = UserFollow::where('follower_id', $meId)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user_id' => (int) $user->id,
                'following' => $following,
                'followers_count' => $targetFollowersCount,
                'my_following_count' => $myFollowingCount,
            ]);
        }

        return back();
    }

    public function list(Request $request, User $user): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $meId = Auth::id();
        $viewer = Auth::user();
        $isOwnerOrAdmin = $meId !== null && ((int) $meId === (int) $user->id || (bool) $viewer?->hasAdminAccess());

        if (($user->privacy_config['is_private_profile'] ?? false) && ! $isOwnerOrAdmin) {
            return response()->json([
                'is_private' => true,
                'message' => app()->getLocale() === 'en'
                    ? 'This account is private.'
                    : 'Daftar pengikut disembunyikan karena profil ini bersifat privat.',
                'followers' => [],
                'following' => [],
                'followers_count' => 0,
                'following_count' => 0,
            ], 403);
        }

        $myFollowingIds = $meId
            ? UserFollow::where('follower_id', $meId)->pluck('following_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $formatUser = function (User $u) use ($meId, $myFollowingIds) {
            return [
                'id' => (int) $u->id,
                'nama' => $u->nama_lengkap ?: $u->username,
                'username' => $u->username,
                'inisial' => $u->inisial,
                'avatar_url' => $u->avatar_url,
                'avatar_border_url' => $u->avatar_border_url,
                'pastel_class' => $u->pastel_class,
                'specialty_role' => $u->specialty_role,
                'profile_url' => route('profile.show', $u->id),
                'is_me' => $meId && ((int) $u->id === (int) $meId),
                'is_following' => in_array((int) $u->id, $myFollowingIds, true),
            ];
        };

        $followers = $user->followers()->orderByDesc('user_follows.id')->get()->map($formatUser)->values();
        $following = $user->following()->orderByDesc('user_follows.id')->get()->map($formatUser)->values();

        return response()->json([
            'followers' => $followers,
            'following' => $following,
            'followers_count' => $followers->count(),
            'following_count' => $following->count(),
        ]);
    }
}
