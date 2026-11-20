<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use App\Services\AccountOtpService;
use App\Services\UploadValidationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountSecurityController extends Controller
{
    /**
     * Halaman Reset Password via OTP Gmail.
     */
    public function showForgotPassword(): View
    {
        $prefillEmail = old('email', Auth::check() ? Auth::user()->email : session('otp_reset_email', ''));

        return view('auth.forgot-password-otp', [
            'prefillEmail' => $prefillEmail,
            'otpSentInitial' => (bool) session('otp_reset_sent', false),
        ]);
    }

    /**
     * Kirim kode OTP 6 digit ke Gmail / email terdaftar untuk reset password.
     */
    public function sendPasswordResetOtp(Request $request): JsonResponse|RedirectResponse
    {
        $requestedLocale = $request->input('locale');
        if (in_array($requestedLocale, ['id', 'en'], true)) {
            app()->setLocale($requestedLocale);
            session(['locale' => $requestedLocale]);
        }

        $locale = app()->getLocale();
        $isEn = $locale === 'en';

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:100'],
            'locale' => ['nullable', 'string', 'in:id,en'],
        ], [
            'email.required' => $isEn ? 'Email address is required.' : 'Alamat email wajib diisi.',
            'email.email' => $isEn ? 'Please enter a valid email address.' : 'Format alamat email tidak valid.',
        ]);

        $email = Str::lower(trim($validated['email']));

        // Jika user sedang login, hanya boleh mereset akun Gmail miliknya sendiri
        if (Auth::check()) {
            $ownEmail = Str::lower(trim((string) Auth::user()->email));
            if ($email !== $ownEmail) {
                $msg = $isEn
                    ? 'You can only reset the password for your own registered Gmail account.'
                    : 'Anda hanya dapat mereset password untuk akun Gmail milik Anda sendiri yang sedang login.';

                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => $msg], 403);
                }

                return back()->withErrors(['email' => $msg])->withInput();
            }
        }

        if (! str_ends_with($email, '@gmail.com')) {
            $msg = $isEn
                ? 'Password reset via OTP is only available for registered @gmail.com accounts.'
                : 'Reset password via OTP hanya dapat dilakukan menggunakan alamat @gmail.com yang terdaftar.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['email' => $msg])->withInput();
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || $user->isBot()) {
            $msg = $isEn
                ? 'No Digi-Safe account is registered with that Gmail address.'
                : 'Alamat Gmail tersebut tidak terdaftar pada akun Digi-Safe Purwakarta.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 404);
            }

            return back()->withErrors(['email' => $msg])->withInput();
        }

        $result = AccountOtpService::issueAndSend($user, AccountOtpService::PURPOSE_PASSWORD_RESET, $locale);

        if ($result['ok']) {
            session(['otp_reset_email' => $email, 'otp_reset_sent' => true]);
        }

        if ($request->expectsJson()) {
            return response()->json($result, $result['status']);
        }

        if (! $result['ok']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        return back()
            ->with('status', $result['message'])
            ->with('otp_reset_email', $email)
            ->with('otp_reset_sent', true);
    }

    /**
     * Verifikasi OTP dan perbarui password akun.
     */
    public function resetPasswordWithOtp(Request $request): JsonResponse|RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:100'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ], [
            'email.required' => $isEn ? 'Email address is required.' : 'Alamat email wajib diisi.',
            'otp.required' => $isEn ? '6-digit OTP code is required.' : 'Kode OTP 6 digit wajib diisi.',
            'otp.digits' => $isEn ? 'OTP code must be exactly 6 digits.' : 'Kode OTP harus berupa 6 digit angka.',
            'password.required' => $isEn ? 'New password is required.' : 'Password baru wajib diisi.',
            'password.min' => $isEn ? 'New password must be at least 8 characters.' : 'Password baru minimal 8 karakter.',
            'password.max' => $isEn ? 'New password must not exceed 128 characters.' : 'Password baru maksimal 128 karakter.',
            'password.confirmed' => $isEn ? 'Password confirmation does not match.' : 'Konfirmasi password baru tidak cocok.',
        ]);

        $email = Str::lower(trim($validated['email']));

        if (Auth::check()) {
            $ownEmail = Str::lower(trim((string) Auth::user()->email));
            if ($email !== $ownEmail) {
                $msg = $isEn
                    ? 'You can only reset the password for your own registered Gmail account.'
                    : 'Anda hanya dapat mereset password untuk akun Gmail milik Anda sendiri.';

                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => $msg], 403);
                }

                return back()->withErrors(['email' => $msg])->withInput($request->except(['password', 'password_confirmation']));
            }
        }

        if (! str_ends_with($email, '@gmail.com')) {
            $msg = $isEn
                ? 'Password reset via OTP is only available for registered @gmail.com accounts.'
                : 'Reset password via OTP hanya berlaku untuk akun @gmail.com yang terdaftar.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['email' => $msg])->withInput($request->except(['password', 'password_confirmation']));
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || $user->isBot()) {
            $msg = $isEn
                ? 'Account not found.'
                : 'Akun dengan email tersebut tidak ditemukan.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 404);
            }

            return back()->withErrors(['email' => $msg])->withInput($request->except(['password', 'password_confirmation']));
        }

        $verification = AccountOtpService::verifyAndConsume(
            $email,
            AccountOtpService::PURPOSE_PASSWORD_RESET,
            $validated['otp'],
        );

        if (! $verification['ok']) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $verification['message']], 422);
            }

            return back()
                ->withErrors(['otp' => $verification['message']])
                ->with('otp_reset_email', $email)
                ->with('otp_reset_sent', true)
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $existingPrivacy = is_array($user->privacy_settings) ? $user->privacy_settings : [];
        $existingPrivacy['has_local_password'] = true;

        $user->update([
            'password' => Hash::make($validated['password']),
            'privacy_settings' => $existingPrivacy,
        ]);

        if (Schema::hasTable('api_tokens')) {
            ApiToken::where('user_id', $user->id)->delete();
        }

        if (Auth::check() && Auth::id() === $user->id) {
            $request->session()->regenerate();
        }

        // Cabut seluruh sesi perangkat lain agar akun tetap aman setelah reset password
        if (Schema::hasTable('sessions')) {
            $sessionQuery = DB::table('sessions')->where('user_id', $user->id);
            if (Auth::check() && Auth::id() === $user->id) {
                $sessionQuery->where('id', '!=', $request->session()->getId());
            }
            $sessionQuery->delete();
        }

        session()->forget(['otp_reset_email', 'otp_reset_sent']);
        \App\Services\SecurityActivityService::record($user, \App\Services\SecurityActivityService::EVENT_PASSWORD_RESET, $request);

        $successMsg = $isEn
            ? 'Your password has been reset successfully. Please sign in with your new password.'
            : 'Password Anda berhasil direset! Silakan masuk menggunakan password baru Anda.';

        $redirectUrl = Auth::check() ? route('profile.edit') : route('login');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $successMsg,
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', $successMsg);
    }

    /**
     * Kirim kode OTP 6 digit ke Gmail / email user yang sedang login untuk hapus akun permanen.
     */
    public function sendDeleteAccountOtp(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $requestedLocale = $request->input('locale');
        if (in_array($requestedLocale, ['id', 'en'], true)) {
            app()->setLocale($requestedLocale);
            session(['locale' => $requestedLocale]);
        }

        $locale = app()->getLocale();
        $isEn = $locale === 'en';

        if ($user->isOwner() || $user->isBot()) {
            $msg = $isEn
                ? 'Platform Owner account is protected and cannot be deleted directly.'
                : 'Akun Platform Owner dilindungi sistem dan tidak dapat dihapus langsung.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 403);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        // Wajib verifikasi password akun terlebih dahulu sebelum OTP hapus akun boleh dikirim
        $pwdThrottleKey = 'delete-account-pwd:' . $user->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($pwdThrottleKey, 5)) {
            $wait = RateLimiter::availableIn($pwdThrottleKey);
            $msg = $isEn
                ? "Too many incorrect password attempts. Please try again in {$wait} seconds."
                : "Terlalu banyak percobaan password yang salah. Silakan coba lagi dalam {$wait} detik.";

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg, 'retry_after' => $wait], 429);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:128'],
            'locale' => ['nullable', 'string', 'in:id,en'],
        ], [
            'current_password.required' => $isEn
                ? 'Please enter your account password first before requesting a deletion OTP.'
                : 'Masukkan password akun Anda terlebih dahulu sebelum meminta kode OTP hapus akun.',
        ]);

        if (! Hash::check($validated['current_password'], (string) $user->password)) {
            RateLimiter::hit($pwdThrottleKey, 300);
            $msg = $isEn
                ? 'Account password is incorrect. Please verify your password before requesting the OTP code.'
                : 'Password akun Anda tidak sesuai. Pastikan password benar sebelum mengirim kode OTP.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        RateLimiter::clear($pwdThrottleKey);

        $result = AccountOtpService::issueAndSend($user, AccountOtpService::PURPOSE_DELETE_ACCOUNT, $locale);

        if ($result['ok']) {
            session(['delete_account_pwd_verified_at' => now()->timestamp]);
            \App\Services\SecurityActivityService::record($user, \App\Services\SecurityActivityService::EVENT_DELETE_OTP_SENT, $request);
        }

        if ($request->expectsJson()) {
            return response()->json($result, $result['status']);
        }

        if (! $result['ok']) {
            return back()->withErrors(['delete_account' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Verifikasi password + OTP dan hapus akun pengguna beserta seluruh data terkait secara permanen.
     */
    public function destroyAccount(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isEn = app()->getLocale() === 'en';

        if ($user->isOwner() || $user->isBot()) {
            $msg = $isEn
                ? 'Platform Owner account cannot be deleted.'
                : 'Akun Platform Owner tidak dapat dihapus.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 403);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        $pwdThrottleKey = 'delete-account-pwd:' . $user->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($pwdThrottleKey, 5)) {
            $wait = RateLimiter::availableIn($pwdThrottleKey);
            $msg = $isEn
                ? "Too many incorrect password attempts. Please try again in {$wait} seconds."
                : "Terlalu banyak percobaan password yang salah. Silakan coba lagi dalam {$wait} detik.";

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 429);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:128'],
            'otp' => ['required', 'digits:6'],
            'confirm_delete' => ['accepted'],
        ], [
            'current_password.required' => $isEn
                ? 'Your account password is required to confirm account deletion.'
                : 'Password akun wajib diisi untuk mengonfirmasi penghapusan akun.',
            'otp.required' => $isEn ? 'Please enter the 6-digit OTP sent to your email.' : 'Masukkan kode OTP 6 digit yang dikirim ke Gmail Anda.',
            'otp.digits' => $isEn ? 'OTP code must be 6 digits.' : 'Kode OTP harus berupa 6 digit angka.',
            'confirm_delete.accepted' => $isEn
                ? 'Please confirm that you understand account deletion is permanent.'
                : 'Centang konfirmasi bahwa penghapusan akun bersifat permanen.',
        ]);

        if (! Hash::check($validated['current_password'], (string) $user->password)) {
            RateLimiter::hit($pwdThrottleKey, 300);
            $msg = $isEn
                ? 'Account password is incorrect.'
                : 'Password akun tidak sesuai.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['delete_account' => $msg]);
        }

        RateLimiter::clear($pwdThrottleKey);

        $verification = AccountOtpService::verifyAndConsume(
            (string) $user->email,
            AccountOtpService::PURPOSE_DELETE_ACCOUNT,
            $validated['otp'],
        );

        if (! $verification['ok']) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $verification['message']], 422);
            }

            return back()->withErrors(['delete_account' => $verification['message']]);
        }

        $userId = (int) $user->id;
        $userEmail = Str::lower(trim((string) $user->email));
        $oldAvatar = $user->avatar;
        $oldBanner = $user->banner;

        DB::transaction(function () use ($user, $userId, $userEmail): void {
            if (Schema::hasTable('account_otps')) {
                DB::table('account_otps')->where('email', $userEmail)->delete();
            }
            if (Schema::hasTable('api_tokens')) {
                DB::table('api_tokens')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('chat_messages')) {
                DB::table('chat_messages')
                    ->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId)
                    ->delete();
            }
            if (Schema::hasTable('friendships')) {
                DB::table('friendships')
                    ->where('requester_id', $userId)
                    ->orWhere('addressee_id', $userId)
                    ->delete();
            }
            if (Schema::hasTable('user_follows')) {
                DB::table('user_follows')
                    ->where('follower_id', $userId)
                    ->orWhere('following_id', $userId)
                    ->delete();
            }
            if (Schema::hasTable('edukasi_requests')) {
                DB::table('edukasi_requests')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('jawaban_forum_votes')) {
                DB::table('jawaban_forum_votes')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('jawaban_forum')) {
                if (Schema::hasTable('forum')) {
                    $forumIds = DB::table('forum')->where('user_id', $userId)->pluck('id');
                    if ($forumIds->isNotEmpty()) {
                        if (Schema::hasTable('jawaban_forum_votes')) {
                            $replyIds = DB::table('jawaban_forum')->whereIn('forum_id', $forumIds)->pluck('id');
                            if ($replyIds->isNotEmpty()) {
                                DB::table('jawaban_forum_votes')->whereIn('jawaban_id', $replyIds)->delete();
                            }
                        }
                        DB::table('jawaban_forum')->whereIn('forum_id', $forumIds)->delete();
                    }
                }
                DB::table('jawaban_forum')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('forum')) {
                DB::table('forum')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('skor_game')) {
                DB::table('skor_game')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('testimoni')) {
                DB::table('testimoni')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('user_badges')) {
                DB::table('user_badges')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('progres_baca')) {
                DB::table('progres_baca')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('log_aktivitas')) {
                DB::table('log_aktivitas')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $userId)->delete();
            }

            $user->delete();
        });

        UploadValidationHelper::deleteOldAsset($oldAvatar, 'avatars');
        UploadValidationHelper::deleteOldAsset($oldBanner, 'banners');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $successMsg = $isEn
            ? 'Your account and all associated data have been permanently deleted.'
            : 'Akun Anda beserta seluruh data terkait telah dihapus secara permanen.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $successMsg,
                'redirect' => route('beranda'),
            ]);
        }

        return redirect()->route('beranda')->with('success', $successMsg);
    }
}
