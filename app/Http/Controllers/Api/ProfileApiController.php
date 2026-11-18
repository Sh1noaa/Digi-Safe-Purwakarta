<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResourceFormatter;
use App\Services\SocialSchemaHelper;
use App\Services\UploadValidationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'specialty_role' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:350'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => UploadValidationHelper::imageRules(3072),
            'banner' => UploadValidationHelper::imageRules(5120),
        ]);

        $data = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'specialty_role' => filled($validated['specialty_role'] ?? null) ? trim($validated['specialty_role']) : $user->specialty_role,
            'bio' => $validated['bio'] ?? $user->bio,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $targetDir = public_path('images/avatars');
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $file->move($targetDir, $filename);

            $storageDir = storage_path('app/public/avatars');
            if (! is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }
            @copy($targetDir . '/' . $filename, $storageDir . '/' . $filename);

            $data['avatar'] = 'images/avatars/' . $filename;
        }

        if ($request->hasFile('banner')) {
            $bannerFile = $request->file('banner');
            $bannerName = 'banner_' . $user->id . '_' . time() . '.' . $bannerFile->getClientOriginalExtension();

            $bannerDir = public_path('images/banners');
            if (! is_dir($bannerDir)) {
                mkdir($bannerDir, 0777, true);
            }
            $bannerFile->move($bannerDir, $bannerName);

            $storageBannerDir = storage_path('app/public/banners');
            if (! is_dir($storageBannerDir)) {
                mkdir($storageBannerDir, 0777, true);
            }
            @copy($bannerDir . '/' . $bannerName, $storageBannerDir . '/' . $bannerName);

            $data['banner'] = 'images/banners/' . $bannerName;
        }

        $user->update($data);
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
}

