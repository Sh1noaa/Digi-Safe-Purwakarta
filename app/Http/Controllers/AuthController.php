<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $intended = (string) $request->session()->get('url.intended', '');
        if (str_contains($intended, 'minigame') && ! $request->session()->has('warning')) {
            $request->session()->flash('warning', 'Minigame hanya dapat diakses oleh pengguna terdaftar. Silakan masuk atau daftar akun baru terlebih dahulu.');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // validasi form login
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $defaultTarget = Auth::user()->hasAdminAccess()
                ? route('admin.dashboard')
                : route('beranda');

            return redirect()->intended($defaultTarget);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah'])
            ->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // validasi form registrasi
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'nama_lengkap.required' => 'Nickname / Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // simpan user baru dengan role default user
        $user = User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('beranda'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }

    /**
     * Arahkan pengguna ke halaman login Google OAuth 2.0 (mendukung web & mode=mobile untuk APK).
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $isMobile = $request->query('mode') === 'mobile';
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            $msg = 'Login Google belum dikonfigurasi. Lengkapi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env.';

            return $isMobile
                ? redirect()->away('digisafe://auth/callback?error=' . urlencode($msg))
                : redirect()->route('login')->with('error', $msg);
        }

        if ($isMobile) {
            $state = 'mobile_' . Str::random(32);
            Cache::put('google_oauth_mobile_' . $state, true, now()->addMinutes(10));
        } else {
            $state = Str::random(40);
            $request->session()->put('google_oauth_state', $state);
        }

        $redirectUri = config('services.google.redirect') ?: route('auth.google.callback');

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Tangani callback dari Google OAuth 2.0, buat/tautkan akun user, lalu login (web / APK).
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $incomingState = (string) $request->query('state', '');
        $isMobile = str_starts_with($incomingState, 'mobile_');

        $failRedirect = fn (string $msg) => $isMobile
            ? redirect()->away('digisafe://auth/callback?error=' . urlencode($msg))
            : redirect()->route('login')->with('error', $msg);

        if ($request->filled('error') || ! $request->filled('code')) {
            return $failRedirect('Autentikasi Google dibatalkan atau tidak valid.');
        }

        if ($isMobile) {
            $validMobileState = (bool) Cache::pull('google_oauth_mobile_' . $incomingState, false);
            if (! $validMobileState) {
                return $failRedirect('Sesi login Google kedaluwarsa. Silakan coba lagi.');
            }
        } else {
            $expectedState = (string) $request->session()->pull('google_oauth_state', '');
            if ($expectedState === '' || ! hash_equals($expectedState, $incomingState)) {
                return $failRedirect('Sesi login Google kedaluwarsa. Silakan coba lagi.');
            }
        }

        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');
        $redirectUri = config('services.google.redirect') ?: route('auth.google.callback');

        $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'code' => (string) $request->query('code'),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful() || ! $tokenResponse->json('access_token')) {
            return $failRedirect('Gagal memverifikasi token dari Google. Pastikan Client ID, Secret, dan Redirect URI sudah sesuai.');
        }

        $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $profileResponse->successful()) {
            return $failRedirect('Gagal mengambil profil akun Google Anda.');
        }

        $googleUser = $profileResponse->json();
        $googleId = (string) ($googleUser['sub'] ?? '');
        $email = Str::lower(trim((string) ($googleUser['email'] ?? '')));
        $fullName = trim((string) ($googleUser['name'] ?? ''));
        $avatarUrl = trim((string) ($googleUser['picture'] ?? ''));

        if ($googleId === '' || $email === '') {
            return $failRedirect('Akun Google tidak menyertakan alamat email yang valid.');
        }

        $user = self::findOrCreateGoogleUser($googleId, $email, $fullName, $avatarUrl);

        if ($isMobile) {
            $token = ApiToken::issueFor($user, 'flutter_google');

            return redirect()->away('digisafe://auth/callback?token=' . urlencode($token));
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $defaultTarget = $user->hasAdminAccess()
            ? route('admin.dashboard')
            : route('beranda');

        return redirect()->intended($defaultTarget);
    }

    /**
     * Cari atau buat akun user berdasarkan kredensial Google (dipakai Web & Mobile API).
     */
    public static function findOrCreateGoogleUser(string $googleId, string $email, string $fullName, string $avatarUrl = ''): User
    {
        $user = User::query()
            ->where('oauth_provider', 'google')
            ->where('oauth_uid', $googleId)
            ->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();

            if ($user) {
                $user->update([
                    'oauth_provider' => 'google',
                    'oauth_uid' => $googleId,
                    'avatar' => $user->avatar ?: ($avatarUrl !== '' ? $avatarUrl : null),
                ]);
            } else {
                $username = self::generateUniqueUsername($email, $fullName);

                $user = User::create([
                    'nama_lengkap' => Str::limit($fullName !== '' ? $fullName : $username, 100, ''),
                    'username' => $username,
                    'email' => Str::limit($email, 100, ''),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                    'oauth_provider' => 'google',
                    'oauth_uid' => $googleId,
                    'avatar' => $avatarUrl !== '' ? $avatarUrl : null,
                ]);
            }
        } elseif (! $user->avatar && $avatarUrl !== '') {
            $user->update(['avatar' => $avatarUrl]);
        }

        return $user;
    }

    private static function generateUniqueUsername(string $email, string $fullName): string
    {
        $base = Str::before($email, '@');
        $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base) ?: '';
        $base = trim(Str::lower($base), '_');

        if ($base === '') {
            $base = preg_replace('/[^a-zA-Z0-9_]/', '_', Str::lower($fullName)) ?: 'warga_siber';
        }

        $base = Str::limit($base, 40, '');
        $candidate = $base;
        $counter = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = Str::limit($base, 40, '') . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
