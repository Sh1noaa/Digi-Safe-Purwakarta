<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\SkorGame;
use App\Models\Testimoni;
use App\Models\User;
use App\Models\UserFollow;
use App\Services\AvatarBorderService;
use App\Services\SocialSchemaHelper;
use App\Services\UploadValidationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        // tampilkan halaman profil milik pengguna yang sedang login
        return $this->renderProfile(Auth::user());
    }

    public function show(string $identifier): View
    {
        // cari pengguna berdasarkan id atau username
        $profileUser = User::where('id', $identifier)
            ->orWhere('username', $identifier)
            ->firstOrFail();

        return $this->renderProfile($profileUser);
    }

    public function update(Request $request): RedirectResponse
    {
        SocialSchemaHelper::ensureReady();
        $user = Auth::user();

        // validasi input edit profil termasuk foto avatar dan banner (mendukung gif/foto)
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'specialty_role' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:350'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => UploadValidationHelper::imageRules(5120),
            'banner' => UploadValidationHelper::imageRules(6144),
        ]);

        // Wajib verifikasi password lama jika user non-Google ingin mengganti password
        if (! empty($validated['password']) && empty($user->oauth_provider)) {
            $currentPwd = (string) ($validated['current_password'] ?? '');
            if ($currentPwd === '' || ! Hash::check($currentPwd, (string) $user->password)) {
                return back()
                    ->withErrors([
                        'current_password' => app()->getLocale() === 'en'
                            ? 'Current password is required and must match your existing password.'
                            : 'Password lama wajib diisi dengan benar untuk mengganti password.',
                    ])
                    ->withInput($request->except(['password', 'current_password']));
            }
        }

        $data = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'specialty_role' => filled($validated['specialty_role'] ?? null) ? trim($validated['specialty_role']) : null,
            'bio' => $validated['bio'] ?? $user->bio,
        ];

        // hash password baru jika diisi & regenerasi session + cabut token API lama bila ada
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
            $request->session()->regenerate();
            \App\Models\ApiToken::ensureTable();
            \App\Models\ApiToken::where('user_id', $user->id)->delete();
        }

        // upload foto profil ke public/images/avatars dengan ekstensi aman & hapus file lama
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $safeExt = UploadValidationHelper::safeExtension($file);
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $safeExt;

            $targetDir = public_path('images/avatars');
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $file->move($targetDir, $filename);

            $storageDir = storage_path('app/public/avatars');
            if (! is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            @copy($targetDir . '/' . $filename, $storageDir . '/' . $filename);

            UploadValidationHelper::deleteOldAsset($user->avatar, 'avatars');
            $data['avatar'] = 'images/avatars/' . $filename;
        }

        // upload banner profil (mendukung gif maupun foto biasa) dengan ekstensi aman & hapus file lama
        if ($request->hasFile('banner')) {
            $bannerFile = $request->file('banner');
            $safeExt = UploadValidationHelper::safeExtension($bannerFile);
            $bannerName = 'banner_' . $user->id . '_' . time() . '.' . $safeExt;

            $bannerDir = public_path('images/banners');
            if (! is_dir($bannerDir)) {
                mkdir($bannerDir, 0755, true);
            }

            $bannerFile->move($bannerDir, $bannerName);

            $storageBannerDir = storage_path('app/public/banners');
            if (! is_dir($storageBannerDir)) {
                mkdir($storageBannerDir, 0755, true);
            }
            @copy($bannerDir . '/' . $bannerName, $storageBannerDir . '/' . $bannerName);

            UploadValidationHelper::deleteOldAsset($user->banner, 'banners');
            $data['banner'] = 'images/banners/' . $bannerName;
        }

        $user->update($data);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil dan tampilan banner Anda berhasil diperbarui.');
    }

    public function updateRole(Request $request): RedirectResponse|JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $user = Auth::user();

        $allowedRoles = User::availableSpecialtyRoles();
        $validated = $request->validate([
            'specialty_role' => ['nullable', 'string', 'max:60'],
        ]);

        $chosen = filled($validated['specialty_role'] ?? null) ? trim((string) $validated['specialty_role']) : null;
        if ($chosen !== null && ! in_array($chosen, $allowedRoles, true) && $chosen !== $user->specialty_role) {
            $chosen = $allowedRoles[0];
        }

        $user->update(['specialty_role' => $chosen]);

        $msg = app()->getLocale() === 'en'
            ? 'Your role has been updated successfully.'
            : 'Role Anda berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'specialty_role' => $chosen,
                'display_role' => $user->fresh()->display_role,
                'message' => $msg,
            ]);
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', $msg);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:id,en'],
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);

        $privacy = [
            'is_private_profile' => $request->boolean('is_private_profile'),
            'hide_location' => $request->boolean('hide_location'),
            'hide_email' => $request->boolean('hide_email'),
            'hide_minigame_stats' => $request->boolean('hide_minigame_stats'),
            'hide_badges' => $request->boolean('hide_badges'),
            'hide_activity' => $request->boolean('hide_activity'),
        ];

        $user->update([
            'locale' => $validated['locale'],
            'theme' => $validated['theme'],
            'privacy_settings' => $privacy,
        ]);

        session([
            'locale' => $validated['locale'],
            'theme' => $validated['theme'],
        ]);
        App::setLocale($validated['locale']);

        return redirect()
            ->route('profile.edit')
            ->with('success', __('ui.profile.settings_saved'));
    }

    /**
     * Toggle cepat tema (light/dark) atau bahasa (id/en) dari navbar untuk guest maupun user login.
     */
    public function quickPreferences(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['nullable', 'string', 'in:id,en'],
            'theme' => ['nullable', 'string', 'in:light,dark'],
        ]);

        $updates = [];
        if (! empty($validated['locale'])) {
            session(['locale' => $validated['locale']]);
            App::setLocale($validated['locale']);
            $updates['locale'] = $validated['locale'];
        }
        if (! empty($validated['theme'])) {
            session(['theme' => $validated['theme']]);
            $updates['theme'] = $validated['theme'];
        }

        if (Auth::check() && ! empty($updates)) {
            User::ensurePreferenceColumns();
            Auth::user()->update($updates);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'locale' => session('locale', 'id'),
                'theme' => session('theme', 'light'),
            ]);
        }

        return back();
    }

    /**
     * Pasang atau lepas border avatar pengguna dengan validasi syarat unlock di sisi server.
     */
    public function updateBorder(Request $request): RedirectResponse|JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        /** @var User $user */
        $user = Auth::user();
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'avatar_border' => ['nullable', 'string', 'max:40'],
        ]);

        $chosen = trim((string) ($validated['avatar_border'] ?? ''));
        if ($chosen === '' || $chosen === 'none') {
            $user->update(['avatar_border' => null]);

            $msg = $isEn ? 'Avatar border removed.' : 'Border avatar berhasil dilepas.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'avatar_border' => null,
                    'avatar_border_url' => null,
                    'message' => $msg,
                ]);
            }

            return redirect()->route('profile.edit')->with('success', $msg);
        }

        if (! in_array($chosen, AvatarBorderService::validKeys(), true)) {
            $err = $isEn ? 'Invalid avatar border selection.' : 'Pilihan border avatar tidak valid.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $err], 422);
            }

            return redirect()->route('profile.edit')->with('error', $err);
        }

        if (! AvatarBorderService::isUnlockedForUser($user, $chosen)) {
            $err = $isEn
                ? 'This border is still locked. Complete its unlock requirement first.'
                : 'Border ini masih terkunci. Selesaikan syarat pencapaiannya terlebih dahulu.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $err], 403);
            }

            return redirect()->route('profile.edit')->with('error', $err);
        }

        $user->update(['avatar_border' => $chosen]);
        $borderUrl = AvatarBorderService::imageUrl($chosen);
        $borderLabel = AvatarBorderService::label($chosen);
        $msg = $isEn
            ? 'Avatar border "' . $borderLabel . '" equipped!'
            : 'Border avatar "' . $borderLabel . '" berhasil dipasang!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'avatar_border' => $chosen,
                'avatar_border_url' => $borderUrl,
                'avatar_border_name' => $borderLabel,
                'message' => $msg,
            ]);
        }

        return redirect()->route('profile.edit')->with('success', $msg);
    }

    protected function renderProfile(User $profileUser): View
    {
        SocialSchemaHelper::ensureReady();
        $isOwnProfile = Auth::check() && Auth::id() === $profileUser->id;
        $privacy = $profileUser->privacy_config;
        $isPrivateForVisitor = ! $isOwnProfile && $privacy['is_private_profile'];
        $friendRelation = Auth::check() && ! $isOwnProfile
            ? Friendship::relationFor(Auth::id(), (int) $profileUser->id)['status']
            : 'none';

        // hitung statistik kontribusi user
        $totalSkor = (int) SkorGame::where('user_id', $profileUser->id)->sum('skor');
        $topSkor = (int) SkorGame::where('user_id', $profileUser->id)->max('skor_tertinggi');
        $totalMain = SkorGame::where('user_id', $profileUser->id)->count();

        $totalUlasan = Testimoni::where('user_id', $profileUser->id)->count();
        $totalThread = Forum::where('user_id', $profileUser->id)->count();
        $totalJawaban = JawabanForum::where('user_id', $profileUser->id)->count();
        $totalDiskusi = $totalThread + $totalJawaban;

        $badges = $profileUser->badges_list;

        $friendsList = Friendship::with(['requester', 'addressee'])
            ->where('status', 'accepted')
            ->where(function ($q) use ($profileUser) {
                $q->where('requester_id', $profileUser->id)
                    ->orWhere('addressee_id', $profileUser->id);
            })
            ->get()
            ->map(fn (Friendship $f) => (int) $f->requester_id === (int) $profileUser->id ? $f->addressee : $f->requester)
            ->filter()
            ->values();
        $friendsCount = $friendsList->count();

        $followersCount = UserFollow::where('following_id', $profileUser->id)->count();
        $followingCount = UserFollow::where('follower_id', $profileUser->id)->count();
        $isFollowingProfile = Auth::check() && ! $isOwnProfile
            ? UserFollow::where('follower_id', Auth::id())->where('following_id', $profileUser->id)->exists()
            : false;

        $avatarBorders = AvatarBorderService::evaluateForUser($profileUser);

        return view('profile.show', compact(
            'profileUser',
            'isOwnProfile',
            'privacy',
            'isPrivateForVisitor',
            'totalSkor',
            'topSkor',
            'totalMain',
            'totalUlasan',
            'totalDiskusi',
            'totalThread',
            'totalJawaban',
            'badges',
            'friendRelation',
            'friendsList',
            'friendsCount',
            'followersCount',
            'followingCount',
            'isFollowingProfile',
            'avatarBorders'
        ));
    }
}
