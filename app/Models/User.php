<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            });
        } catch (\Throwable $e) {
            // Abaikan bila DB belum siap
        }
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

    public function getRoleLabelAttribute(): string
    {
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
            'Software Engineer',
            'Web & Mobile Developer',
            'Pelajar / Mahasiswa',
            'UI/UX Designer',
            'Cybersecurity Enthusiast',
            'Konten Kreator',
            'Gamer',
            'Guru / Pendidik',
            'Wirausaha / UMKM',
            'Hobi Teknologi',
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
}
