<?php

namespace App\Services;

use App\Mail\AccountOtpMail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountOtpService
{
    public const PURPOSE_PASSWORD_RESET = 'password_reset';
    public const PURPOSE_DELETE_ACCOUNT = 'delete_account';
    public const PURPOSE_REGISTER = 'register';

    public const EXPIRY_MINUTES = 10;
    public const RESEND_COOLDOWN_SECONDS = 20;
    public const MAX_ATTEMPTS = 5;
    public const MAX_SENDS_PER_HOUR = 8;

    public static function ensureTable(): void
    {
        if (Schema::hasTable('account_otps')) {
            return;
        }

        try {
            Schema::create('account_otps', function (Blueprint $table) {
                $table->id();
                $table->string('email', 120)->index();
                $table->string('purpose', 32)->index();
                $table->string('code_hash');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->timestamps();

                $table->index(['email', 'purpose', 'consumed_at']);
            });
        } catch (\Throwable $e) {
            Log::warning('Unable to auto-create account_otps table', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate a 6-digit OTP, store its hash, and deliver it to the user's email.
     *
     * @return array{ok: bool, status: int, message: string, retry_after?: int, cooldown?: int, expires_in?: int, masked_email?: string}
     */
    public static function issueAndSend(User $user, string $purpose, ?string $locale = null): array
    {
        $recipientName = trim((string) ($user->nama_lengkap ?: $user->username ?: 'Pengguna Digi-Safe'));

        return self::issueAndSendToEmail((string) $user->email, $recipientName, $purpose, $locale);
    }

    /**
     * Generate a 6-digit OTP, store its hash, and deliver it to any target email address
     * (dipakai untuk registrasi akun baru maupun aksi keamanan akun terdaftar).
     *
     * @return array{ok: bool, status: int, message: string, retry_after?: int, cooldown?: int, expires_in?: int, masked_email?: string}
     */
    public static function issueAndSendToEmail(string $rawEmail, string $recipientName, string $purpose, ?string $locale = null): array
    {
        self::ensureTable();

        $locale = $locale ?: app()->getLocale();
        $isEn = $locale === 'en';
        $email = Str::lower(trim($rawEmail));
        $safeRecipientName = trim($recipientName) !== ''
            ? trim($recipientName)
            : ($isEn ? 'Digi-Safe Citizen' : 'Calon Warga Digi-Safe');

        // Batasi total pengiriman per jam untuk mencegah penyalahgunaan / pengurasan kuota email
        $sentLastHour = DB::table('account_otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($sentLastHour >= self::MAX_SENDS_PER_HOUR) {
            return [
                'ok' => false,
                'status' => 429,
                'retry_after' => 300,
                'cooldown' => 300,
                'message' => $isEn
                    ? 'Hourly OTP request limit reached for this email. Please try again later.'
                    : 'Batas permintaan OTP per jam untuk email ini telah tercapai. Silakan coba beberapa saat lagi.',
            ];
        }

        $recent = DB::table('account_otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if ($recent && $recent->created_at) {
            $elapsed = Carbon::parse($recent->created_at)->diffInSeconds(now());
            if ($elapsed < self::RESEND_COOLDOWN_SECONDS) {
                $remaining = self::RESEND_COOLDOWN_SECONDS - $elapsed;

                return [
                    'ok' => false,
                    'status' => 429,
                    'retry_after' => $remaining,
                    'cooldown' => $remaining,
                    'message' => $isEn
                        ? "Please wait {$remaining} seconds before requesting another OTP code."
                        : "Mohon tunggu {$remaining} detik sebelum meminta kode OTP baru.",
                ];
            }
        }

        // Clean up any old expired OTPs for this email & purpose
        DB::table('account_otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '<=', now())
            ->update(['consumed_at' => now(), 'updated_at' => now()]);

        $plainOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();

        $otpId = DB::table('account_otps')->insertGetId([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($plainOtp),
            'attempts' => 0,
            'expires_at' => $now->copy()->addMinutes(self::EXPIRY_MINUTES),
            'consumed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        try {
            $mailable = new AccountOtpMail(
                otpCode: $plainOtp,
                purpose: $purpose,
                recipientName: $safeRecipientName,
                expiryMinutes: self::EXPIRY_MINUTES,
                localeCode: $locale,
            );

            self::deliverOtpEmail($email, $mailable);
        } catch (\Throwable $e) {
            DB::table('account_otps')->where('id', $otpId)->delete();
            Log::error('Failed sending Account OTP email', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 500,
                'message' => $isEn
                    ? 'Failed to send OTP email. Please verify the Gmail SMTP configuration or try again shortly.'
                    : 'Gagal mengirim email OTP. Pastikan konfigurasi Gmail SMTP sudah aktif atau coba beberapa saat lagi.',
            ];
        }

        $masked = self::maskEmail($email);

        return [
            'ok' => true,
            'status' => 200,
            'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            'expires_in' => self::EXPIRY_MINUTES * 60,
            'masked_email' => $masked,
            'message' => $isEn
                ? "A 6-digit OTP code has been sent to {$masked}. Please check your inbox or spam folder."
                : "Kode OTP 6 digit telah dikirim ke {$masked}. Silakan cek kotak masuk atau folder spam email Anda.",
        ];
    }

    /**
     * Verify a submitted 6-digit OTP and mark it consumed when valid.
     *
     * @return array{ok: bool, message: string}
     */
    public static function verifyAndConsume(string $email, string $purpose, string $submittedCode, ?string $locale = null): array
    {
        self::ensureTable();

        $locale = $locale ?: app()->getLocale();
        $isEn = $locale === 'en';
        $normalizedEmail = Str::lower(trim($email));
        $cleanCode = preg_replace('/\D/', '', trim($submittedCode)) ?: '';

        if (strlen($cleanCode) !== 6) {
            return [
                'ok' => false,
                'message' => $isEn
                    ? 'OTP code must consist of 6 digits.'
                    : 'Kode OTP harus terdiri dari 6 digit angka.',
            ];
        }

        // Cari semua OTP aktif yang belum kedaluwarsa & belum digunakan
        $activeOtps = DB::table('account_otps')
            ->where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->get();

        if ($activeOtps->isEmpty()) {
            $expired = DB::table('account_otps')
                ->where('email', $normalizedEmail)
                ->where('purpose', $purpose)
                ->where('expires_at', '<=', now())
                ->orderByDesc('id')
                ->first();

            if ($expired) {
                return [
                    'ok' => false,
                    'message' => $isEn
                        ? 'OTP code has expired. Please request a new OTP code.'
                        : 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode OTP baru.',
                ];
            }

            return [
                'ok' => false,
                'message' => $isEn
                    ? 'OTP code was not found or has already been used. Please request a new code.'
                    : 'Kode OTP tidak ditemukan atau sudah digunakan. Silakan minta kode baru.',
            ];
        }

        // Cek jika batas percobaan pada OTP aktif sudah habis
        $maxAttemptsReached = $activeOtps->contains(fn ($rec) => (int) $rec->attempts >= self::MAX_ATTEMPTS);
        if ($maxAttemptsReached) {
            DB::table('account_otps')
                ->where('email', $normalizedEmail)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update([
                    'consumed_at' => now(),
                    'updated_at' => now(),
                ]);

            return [
                'ok' => false,
                'message' => $isEn
                    ? 'Too many incorrect OTP attempts. Please request a new code.'
                    : 'Terlalu banyak percobaan OTP yang salah. Silakan minta kode baru.',
            ];
        }

        // Cek kecocokan kode dengan salah satu OTP aktif (terbaru maupun kode sebelum kirim ulang yang masih aktif)
        $matched = false;
        foreach ($activeOtps as $rec) {
            if (Hash::check($cleanCode, (string) $rec->code_hash)) {
                $matched = true;
                break;
            }
        }

        if ($matched) {
            // Berhasil! Konsumsi semua OTP untuk email & tujuan ini agar kode lama tidak bisa dipakai ulang
            DB::table('account_otps')
                ->where('email', $normalizedEmail)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update([
                    'consumed_at' => now(),
                    'updated_at' => now(),
                ]);

            return [
                'ok' => true,
                'message' => $isEn ? 'OTP verified.' : 'Kode OTP terverifikasi.',
            ];
        }

        // Salah kode: Tambahkan percobaan pada semua active records
        $newAttempts = (int) $activeOtps->first()->attempts + 1;
        $updates = [
            'attempts' => $newAttempts,
            'updated_at' => now(),
        ];
        if ($newAttempts >= self::MAX_ATTEMPTS) {
            $updates['consumed_at'] = now();
        }

        DB::table('account_otps')
            ->whereIn('id', $activeOtps->pluck('id'))
            ->update($updates);

        $remaining = max(0, self::MAX_ATTEMPTS - $newAttempts);

        return [
            'ok' => false,
            'message' => $remaining > 0
                ? ($isEn
                    ? "Invalid OTP code. {$remaining} attempt(s) remaining."
                    : "Kode OTP tidak sesuai. Sisa {$remaining} percobaan lagi.")
                : ($isEn
                    ? 'Invalid OTP code and maximum attempts reached. Please request a new OTP.'
                    : 'Kode OTP salah dan batas percobaan habis. Silakan minta kode OTP baru.'),
        ];
    }

    public static function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $len = strlen($local);

        if ($len <= 2) {
            $maskedLocal = substr($local, 0, 1) . '***';
        } else {
            $maskedLocal = substr($local, 0, 2) . str_repeat('*', min(5, $len - 2)) . substr($local, -1);
        }

        return $maskedLocal . '@' . $domain;
    }

    /**
     * Kirim email OTP via HTTPS Port 443 (Google Apps Script Webhook / Brevo API) jika dikonfigurasi,
     * agar tetap tembus pada jaringan LAN/Wi-Fi yang memblokir port SMTP 465/587.
     * Jika tidak dikonfigurasi, otomatis gunakan Laravel Mailer (SMTP/Log).
     */
    private static function deliverOtpEmail(string $toEmail, AccountOtpMail $mailable): void
    {
        $webhookUrl = trim((string) config('services.mail_relay.webhook_url'));
        $secret = (string) config('services.mail_relay.webhook_secret', '');
        $brevoKey = trim((string) config('services.mail_relay.brevo_api_key'));

        // Jika bukan di mode testing, pastikan webhook URL terbaca langsung dari .env
        // agar proses php artisan serve yang belum sempat di-restart tetap langsung mengirim via HTTPS
        if (! app()->environment('testing')) {
            if ($webhookUrl === '') {
                $webhookUrl = trim((string) env('MAIL_HTTPS_WEBHOOK_URL', ''));
            }
            if ($secret === '') {
                $secret = trim((string) env('MAIL_HTTPS_WEBHOOK_SECRET', ''));
            }
            if ($webhookUrl === '' && file_exists(base_path('.env'))) {
                $envLines = @file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($envLines as $line) {
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, 'MAIL_HTTPS_WEBHOOK_URL=')) {
                        $webhookUrl = trim(trim(substr($trimmed, strlen('MAIL_HTTPS_WEBHOOK_URL='))), " \t\n\r\0\x0B\"'");
                    } elseif (str_starts_with($trimmed, 'MAIL_HTTPS_WEBHOOK_SECRET=')) {
                        $secret = trim(trim(substr($trimmed, strlen('MAIL_HTTPS_WEBHOOK_SECRET='))), " \t\n\r\0\x0B\"'");
                    }
                }
            }
        }

        if ($webhookUrl !== '') {
            $subject = (string) $mailable->envelope()->subject;
            $html = $mailable->render();

            $res = Http::asJson()
                ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(15)
                ->post($webhookUrl, [
                    'secret' => $secret,
                    'to' => $toEmail,
                    'subject' => $subject,
                    'html' => $html,
                    'from_name' => (string) config('mail.from.name', 'Digi-Safe Purwakarta'),
                ]);

            if (! $res->successful()) {
                throw new \RuntimeException('HTTPS Mail Relay HTTP ' . $res->status());
            }

            $json = $res->json();
            if (is_array($json) && array_key_exists('ok', $json) && $json['ok'] === false) {
                throw new \RuntimeException((string) ($json['error'] ?? 'HTTPS Mail Relay rejected request.'));
            }

            return;
        }

        if ($brevoKey !== '') {
            $subject = (string) $mailable->envelope()->subject;
            $html = $mailable->render();

            $res = Http::withHeaders([
                'api-key' => $brevoKey,
                'Accept' => 'application/json',
            ])
                ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(15)
                ->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => [
                        'name' => (string) config('mail.from.name', 'Digi-Safe Purwakarta'),
                        'email' => (string) config('mail.from.address', 'digisafepurwakarta@gmail.com'),
                    ],
                    'to' => [
                        ['email' => $toEmail],
                    ],
                    'subject' => $subject,
                    'htmlContent' => $html,
                ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Brevo HTTPS API HTTP ' . $res->status() . ': ' . $res->body());
            }

            return;
        }

        Mail::to($toEmail)->send($mailable);
    }
}
