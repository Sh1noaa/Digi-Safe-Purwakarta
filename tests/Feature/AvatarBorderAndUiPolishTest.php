<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\SkorGame;
use App\Models\Testimoni;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use App\Services\AvatarBorderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AvatarBorderAndUiPolishTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'chat_messages',
            'friendships',
            'user_follows',
            'user_badges',
            'badges',
            'jawaban_forum_votes',
            'jawaban_forum',
            'forum',
            'skor_game',
            'testimoni',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('nama_lengkap')->nullable();
            $table->string('specialty_role', 80)->nullable();
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->string('avatar_border', 40)->nullable();
            $table->text('bio')->nullable();
            $table->string('locale', 10)->default('id');
            $table->string('theme', 20)->default('light');
            $table->json('privacy_settings')->nullable();
            $table->json('badge_json')->nullable();
            $table->string('oauth_provider', 30)->nullable();
            $table->string('oauth_uid', 120)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('skor_game', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('game_type');
            $table->integer('skor')->default(0);
            $table->integer('skor_tertinggi')->default(0);
            $table->unsignedInteger('benar')->default(0);
            $table->unsignedInteger('salah')->default(0);
            $table->timestamp('played_at')->nullable();
        });

        Schema::create('forum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('judul');
            $table->string('kategori')->default('Umum');
            $table->text('pertanyaan');
            $table->string('status')->default('terbuka');
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forum_id');
            $table->unsignedBigInteger('user_id');
            $table->text('jawaban');
            $table->boolean('is_admin')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jawaban_id');
            $table->unsignedBigInteger('user_id');
            $table->string('vote_type', 10);
            $table->timestamps();
        });

        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('judul')->nullable();
            $table->text('cerita');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('mood')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status_approval')->default('approved');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('nama_badge');
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('earned_at')->nullable();
        });
    }

    public function test_password_checker_starts_empty_without_ganteng123_default(): void
    {
        $resp = $this->get('/minigame/password-checker');

        $resp->assertOk()
            ->assertDontSee('ganteng123')
            ->assertSee("password: ''", false);
    }

    public function test_leaderboard_renders_rich_real_data_sections_and_footer(): void
    {
        $player = User::create([
            'username' => 'juara_siber',
            'email' => 'juara@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Juara Siber',
            'avatar_border' => 'aqua_crest',
        ]);

        SkorGame::create([
            'user_id' => $player->id,
            'game_type' => 'phishing',
            'skor' => 95,
            'skor_tertinggi' => 95,
            'benar' => 9,
            'salah' => 1,
            'played_at' => now(),
        ]);

        $resp = $this->actingAs($player)->get('/leaderboard');
        $resp->assertOk()
            ->assertSee('Peringkat Komunitas & Rekor Arena')
            ->assertSee('Peringkat #1')
            ->assertDontSee('Belum masuk klasemen')
            ->assertSee('Rekor per Arena Minigame')
            ->assertSee('Koleksi 5 Border Avatar Prestasi')
            ->assertSee('Aktivitas Skor Terbaru')
            ->assertSee('aqua-crest.png')
            ->assertSee('<footer', false);
    }

    public function test_profile_hides_footer_has_cursor_background_and_enforces_border_unlocks(): void
    {
        $newUser = User::create([
            'username' => 'pengguna_baru',
            'email' => 'baru@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Pengguna Baru',
        ]);

        // Akun baru tidak punya border
        $this->assertNull($newUser->avatar_border);
        $this->assertNull($newUser->avatar_border_url);

        // Profil menyembunyikan footer, tanpa lingkaran kursor, dan memuat pop-up modal border dengan Simpan Perubahan + toast kanan atas + animasi switch tema 3D
        $profilePage = $this->actingAs($newUser)->get('/profile');
        $profilePage->assertOk()
            ->assertDontSee('<footer', false)
            ->assertDontSee('profile-cursor-ring')
            ->assertSee('profile-cursor-stage')
            ->assertSee('id="digisafe-toast-stack"', false)
            ->assertSee('createIllustratedThemeSwitch')
            ->assertSee('id="avatar-borders"', false)
            ->assertSee('Simpan Perubahan');

        // Halaman direktori pengguna (/pendaftar) merender kartu profil dengan tinggi slot seragam
        $dirPage = $this->actingAs($newUser)->get('/pendaftar');
        $dirPage->assertOk()
            ->assertSee('h-[38px]', false);

        // Tidak boleh memasang border yang masih terkunci
        $this->actingAs($newUser)
            ->putJson('/profile/border', ['avatar_border' => 'aqua_crest'])
            ->assertStatus(403);

        $this->actingAs($newUser)
            ->putJson('/profile/border', ['avatar_border' => 'crimson_flare'])
            ->assertStatus(403);

        // Mainkan 1 minigame -> Aqua Crest terbuka!
        SkorGame::create([
            'user_id' => $newUser->id,
            'game_type' => 'phishing',
            'skor' => 60,
            'skor_tertinggi' => 60,
            'benar' => 6,
            'salah' => 4,
            'played_at' => now(),
        ]);

        $this->assertTrue(AvatarBorderService::isUnlockedForUser($newUser, 'aqua_crest'));
        $this->assertFalse(AvatarBorderService::isUnlockedForUser($newUser, 'cosmic_ring'));

        // Sekarang Aqua Crest dapat dipasang
        $this->actingAs($newUser)
            ->putJson('/profile/border', ['avatar_border' => 'aqua_crest'])
            ->assertOk()
            ->assertJsonPath('avatar_border', 'aqua_crest');

        $newUser->refresh();
        $this->assertSame('aqua_crest', $newUser->avatar_border);
        $this->assertStringContainsString('images/borders/aqua-crest.png', (string) $newUser->avatar_border_url);

        // Border tampil di forum & JSON data
        Forum::create([
            'user_id' => $newUser->id,
            'judul' => 'Tips Aman Siber',
            'kategori' => 'Phishing',
            'pertanyaan' => 'Bagaimana cek tautan aman?',
            'status' => 'terbuka',
            'created_at' => now(),
        ]);

        $this->actingAs($newUser)
            ->getJson('/forum/live-data')
            ->assertOk()
            ->assertJsonPath('forums.0.author_border_url', $newUser->avatar_border_url);

        // User juga bisa melepas border (opsional kembali ke tanpa border)
        $this->actingAs($newUser)
            ->putJson('/profile/border', ['avatar_border' => ''])
            ->assertOk()
            ->assertJsonPath('avatar_border', null);

        $this->assertNull($newUser->fresh()->avatar_border);
    }

    public function test_admin_panel_renders_user_growth_and_testimoni_star_rating_trend_diagrams(): void
    {
        $admin = User::create([
            'username' => 'admin_purwakarta',
            'email' => 'admin@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nama_lengkap' => 'Admin Purwakarta',
            'created_at' => now()->subMonth(),
        ]);

        // Tambah 2 user baru bulan ini sehingga tren user bulan ini (2) > bulan lalu (1) -> Menaik (+1 user)
        $u1 = User::create([
            'username' => 'warga_satu',
            'email' => 'warga1@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Satu',
            'created_at' => now()->subDays(2),
        ]);

        $u2 = User::create([
            'username' => 'warga_dua',
            'email' => 'warga2@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Dua',
            'created_at' => now()->subDay(),
        ]);

        // Tambah testimoni untuk menguji kenaikan Bintang 5, 4, 3, 2, 1
        Testimoni::create([
            'user_id' => $u1->id,
            'judul' => 'Ulasan Bintang 5 A',
            'cerita' => 'Sangat membantu warga mengenali phishing.',
            'rating' => 5,
            'mood' => '5',
            'is_anonymous' => false,
            'status_approval' => 'approved',
            'created_at' => now()->subDays(2),
        ]);

        Testimoni::create([
            'user_id' => $u2->id,
            'judul' => 'Ulasan Bintang 5 B',
            'cerita' => 'Simulasi kuisnya interaktif dan edukatif.',
            'rating' => 5,
            'mood' => '5',
            'is_anonymous' => false,
            'status_approval' => 'approved',
            'created_at' => now()->subDay(),
        ]);

        Testimoni::create([
            'user_id' => $admin->id,
            'judul' => 'Ulasan Bintang 4',
            'cerita' => 'Materi mudah dipahami.',
            'rating' => 4,
            'mood' => '4',
            'is_anonymous' => true,
            'status_approval' => 'approved',
            'created_at' => now(),
        ]);

        $analytics = AdminAnalyticsService::build();
        $this->assertSame('up', $analytics['user_growth']['monthly']['direction']);
        $this->assertSame(1, $analytics['user_growth']['monthly']['delta']);
        $this->assertCount(5, $analytics['testimoni_ratings']['stars']);
        $this->assertSame('up', $analytics['testimoni_ratings']['stars'][0]['monthly']['direction']);

        $resp = $this->actingAs($admin)->get('/admin/users');
        $resp->assertOk()
            ->assertSee('Analitik &amp; Diagram', false)
            ->assertSee('Analitik Interaktif &amp; Diagram Tren', false)
            ->assertSee('ANALISA PERTUMBUHAN PENGGUNA')
            ->assertSee('Tren Pendaftar User')
            ->assertSee('Distribusi &amp; Tren Rating', false);

        // Verifikasi Dark Mode pada halaman Tentang, Forum, dan Testimoni
        $admin->update(['theme' => 'dark']);

        $this->actingAs($admin)->get('/tentang')
            ->assertOk()
            ->assertSee('about-hero-surface')
            ->assertSee('about-command-bar')
            ->assertDontSee('style="background: linear-gradient(145deg, #E8F5E9', false);

        $this->actingAs($admin)->get('/forum')
            ->assertOk()
            ->assertSee('forum-curve-end-stop');

        $this->actingAs($admin)->get('/testimoni')
            ->assertOk()
            ->assertSee('testi-pill-glass');
    }

    public function test_dark_mode_switches_hero_to_nightsky_with_birds_and_mobile_auth_hides_mascots(): void
    {
        $user = User::create([
            'username' => 'langit_malam',
            'email' => 'malam@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Pengamat Langit',
            'theme' => 'light',
        ]);

        // Mode Terang: pakai bluesky.jpeg + kawanan burung
        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('images/bluesky.jpeg')
            ->assertDontSee('images/nightsky.jpg')
            ->assertSee('bird-flock')
            ->assertSee('hero-sky-scrim');

        // Beralih ke Mode Gelap via tombol mode switch: ganti ke nightsky.jpg, burung tetap ada, dan kartu default pakai nightsky.jpg
        $this->actingAs($user)
            ->postJson('/preferences/quick', ['theme' => 'dark'])
            ->assertOk()
            ->assertJsonPath('theme', 'dark');

        $this->actingAs($user->fresh())->get('/')
            ->assertOk()
            ->assertSee('images/nightsky.jpg')
            ->assertDontSee('images/bluesky.jpeg')
            ->assertSee('bird-flock')
            ->assertSee('hero-sky-scrim')
            ->assertSee('hero-surface-card');

        // Halaman Login & Register (sebagai guest): kolom maskot disembunyikan di mobile (hidden lg:flex)
        auth()->logout();

        $this->get('/login')
            ->assertOk()
            ->assertSee('hidden lg:flex lg:col-span-5 lg:order-1', false)
            ->assertSee('mascot-login.png');

        $this->get('/register')
            ->assertOk()
            ->assertSee('hidden lg:flex lg:col-span-5 lg:order-2', false)
            ->assertSee('mascot-register.png');
    }

    public function test_google_only_customize_onboarding_page_and_submission(): void
    {
        $regularUser = User::create([
            'username' => 'user_biasa',
            'email' => 'biasa@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'User Biasa',
        ]);

        // User non-Google tidak boleh mengakses halaman kustomisasi Google
        $this->actingAs($regularUser)
            ->get('/auth/google/customize')
            ->assertRedirect(route('beranda'));

        $googleUser = User::create([
            'username' => 'google_temp',
            'email' => 'warga.google@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Google',
            'oauth_provider' => 'google',
            'oauth_uid' => 'google-sub-12345',
        ]);

        // User Google yang belum selesai onboarding melihat halaman tanpa navbar + logo + maskot loading
        $this->actingAs($googleUser)
            ->withSession(['google_onboarding_pending' => true])
            ->get('/auth/google/customize')
            ->assertOk()
            ->assertDontSee('<nav', false)
            ->assertSee('images/logo.png')
            ->assertSee('images/mascot-loading.png')
            ->assertSee('nama_lengkap')
            ->assertSee('username')
            ->assertSee('avatar')
            ->assertSee('banner');

        // Jika mencoba lompat ke beranda sebelum selesai kustomisasi, tetap diarahkan ke halaman customize
        $this->actingAs($googleUser)
            ->withSession(['google_onboarding_pending' => true])
            ->get('/')
            ->assertRedirect(route('auth.google.customize'));

        // Nickname & Username wajib; username duplikat ditolak
        $this->actingAs($googleUser)
            ->postJson('/auth/google/customize', [
                'nama_lengkap' => '',
                'username' => 'user_biasa',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_lengkap', 'username']);

        // Submit valid tanpa avatar/banner (opsional) berhasil, menyimpan nickname & username dan menandai selesai
        $this->actingAs($googleUser)
            ->withSession(['google_onboarding_pending' => true])
            ->postJson('/auth/google/customize', [
                'nama_lengkap' => 'Bintang Siber',
                'username' => 'bintang_pwk',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('redirect', route('beranda'));

        $googleUser->refresh();
        $this->assertSame('Bintang Siber', $googleUser->nama_lengkap);
        $this->assertSame('bintang_pwk', $googleUser->username);
        $this->assertTrue((bool) ($googleUser->privacy_settings['google_onboarded'] ?? false));

        // Setelah selesai onboarding, mengakses /auth/google/customize langsung diarahkan ke Beranda
        $this->actingAs($googleUser)
            ->get('/auth/google/customize')
            ->assertRedirect(route('beranda'));
    }
}
