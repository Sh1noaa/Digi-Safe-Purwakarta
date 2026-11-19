<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Services\ApiResourceFormatter;
use App\Services\AvatarBorderService;
use App\Services\SocialSchemaHelper;
use App\Services\UploadValidationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isEn = app()->getLocale() === 'en';

        SocialSchemaHelper::ensureReady();
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'specialty_role' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:350'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => UploadValidationHelper::imageRules(3072),
            'banner' => UploadValidationHelper::imageRules(5120),
        ]);

        $data = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => filled($validated['username'] ?? null) ? trim((string) $validated['username']) : $user->username,
            'specialty_role' => filled($validated['specialty_role'] ?? null) ? trim($validated['specialty_role']) : $user->specialty_role,
            'bio' => $validated['bio'] ?? $user->bio,
        ];

        $passwordChanged = false;
        if (! empty($validated['password'])) {
            if (! Hash::check((string) ($validated['current_password'] ?? ''), (string) $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => $isEn ? 'Current password does not match.' : 'Kata sandi saat ini tidak sesuai.',
                    'errors' => [
                        'current_password' => [$isEn ? 'Current password does not match.' : 'Kata sandi saat ini tidak sesuai.'],
                    ],
                ], 422);
            }
            $data['password'] = Hash::make($validated['password']);
            $passwordChanged = true;
        }

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
        if ($passwordChanged) {
            ApiToken::ensureTable();
            ApiToken::where('user_id', $user->id)->delete();
        }
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => $isEn ? 'Profile updated successfully.' : 'Profil dan tampilan banner berhasil diperbarui.',
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        /** @var User $user */
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

        App::setLocale($validated['locale']);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => __('ui.profile.settings_saved'),
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function updateRole(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        SocialSchemaHelper::ensureReady();

        $allowed = User::availableSpecialtyRoles();
        $validated = $request->validate([
            'specialty_role' => ['nullable', 'string', 'max:60'],
        ]);

        $chosen = filled($validated['specialty_role'] ?? null) ? trim((string) $validated['specialty_role']) : null;
        if ($chosen !== null && ! in_array($chosen, $allowed, true) && $chosen !== $user->specialty_role) {
            $chosen = $allowed[0];
        }

        $user->update(['specialty_role' => $chosen]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Your role has been updated.' : 'Role komunitas berhasil diperbarui.',
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function updateBorder(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        SocialSchemaHelper::ensureReady();
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'avatar_border' => ['nullable', 'string', 'max:40'],
        ]);

        $code = isset($validated['avatar_border']) ? trim((string) $validated['avatar_border']) : '';
        if ($code === '' || $code === 'none') {
            $code = null;
        }

        if ($code !== null && ! AvatarBorderService::isUnlockedForUser($user, $code)) {
            return response()->json([
                'success' => false,
                'message' => $isEn
                    ? 'This border is still locked. Complete its challenge first!'
                    : 'Bingkai ini masih terkunci. Selesaikan tantangannya terlebih dahulu!',
            ], 403);
        }

        $user->update(['avatar_border' => $code]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => $code
                ? ($isEn ? 'Profile border equipped!' : 'Bingkai profil berhasil dipasang!')
                : ($isEn ? 'Profile border removed.' : 'Bingkai profil dilepas.'),
            'avatar_border' => $user->avatar_border,
            'avatar_border_url' => $user->avatar_border_url,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }
}

