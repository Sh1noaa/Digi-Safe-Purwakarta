<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SecurityActivityService
{
    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGIN_GOOGLE = 'login_google';
    public const EVENT_LOGIN_API = 'login_api';
    public const EVENT_PASSWORD_RESET = 'password_reset';
    public const EVENT_PASSWORD_CHANGED = 'password_changed';
    public const EVENT_DELETE_OTP_SENT = 'delete_otp_sent';

    public static function ensureTable(): void
    {
        if (Schema::hasTable('security_activity_logs')) {
            return;
        }

        try {
            Schema::create('security_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('event', 48)->index();
                $table->string('ip_address', 64)->nullable();
                $table->string('device_label', 120)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        } catch (\Throwable) {
            // Abaikan jika DB belum siap
        }
    }

    public static function record(User|int $user, string $event, ?Request $request = null): void
    {
        self::ensureTable();
        $userId = $user instanceof User ? (int) $user->id : (int) $user;
        if ($userId <= 0) {
            return;
        }

        $ua = $request ? (string) $request->userAgent() : '';
        $ip = $request ? (string) $request->ip() : null;

        try {
            DB::table('security_activity_logs')->insert([
                'user_id' => $userId,
                'event' => Str::limit($event, 48, ''),
                'ip_address' => $ip ? Str::limit($ip, 64, '') : null,
                'device_label' => self::parseDeviceLabel($ua),
                'user_agent' => Str::limit($ua, 255, ''),
                'created_at' => now(),
            ]);

            // Pertahankan maks 30 log terakhir per user agar tabel tetap ramping
            $keepIds = DB::table('security_activity_logs')
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->limit(30)
                ->pluck('id');

            if ($keepIds->count() === 30) {
                DB::table('security_activity_logs')
                    ->where('user_id', $userId)
                    ->whereNotIn('id', $keepIds)
                    ->delete();
            }
        } catch (\Throwable) {
            // Jangan gagalkan alur auth bila pencatatan log terkendala
        }
    }

    /**
     * Ambil daftar riwayat aktivitas login & keamanan terbaru untuk ditampilkan di modal Pengaturan.
     *
     * @return array<int, array{event: string, label: string, tone: string, device: string, ip: string, time_wib: string, time_ago: string}>
     */
    public static function recentForUser(User|int $user, int $limit = 8, ?string $locale = null): array
    {
        self::ensureTable();
        $userId = $user instanceof User ? (int) $user->id : (int) $user;
        $isEn = ($locale ?: app()->getLocale()) === 'en';

        if ($userId <= 0 || ! Schema::hasTable('security_activity_logs')) {
            return [];
        }

        $rows = DB::table('security_activity_logs')
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $rows->map(function ($row) use ($isEn) {
            $ts = $row->created_at ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta') : now('Asia/Jakarta');

            [$label, $tone] = match ($row->event) {
                self::EVENT_LOGIN => [$isEn ? 'Signed in via Email & Password' : 'Login dengan Email & Password', 'sky'],
                self::EVENT_LOGIN_GOOGLE => [$isEn ? 'Signed in via Google Account' : 'Login dengan Akun Google', 'emerald'],
                self::EVENT_LOGIN_API => [$isEn ? 'Signed in via Mobile App (APK)' : 'Login dari Aplikasi Mobile (APK)', 'sky'],
                self::EVENT_PASSWORD_RESET => [$isEn ? 'Password reset via Gmail OTP' : 'Reset Password via OTP Gmail', 'amber'],
                self::EVENT_PASSWORD_CHANGED => [$isEn ? 'Password updated in Profile' : 'Password Diperbarui di Profil', 'amber'],
                self::EVENT_DELETE_OTP_SENT => [$isEn ? 'Requested Account Deletion OTP' : 'Meminta Kode OTP Hapus Akun', 'red'],
                default => [$isEn ? 'Account security event' : 'Aktivitas keamanan akun', 'slate'],
            };

            return [
                'event' => (string) $row->event,
                'label' => $label,
                'tone' => $tone,
                'device' => (string) ($row->device_label ?: ($isEn ? 'Web Browser' : 'Browser Web')),
                'ip' => (string) ($row->ip_address ?: '-'),
                'time_wib' => $ts->format('d M Y, H:i') . ' WIB',
                'time_ago' => $ts->diffForHumans(),
            ];
        })->all();
    }

    public static function parseDeviceLabel(string $ua): string
    {
        if ($ua === '') {
            return 'Web Browser';
        }

        $lower = strtolower($ua);
        if (str_contains($lower, 'dart/') || str_contains($lower, 'flutter')) {
            return 'Digi-Safe Mobile APK · Android';
        }

        $browser = match (true) {
            str_contains($lower, 'edg/') => 'Edge',
            str_contains($lower, 'opr/') || str_contains($lower, 'opera') => 'Opera',
            str_contains($lower, 'chrome') && ! str_contains($lower, 'edg') => 'Chrome',
            str_contains($lower, 'firefox') => 'Firefox',
            str_contains($lower, 'safari') && ! str_contains($lower, 'chrome') => 'Safari',
            default => 'Browser',
        };

        $os = match (true) {
            str_contains($lower, 'android') => 'Android',
            str_contains($lower, 'iphone') || str_contains($lower, 'ipad') => 'iOS',
            str_contains($lower, 'windows') => 'Windows',
            str_contains($lower, 'mac os') || str_contains($lower, 'macintosh') => 'macOS',
            str_contains($lower, 'linux') => 'Linux',
            default => 'Perangkat',
        };

        return "{$browser} · {$os}";
    }
}
