<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use App\Rules\ValidRealEmail;
use App\Services\SocialSchemaHelper;
use App\Services\UploadValidationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const RESERVED_USERNAMES = [
        'admin',
        'administrator',
        'owner',
        'root',
        'system',
        'support',
        'moderator',
        'staff',
        'digisafe',
        'digisafepurwakarta',
        'bot_digika',
        'official',
        'security',
        'helpdesk',
    ];

    public function showLogin(Request $request): View
    {
        $isEn = app()->getLocale() === 'en';
        $intended = (string) $request->session()->get('url.intended', '');
        if (str_contains($intended, 'minigame') && ! $request->session()->has('warning')) {
            $request->session()->flash('warning', $isEn
                ? 'Minigames are only accessible to registered users. Please sign in or create an account first.'
                : 'Minigame hanya dapat diakses oleh pengguna terdaftar. Silakan masuk atau daftar akun baru terlebih dahulu.');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        // validasi form login (tolak format & domain disposable/asal-asalan)
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:100', new ValidRealEmail(checkDns: false)],
            'password' => ['required', 'string', 'max:128'],
        ], [
            'email.required' => $isEn ? 'Email address is required.' : 'Alamat email wajib diisi.',
            'password.required' => $isEn ? 'Password is required.' : 'Password wajib diisi.',
            'password.max' => $isEn ? 'Password must not exceed 128 characters.' : 'Password maksimal 128 karakter.',
        ]);

        $email = Str::lower(trim($credentials['email']));
        $credentials['email'] = $email;

        // Kunci brute-force per kombinasi akun + IP (maksimal 5 percobaan gagal per menit)
        $throttleKey = '`login`-web:' . sha1($email . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors([
                    'email' => $isEn
                        ? "Too many failed sign-in attempts. Please try again in {$seconds} seconds."
                        : "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
                ])
                ->onlyInput('email');
        }

        $targetUser = User::query()->where('email', $email)->first();
        if ($targetUser && $targetUser->isBot()) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['email' => $isEn ? 'Invalid email or password.' : 'Email atau password salah.'])
                ->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            \App\Services\SecurityActivityService::record(Auth::user(), \App\Services\SecurityActivityService::EVENT_LOGIN, $request);
            $defaultTarget = Auth::user()->hasAdminAccess()
                ? route('admin.dashboard')
                : route('beranda');

            return redirect()->intended($defaultTarget);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withErrors(['email' => $isEn ? 'Invalid email or password.' : 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        // Normalisasi username & email sebelum validasi agar tidak ada duplikat huruf besar/kecil
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username', ''))),
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        // validasi form registrasi (wajib email aktif & valid dengan cek MX domain + cegah username sistem)
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:2', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::notIn(self::RESERVED_USERNAMES),
                'unique:users,username',
            ],
            'email' => ['required', 'string', 'max:100', new ValidRealEmail(checkDns: true), 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ], [
            'nama_lengkap.required' => $isEn ? 'Display name / full name is required.' : 'Nickname / Nama lengkap wajib diisi.',
            'nama_lengkap.min' => $isEn ? 'Display name must be at least 2 characters.' : 'Nickname / Nama lengkap minimal 2 karakter.',
            'username.required' => $isEn ? 'Username is required.' : 'Username wajib diisi.',
            'username.min' => $isEn ? 'Username must be at least 3 characters.' : 'Username minimal 3 karakter.',
            'username.unique' => $isEn ? 'This username is already taken.' : 'Username sudah digunakan.',
            'username.not_in' => $isEn ? 'This username is reserved by the system. Please choose another.' : 'Username ini dilindungi sistem. Gunakan username lain.',
            'username.alpha_dash' => $isEn ? 'Username may only contain letters, numbers, dashes, and underscores.' : 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'email.required' => $isEn ? 'Email address is required.' : 'Alamat email wajib diisi.',
            'email.unique' => $isEn ? 'This email is already registered.' : 'Email sudah terdaftar.',
            'password.required' => $isEn ? 'Password is required.' : 'Password wajib diisi.',
            'password.min' => $isEn ? 'Password must be at least 8 characters.' : 'Password minimal 8 karakter.',
            'password.max' => $isEn ? 'Password must not exceed 128 characters.' : 'Password maksimal 128 karakter.',
            'password.confirmed' => $isEn ? 'Password confirmation does not match.' : 'Konfirmasi password tidak cocok.',
        ]);

        // simpan user baru dengan role default user + preferensi bahasa/tema yang sedang aktif
        $user = User::create([
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'locale' => in_array(session('locale', 'id'), ['id', 'en'], true) ? session('locale', 'id') : 'id',
            'theme' => in_array(session('theme', 'light'), ['light', 'dark'], true) ? session('theme', 'light') : 'light',
            'privacy_settings' => ['has_local_password' => true],
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

        $redirectUri = trim((string) config('services.google.redirect')) ?: route('auth.google.callback');

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
            $sessionMatches = $expectedState !== '' && $incomingState !== '' && hash_equals($expectedState, $incomingState);

            if (! $sessionMatches) {
                return $failRedirect('Sesi login Google tidak valid atau kedaluwarsa. Silakan coba lagi.');
            }
        }

        $clientId = trim((string) config('services.google.client_id'));
        $clientSecret = trim((string) config('services.google.client_secret'));
        $redirectUri = trim((string) config('services.google.redirect')) ?: route('auth.google.callback');

        try {
            $tokenResponse = Http::asForm()
                ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => (string) $request->query('code'),
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ]);

            if (! $tokenResponse->successful() || ! $tokenResponse->json('access_token')) {
                return $failRedirect('Gagal memverifikasi token dari Google. Silakan coba login ulang.');
            }

            $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))
                ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(15)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (! $profileResponse->successful()) {
                return $failRedirect('Gagal mengambil profil akun Google Anda.');
            }
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback error', ['error' => $e->getMessage()]);

            return $failRedirect('Tidak dapat terhubung ke layanan Google saat ini. Silakan coba beberapa saat lagi.');
        }

        $googleUser = $profileResponse->json();
        $googleId = (string) ($googleUser['sub'] ?? '');
        $email = Str::lower(trim((string) ($googleUser['email'] ?? '')));
        $emailVerified = filter_var($googleUser['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $fullName = trim((string) ($googleUser['name'] ?? ''));
        $avatarUrl = trim((string) ($googleUser['picture'] ?? ''));

        if ($googleId === '' || $email === '' || ! $emailVerified) {
            return $failRedirect('Akun Google harus memiliki alamat email yang terverifikasi.');
        }

        $user = self::findOrCreateGoogleUser($googleId, $email, $fullName, $avatarUrl);

        if ($isMobile) {
            $token = ApiToken::issueFor($user, 'flutter_google');
            $isNew = ($user->wasRecentlyCreated || blank($user->specialty_role)) ? '1' : '0';

            return redirect()->away('digisafe://auth/callback?token=' . urlencode($token) . '&is_new=' . $isNew);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        \App\Services\SecurityActivityService::record($user, \App\Services\SecurityActivityService::EVENT_LOGIN_GOOGLE, $request);

        // Khusus login Google: arahkan ke halaman kustomisasi profil jika belum pernah menyelesaikan onboarding Google
        $privacySettings = is_array($user->privacy_settings) ? $user->privacy_settings : [];
        $hasCompletedGoogleOnboarding = (bool) ($privacySettings['google_onboarded'] ?? false);

        if ($user->wasRecentlyCreated || ! $hasCompletedGoogleOnboarding) {
            $request->session()->put('google_onboarding_pending', true);

            return redirect()->route('auth.google.customize');
        }

        $defaultTarget = $user->hasAdminAccess()
            ? route('admin.dashboard')
            : route('beranda');

        return redirect()->intended($defaultTarget);
    }

    /**
     * Halaman kustomisasi profil khusus pengguna yang masuk lewat Google OAuth (tanpa navbar).
     */
    public function showGoogleCustomize(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->oauth_provider !== 'google') {
            return redirect()->route('beranda');
        }

        $privacySettings = is_array($user->privacy_settings) ? $user->privacy_settings : [];
        if ((bool) ($privacySettings['google_onboarded'] ?? false) && ! $request->session()->get('google_onboarding_pending')) {
            return redirect()->route($user->hasAdminAccess() ? 'admin.dashboard' : 'beranda');
        }

        return view('auth.google-customize', [
            'user' => $user,
            'redirectTarget' => $user->hasAdminAccess() ? route('admin.dashboard') : route('beranda'),
        ]);
    }

    /**
     * Simpan kustomisasi profil awal khusus pengguna Google (nickname & username wajib, avatar & banner opsional).
     */
    public function storeGoogleCustomize(Request $request): JsonResponse|RedirectResponse
    {
        SocialSchemaHelper::ensureReady();
        $user = Auth::user();

        if (! $user || $user->oauth_provider !== 'google') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Halaman ini khusus untuk akun Google.'], 403);
            }

            return redirect()->route('beranda');
        }

        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username', ''))),
        ]);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:2', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::notIn(self::RESERVED_USERNAMES),
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'avatar' => UploadValidationHelper::imageRules(5120),
            'banner' => UploadValidationHelper::imageRules(6144),
        ], [
            'nama_lengkap.required' => 'Nickname / Nama tampilan wajib diisi.',
            'nama_lengkap.min' => 'Nickname minimal 2 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.unique' => 'Username ini sudah dipakai pengguna lain.',
            'username.not_in' => 'Username ini dilindungi sistem. Gunakan username lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
        ]);

        $privacy = is_array($user->privacy_settings) ? $user->privacy_settings : [];
        $privacy['google_onboarded'] = true;

        $data = [
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'username' => Str::lower(trim($validated['username'])),
            'privacy_settings' => $privacy,
        ];

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
        $request->session()->forget('google_onboarding_pending');

        $redirectTarget = $user->hasAdminAccess() ? route('admin.dashboard') : route('beranda');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'redirect' => $redirectTarget,
                'message' => 'Profil berhasil dikustomisasi!',
            ]);
        }

        return redirect()->to($redirectTarget)->with('success', 'Profil Google Anda berhasil dikustomisasi! Selamat datang di Digi-Safe Purwakarta.');
    }

    /**
     * Cari atau buat akun user berdasarkan kredensial Google (dipakai Web & Mobile API).
     */
    public static function findOrCreateGoogleUser(string $googleId, string $email, string $fullName, string $avatarUrl = ''): User
    {
        User::ensurePreferenceColumns();

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
