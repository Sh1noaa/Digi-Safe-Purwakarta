<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'last_used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Pastikan tabel api_tokens tersedia tanpa wajib SSH migrate di shared hosting.
     */
    public static function ensureTable(): void
    {
        try {
            if (! Schema::hasTable('api_tokens')) {
                Schema::create('api_tokens', function (Blueprint $table) {
                    $table->id();
                    $table->integer('user_id')->index();
                    $table->string('name', 80)->default('flutter_apk');
                    $table->string('token', 64)->unique();
                    $table->timestamp('last_used_at')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Abaikan jika koneksi DB belum siap
        }
    }

    /**
     * Terbitkan Bearer token baru untuk pengguna dan kembalikan plain token.
     */
    public static function issueFor(User $user, string $deviceName = 'flutter_apk'): string
    {
        self::ensureTable();

        $plain = Str::random(48);
        $record = static::create([
            'user_id' => $user->id,
            'name' => Str::limit($deviceName, 80, ''),
            'token' => hash('sha256', $plain),
            'last_used_at' => now(),
            'expires_at' => now()->addDays(90),
        ]);

        return $record->id . '|' . $plain;
    }

    /**
     * Resolusi user dan preferensi locale dari header Bearer / X-App-Locale pada request API.
     */
    public static function resolveRequestContext(Request $request): ?self
    {
        self::ensureTable();

        $headerLocale = strtolower((string) ($request->header('X-App-Locale') ?: $request->query('lang', '')));
        $locale = in_array($headerLocale, ['id', 'en'], true) ? $headerLocale : null;

        $bearer = $request->bearerToken();
        $tokenModel = null;

        if (! empty($bearer)) {
            $rawSecret = str_contains($bearer, '|') ? Str::after($bearer, '|') : $bearer;
            $hashed = hash('sha256', $rawSecret);

            $query = static::query()->where('token', $hashed);
            if (str_contains($bearer, '|')) {
                $tokenId = (int) Str::before($bearer, '|');
                if ($tokenId > 0) {
                    $query->where('id', $tokenId);
                }
            }

            $tokenModel = $query->with('user')->first();
            if ($tokenModel && $tokenModel->user && (! $tokenModel->expires_at || $tokenModel->expires_at->isFuture())) {
                Auth::setUser($tokenModel->user);
                $tokenModel->forceFill(['last_used_at' => now()])->saveQuietly();

                if ($locale === null && in_array($tokenModel->user->locale, ['id', 'en'], true)) {
                    $locale = $tokenModel->user->locale;
                }
            } else {
                $tokenModel = null;
            }
        }

        $finalLocale = $locale ?? 'id';
        App::setLocale($finalLocale);
        \Carbon\Carbon::setLocale($finalLocale);

        return $tokenModel;
    }

    /**
     * Hapus token saat logout dari aplikasi mobile.
     */
    public static function revokeFromRequest(Request $request): void
    {
        self::ensureTable();
        $bearer = $request->bearerToken();
        if (empty($bearer)) {
            return;
        }

        $rawSecret = str_contains($bearer, '|') ? Str::after($bearer, '|') : $bearer;
        static::query()->where('token', hash('sha256', $rawSecret))->delete();
    }
}

