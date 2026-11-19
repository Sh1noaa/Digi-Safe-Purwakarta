<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Rules\ValidRealEmail;
use App\Services\ApiResourceFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'email' => ['required', 'string', 'max:100', new ValidRealEmail(checkDns: false)],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $emailLower = Str::lower(trim($validated['email']));
        $user = User::query()->where('email', $emailLower)->first();

        if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
            return response()->json([
                'success' => false,
                'message' => $isEn ? 'Invalid email or password.' : 'Email atau password salah.',
            ], 422);
        }

        $token = ApiToken::issueFor($user, (string) ($validated['device_name'] ?? 'flutter_apk'));

        return response()->json([
            'success' => true,
            'message' => $isEn ? 'Signed in successfully.' : 'Berhasil masuk ke akun Digi-Safe.',
            'token' => $token,
            'is_new_user' => false,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'nama_lengkap' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'max:100', new ValidRealEmail(checkDns: true), 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $emailLower = Str::lower(trim($validated['email']));
        $base = preg_replace('/[^a-zA-Z0-9_]/', '_', Str::before($emailLower, '@')) ?: 'warga';
        $base = trim(Str::lower(Str::limit($base, 36, '')), '_') ?: 'warga';
        $username = filled($validated['username'] ?? null) ? trim((string) $validated['username']) : $base;
        if (blank($validated['username'] ?? null)) {
            $candidate = $username;
            $suffix = 1;
            while (User::query()->where('username', $candidate)->exists()) {
                $candidate = Str::limit($base, 36, '') . '_' . $suffix;
                $suffix++;
            }
            $username = $candidate;
        }

        $displayName = filled($validated['nama_lengkap'] ?? null)
            ? trim((string) $validated['nama_lengkap'])
            : Str::title(str_replace('_', ' ', $base));

        $user = User::create([
            'nama_lengkap' => $displayName,
            'username' => $username,
            'email' => $emailLower,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = ApiToken::issueFor($user, (string) ($validated['device_name'] ?? 'flutter_apk'));

        return response()->json([
            'success' => true,
            'message' => $isEn ? 'Account created successfully!' : 'Akun baru berhasil dibuat!',
            'token' => $token,
            'is_new_user' => true,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ], 201);
    }

    public function googleTokenLogin(Request $request): JsonResponse
    {
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'id_token' => ['nullable', 'string'],
            'access_token' => ['nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $googleData = null;
        $expectedClientId = trim((string) config('services.google.client_id'));

        if (! empty($validated['id_token'])) {
            $resp = Http::when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(12)
                ->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $validated['id_token'],
                ]);
            if ($resp->successful()) {
                $candidate = $resp->json();
                $tokenAud = (string) ($candidate['aud'] ?? '');
                if ($expectedClientId === '' || hash_equals($expectedClientId, $tokenAud)) {
                    $googleData = $candidate;
                }
            }
        } elseif (! empty($validated['access_token'])) {
            $tokenInfoResp = Http::when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(12)
                ->get('https://oauth2.googleapis.com/tokeninfo', [
                    'access_token' => $validated['access_token'],
                ]);

            $audValid = $tokenInfoResp->successful() && (
                $expectedClientId === ''
                || hash_equals($expectedClientId, (string) ($tokenInfoResp->json('aud') ?? ''))
            );

            if ($audValid) {
                $resp = Http::withToken($validated['access_token'])
                    ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                    ->timeout(12)
                    ->get('https://www.googleapis.com/oauth2/v3/userinfo');
                if ($resp->successful()) {
                    $googleData = $resp->json();
                }
            }
        }

        $emailVerified = is_array($googleData)
            && filter_var($googleData['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! is_array($googleData) || empty($googleData['sub']) || empty($googleData['email']) || ! $emailVerified) {
            return response()->json([
                'success' => false,
                'message' => $isEn ? 'Invalid or unverified Google token.' : 'Token Google tidak valid atau email belum terverifikasi.',
            ], 422);
        }

        $user = AuthController::findOrCreateGoogleUser(
            (string) $googleData['sub'],
            Str::lower(trim((string) $googleData['email'])),
            trim((string) ($googleData['name'] ?? '')),
            trim((string) ($googleData['picture'] ?? ''))
        );

        $token = ApiToken::issueFor($user, (string) ($validated['device_name'] ?? 'flutter_google'));

        return response()->json([
            'success' => true,
            'message' => $isEn ? 'Signed in with Google.' : 'Berhasil masuk dengan akun Google.',
            'token' => $token,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => ApiResourceFormatter::formatUser($user, $user, true),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        ApiToken::revokeFromRequest($request);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'en' ? 'Signed out successfully.' : 'Berhasil keluar dari akun.',
        ]);
    }
}

