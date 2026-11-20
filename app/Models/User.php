<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const BOT_DIGIKA_EMAIL = 'bot.digika@digisafe.id';

    // tabel users hanya memiliki kolom created_at tanpa updated_at
    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'specialty_role',
        'nama_lengkap',
        'bio',
        'avatar',
        'banner',
        'avatar_border',
        'badge_json',
        'created_at',
        'oauth_provider',
        'oauth_uid',
        'locale',
        'theme',
        'privacy_settings',
    ];

    protected $hidden = [
        'password',
        'reset_token',
        'remember_token',
        'oauth_uid',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'privacy_settings' => 'array',
        ];
    }

    /**
     * Pastikan kolom preferensi bahasa, tema layar, dan privasi tersedia di tabel users.
     */
    public static function ensurePreferenceColumns(): void
    {
        $cacheKey = 'digisafe_user_pref_cols_ready_v2';
        if (! app()->runningUnitTests() && Cache::get($cacheKey) === true) {
            return;
        }

        try {
            if (! Schema::hasTable('users')) {
                return;
            }
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'locale')) {
                    $table->string('locale', 10)->default('id');
                }
                if (! Schema::hasColumn('users', 'theme')) {
                    $table->string('theme', 10)->default('light');
                }
                if (! Schema::hasColumn('users', 'privacy_settings')) {
                    $table->text('privacy_settings')->nullable();
                }
                if (! Schema::hasColumn('users', 'specialty_role')) {
                    $table->string('specialty_role', 80)->nullable();
                }
                if (! Schema::hasColumn('users', 'oauth_provider')) {
                    $table->string('oauth_provider', 30)->nullable();
                }
                if (! Schema::hasColumn('users', 'oauth_uid')) {
                    $table->string('oauth_uid', 120)->nullable();
                }
                if (! Schema::hasColumn('users', 'avatar_border')) {
                    $table->string('avatar_border', 40)->nullable();
                }
            });

            if (! app()->runningUnitTests()) {
                Cache::put($cacheKey, true, now()->addHours(6));
            }
        } catch (\Throwable $e) {
            // Abaikan bila DB belum siap
        }
    }

    public function getAvatarBorderUrlAttribute(): ?string
    {
        return \App\Services\AvatarBorderService::imageUrl($this->avatar_border ?? null);
    }

    public function getAvatarBorderNameAttribute(): ?string
    {
        return \App\Services\AvatarBorderService::label($this->avatar_border ?? null);
    }

    public function getPrivacyConfigAttribute(): array
    {
        $raw = is_array($this->privacy_settings) ? $this->privacy_settings : [];

        return [
            'is_private_profile' => (bool) ($raw['is_private_profile'] ?? false),
            'hide_location' => (bool) ($raw['hide_location'] ?? false),
            'hide_email' => (bool) ($raw['hide_email'] ?? false),
            'hide_minigame_stats' => (bool) ($raw['hide_minigame_stats'] ?? false),
            'hide_badges' => (bool) ($raw['hide_badges'] ?? false),
            'hide_activity' => (bool) ($raw['hide_activity'] ?? false),
        ];
    }

    // nonaktifkan remember_token karena kolom tidak ada di skema tabel users
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner' || strtolower((string) $this->email) === 'owner@digisafe.id';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }

    public function isBot(): bool
    {
        return strtolower((string) $this->email) === self::BOT_DIGIKA_EMAIL
            || strtolower((string) $this->username) === 'bot_digika';
    }

    public function scopeHumans($query)
    {
        return $query->where('email', '!=', self::BOT_DIGIKA_EMAIL)
            ->where('username', '!=', 'bot_digika');
    }

    /**
     * Pastikan akun sistem Bot Digika tersedia untuk mengirim notifikasi evaluasi otomatis.
     */
    public static function ensureBotDigika(): self
    {
        $bot = static::query()
            ->where('email', self::BOT_DIGIKA_EMAIL)
            ->orWhere('username', 'bot_digika')
            ->first();

        if ($bot) {
            if ($bot->avatar !== 'images/bot-digika.jpg') {
                $bot->update(['avatar' => 'images/bot-digika.jpg']);
            }

            return $bot;
        }

        return static::create([
            'username' => 'bot_digika',
            'email' => self::BOT_DIGIKA_EMAIL,
            'password' => Hash::make(Str::random(40)),
            'role' => 'user',
            'nama_lengkap' => 'Bot Digika',
            'specialty_role' => 'Sistem Otomatis',
            'avatar' => 'images/bot-digika.jpg',
            'bio' => 'Asisten sistem otomatis Digi-Safe Purwakarta yang menyampaikan hasil evaluasi usulan tema edukasi.',
        ]);
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->isBot()) {
            return app()->getLocale() === 'en' ? 'Digi-Safe Automated System' : 'Sistem Otomatis Digi-Safe';
        }

        $isEn = app()->getLocale() === 'en';
        return match ($this->role) {
            'owner' => $isEn ? 'Platform Owner & Lead Developer' : 'Platform Owner & Lead Developer',
            'admin' => $isEn ? 'Cybersecurity Administrator' : 'Administrator Keamanan Siber',
            default => $isEn ? 'Digi-Safe Community Member' : 'Anggota Komunitas Digi-Safe',
        };
    }

    public function getDisplayRoleAttribute(): string
    {
        $custom = trim((string) ($this->specialty_role ?? ''));

        return $custom !== '' ? $custom : $this->role_label;
    }

    public static function availableSpecialtyRoles(): array
    {
        return [
            'Cyber Guardian',
            'Digital Educator',
            'Phishing Hunter',
            'Tech Explorer',
        ];
    }

    public static function specialtyRoleOptions(bool $isEn = false): array
    {
        return [
            [
                'value' => 'Cyber Guardian',
                'label' => 'Cyber Guardian',
                'desc' => $isEn ? 'Digital safety & anti-scam protector' : 'Pelindung ruang digital & anti-penipuan',
                'icon' => 'shield',
            ],
            [
                'value' => 'Digital Educator',
                'label' => 'Digital Educator',
                'desc' => $isEn ? 'Student, teacher & literacy advocate' : 'Pelajar, guru & penggerak literasi',
                'icon' => 'book',
            ],
            [
                'value' => 'Phishing Hunter',
                'label' => 'Phishing Hunter',
                'desc' => $isEn ? 'Link inspector & threat analyst' : 'Detektif tautan & analis ancaman',
                'icon' => 'search',
            ],
            [
                'value' => 'Tech Explorer',
                'label' => 'Tech Explorer',
                'desc' => $isEn ? 'Developer, creator & IT enthusiast' : 'Developer, kreator & penggiat teknologi',
                'icon' => 'code',
            ],
        ];
    }

    public function getInisialAttribute(): string
    {
        $nama = trim($this->nama_lengkap ?: $this->username ?: 'U');
        return mb_strtoupper(mb_substr($nama, 0, 1));
    }

    // kelas warna pastel konsisten berdasarkan nama user untuk fallback inisial
    public function getPastelClassAttribute(): string
    {
        $palettes = [
            'bg-[#DBEAFE] text-[#1E40AF]',
            'bg-[#D1FAE5] text-[#065F46]',
            'bg-[#FEF3C7] text-[#92400E]',
            'bg-[#FCE7F3] text-[#9D174D]',
            'bg-[#E0F2FE] text-[#0369A1]',
            'bg-[#ECFCCB] text-[#365314]',
        ];

        $seed = abs(crc32((string) ($this->nama_lengkap ?: $this->username ?: 'U')));
        return $palettes[$seed % count($palettes)];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        $av = ltrim((string) $this->avatar, '/');

        if (str_starts_with($av, 'http://') || str_starts_with($av, 'https://')) {
            return $av;
        }

        if (file_exists(public_path($av))) {
            return asset($av);
        }

        if (file_exists(public_path('storage/' . $av))) {
            return asset('storage/' . $av);
        }

        if (file_exists(storage_path('app/public/' . $av))) {
            return asset('storage/' . $av);
        }

        return null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->banner)) {
            return null;
        }

        $bn = ltrim((string) $this->banner, '/');

        if (str_starts_with($bn, 'http://') || str_starts_with($bn, 'https://')) {
            return $bn;
        }

        if (file_exists(public_path($bn))) {
            return asset($bn);
        }

        if (file_exists(public_path('storage/' . $bn))) {
            return asset('storage/' . $bn);
        }

        if (file_exists(storage_path('app/public/' . $bn))) {
            return asset('storage/' . $bn);
        }

        return null;
    }

    /**
     * Daftar preset warna Banner Default (6 Solid + 6 Gradient).
     */
    public static function bannerPresets(bool $isEn = false): array
    {
        return [
            'solid' => [
                ['key' => 'solid-navy', 'label' => $isEn ? 'Cyber Navy' : 'Navy Siber', 'css' => '#0F172A'],
                ['key' => 'solid-royal', 'label' => $isEn ? 'Royal Blue' : 'Biru Royal', 'css' => '#1E63E9'],
                ['key' => 'solid-emerald', 'label' => $isEn ? 'Emerald Shield' : 'Hijau Zamrud', 'css' => '#059669'],
                ['key' => 'solid-teal', 'label' => $isEn ? 'Ocean Teal' : 'Toska Samudra', 'css' => '#0D9488'],
                ['key' => 'solid-amber', 'label' => $isEn ? 'Warm Amber' : 'Kuning Amber', 'css' => '#D97706'],
                ['key' => 'solid-crimson', 'label' => $isEn ? 'Crimson Red' : 'Merah Crimson', 'css' => '#BE123C'],
            ],
            'gradient' => [
                ['key' => 'grad-sunrise', 'label' => $isEn ? 'Horizon Sunrise' : 'Fajar Horizon', 'css' => 'linear-gradient(118deg, #F59E0B 0%, #FB923C 28%, #FDA4AF 58%, #67E8F9 84%, #38BDF8 100%)'],
                ['key' => 'grad-cyber', 'label' => $isEn ? 'Cyber Fortress' : 'Benteng Siber', 'css' => 'linear-gradient(125deg, #0F172A 0%, #1E3A8A 52%, #0284C7 100%)'],
                ['key' => 'grad-matrix', 'label' => $isEn ? 'Emerald Lime' : 'Zamrud Lime', 'css' => 'linear-gradient(125deg, #064E3B 0%, #059669 55%, #C8F34A 100%)'],
                ['key' => 'grad-sunset', 'label' => $isEn ? 'Solar Flare' : 'Jingga Senja', 'css' => 'linear-gradient(125deg, #9A3412 0%, #EA580C 50%, #FACC15 100%)'],
                ['key' => 'grad-arctic', 'label' => $isEn ? 'Arctic Sky' : 'Langit Arktik', 'css' => 'linear-gradient(125deg, #0369A1 0%, #0EA5E9 55%, #67E8F9 100%)'],
                ['key' => 'grad-sentinel', 'label' => $isEn ? 'Sentinel Gold' : 'Emas Sentinel', 'css' => 'linear-gradient(125deg, #1E293B 0%, #334155 55%, #F59E0B 100%)'],
            ],
        ];
    }

    public function getBannerPresetKeyAttribute(): string
    {
        $raw = is_array($this->privacy_settings) ? $this->privacy_settings : [];
        $key = trim((string) ($raw['banner_preset'] ?? 'grad-sunrise'));

        return $key !== '' ? $key : 'grad-sunrise';
    }

    public function getBannerStyleCssAttribute(): string
    {
        $key = $this->banner_preset_key;

        if (preg_match('/^solid-custom:(#[0-9a-fA-F]{6})$/', $key, $m)) {
            return $m[1];
        }

        $presets = self::bannerPresets();
        foreach (array_merge($presets['solid'], $presets['gradient']) as $item) {
            if ($item['key'] === $key) {
                return $item['css'];
            }
        }

        return 'linear-gradient(118deg, #F59E0B 0%, #FB923C 28%, #FDA4AF 58%, #67E8F9 84%, #38BDF8 100%)';
    }

    public function forums(): HasMany
    {
        return $this->hasMany(Forum::class, 'user_id');
    }

    public function jawabanForums(): HasMany
    {
        return $this->hasMany(JawabanForum::class, 'user_id');
    }

    public function skorGames(): HasMany
    {
        return $this->hasMany(SkorGame::class, 'user_id');
    }

    public function testimonis(): HasMany
    {
        return $this->hasMany(Testimoni::class, 'user_id');
    }

    // lencana hanya dari New Player (< 1 bulan) dan achievement kemenangan minigame
    public function getBadgesListAttribute(): array
    {
        $badges = [];

        // lencana New Player otomatis diberikan dan hilang otomatis setelah 1 bulan
        $isUnderOneMonth = ! $this->created_at || $this->created_at->gt(now()->subMonth());
        if ($isUnderOneMonth) {
            $badges[] = 'New Player';
        }

        // ambil achievement dari badge_json
        if (! empty($this->badge_json)) {
            $decoded = json_decode((string) $this->badge_json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '' && ! in_array($item, $badges, true)) {
                        $badges[] = $item;
                    }
                }
            }
        }

        // ambil achievement dari tabel relasi user_badges
        $dbBadges = DB::table('user_badges')
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->where('user_badges.user_id', $this->id)
            ->pluck('badges.nama_badge')
            ->unique()
            ->all();

        foreach ($dbBadges as $b) {
            if (! in_array($b, $badges, true)) {
                $badges[] = $b;
            }
        }

        return $badges;
    }

    // simpan lencana achievement baru ke akun user
    public function awardBadges(array $candidates): array
    {
        $current = [];
        if (! empty($this->badge_json)) {
            $decoded = json_decode((string) $this->badge_json, true);
            if (is_array($decoded)) {
                $current = $decoded;
            }
        }

        $existingAll = $this->badges_list;
        $newlyUnlocked = [];

        foreach ($candidates as $badgeName) {
            if (! in_array($badgeName, $existingAll, true) && ! in_array($badgeName, $current, true)) {
                $current[] = $badgeName;
                $newlyUnlocked[] = $badgeName;

                // catat juga ke tabel user_badges jika ada di master badges
                $badgeRow = DB::table('badges')->where('nama_badge', $badgeName)->first();
                if ($badgeRow) {
                    DB::table('user_badges')->insert([
                        'user_id' => $this->id,
                        'badge_id' => $badgeRow->id,
                        'diperoleh_pada' => now(),
                    ]);
                }
            }
        }

        if (! empty($newlyUnlocked)) {
            $this->update([
                'badge_json' => json_encode(array_values($current)),
            ]);
        }

        return $newlyUnlocked;
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function isFollowing(int $targetUserId): bool
    {
        if ((int) $this->id === $targetUserId) {
            return false;
        }

        return UserFollow::where('follower_id', $this->id)
            ->where('following_id', $targetUserId)
            ->exists();
    }
}
