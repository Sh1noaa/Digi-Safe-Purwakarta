<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('jawaban_forum_votes');
        Schema::dropIfExists('jawaban_forum');
        Schema::dropIfExists('forum');
        Schema::dropIfExists('testimoni');
        Schema::dropIfExists('skor_game');
        Schema::dropIfExists('soal_minigame');
        Schema::dropIfExists('minigame_levels');
        Schema::dropIfExists('modul_edukasi');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('nama_lengkap')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->text('badge_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('reset_token')->nullable();
            $table->string('oauth_provider')->default('google');
            $table->string('oauth_uid')->nullable();
            $table->string('locale')->default('id');
            $table->string('theme')->default('light');
            $table->text('privacy_settings')->nullable();
        });

        Schema::create('modul_edukasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('konten')->nullable();
            $table->string('link_url')->nullable();
            $table->string('url')->nullable();
            $table->string('type')->default('teks');
            $table->string('gambar_url')->nullable();
            $table->string('kategori')->default('Literasi Siber');
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('soal_minigame', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->text('pilihan_json');
            $table->string('jawaban_benar');
            $table->string('kategori')->nullable();
            $table->string('tingkat')->default('easy');
            $table->string('game_level')->nullable();
        });

        Schema::create('skor_game', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('game_type');
            $table->integer('skor');
            $table->integer('skor_tertinggi');
            $table->timestamp('played_at')->nullable();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('nama_badge');
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('badge_id');
            $table->timestamp('diperoleh_pada')->nullable();
        });

        Schema::create('forum', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('judul');
            $table->text('pertanyaan');
            $table->string('kategori')->default('umum');
            $table->string('status')->default('terbuka');
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->integer('views')->default(1);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum', function (Blueprint $table) {
            $table->id();
            $table->integer('forum_id');
            $table->integer('user_id');
            $table->text('jawaban');
            $table->boolean('is_admin')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('judul')->nullable();
            $table->text('cerita');
            $table->integer('rating')->default(5);
            $table->string('mood')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status_approval')->default('approved');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_api_register_login_and_sync_admin_edukasi(): void
    {
        // 1. Register user biasa via API
        $regResp = $this->postJson('/api/auth/register', [
            'nama_lengkap' => 'Warga Uji',
            'username' => 'warga_uji',
            'email' => 'warga@test.id',
            'password' => 'password123',
        ]);

        $regResp->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.username', 'warga_uji');

        $userToken = $regResp->json('token');

        // 2. Buat akun admin dan terbitkan modul edukasi via Admin API
        $admin = User::create([
            'username' => 'admin_uji',
            'email' => 'admin@test.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nama_lengkap' => 'Admin Uji',
        ]);

        $adminLogin = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.id',
            'password' => 'password123',
        ])->assertOk();

        $adminToken = $adminLogin->json('token');

        $this->withHeader('Authorization', "Bearer $adminToken")
            ->postJson('/api/admin/edukasi', [
                'judul' => 'Waspada Phishing APK Terbaru',
                'kategori' => 'Phishing',
                'type' => 'teks',
                'konten' => 'Jangan pernah menginstal file .apk dari nomor asing.',
            ])
            ->assertCreated();

        // 3. Pastikan endpoint /api/edukasi langsung mengembalikan modul yang diupload Admin
        $this->withHeader('Authorization', "Bearer $userToken")
            ->getJson('/api/edukasi')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('modules.0.title', 'Waspada Phishing APK Terbaru');
    }

    public function test_forum_realtime_voting_and_most_helpful_and_minigame_score(): void
    {
        $user = User::create([
            'username' => 'penanya',
            'email' => 'penanya@test.id',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'nama_lengkap' => 'Penanya',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'penanya@test.id',
            'password' => 'secret123',
        ])->json('token');

        // Buat thread forum & balasan & vote up
        $threadResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/forum', [
                'judul' => 'Cek link mencurigakan',
                'kategori' => 'phishing',
                'pertanyaan' => 'Apakah domain .xyz aman?',
            ])->assertCreated();

        $forumId = $threadResp->json('forum.id');

        $replyResp = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/forum/$forumId/jawaban", [
                'jawaban' => 'Tidak aman, selalu cek domain .go.id resmi.',
            ])->assertCreated();

        $jawabanId = $replyResp->json('forum.jawaban.0.id');

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/forum/jawaban/$jawabanId/vote", ['vote_type' => 'up'])
            ->assertOk()
            ->assertJsonPath('forum.has_most_helpful', true)
            ->assertJsonPath('forum.jawaban.0.is_most_helpful', true);

        // Simpan skor minigame & cek lencana otomatis
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/minigame/score', [
                'game_type' => 'phishing',
                'skor' => 80,
                'benar' => 5,
                'salah' => 0,
                'avg_time_sec' => 5.5,
            ])
            ->assertOk()
            ->assertJsonPath('saved', true);

        // Toggle settings privasi, bahasa EN & dark mode
        $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/profile/settings', [
                'locale' => 'en',
                'theme' => 'dark',
                'is_private_profile' => true,
            ])
            ->assertOk()
            ->assertJsonPath('user.locale', 'en')
            ->assertJsonPath('user.theme', 'dark')
            ->assertJsonPath('user.is_private_profile', true);
    }

    public function test_mobile_api_role_selector_leaderboard_edukasi_request_and_bot_digika_sync(): void
    {
        $user = User::create([
            'username' => 'citizen_apk',
            'email' => 'citizen@apk.id',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga APK',
        ]);

        $admin = User::create([
            'username' => 'admin_apk',
            'email' => 'admin@apk.id',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'nama_lengkap' => 'Admin APK',
        ]);

        $userToken = $this->postJson('/api/auth/login', [
            'email' => 'citizen@apk.id',
            'password' => 'secret123',
        ])->json('token');

        $adminToken = $this->postJson('/api/auth/login', [
            'email' => 'admin@apk.id',
            'password' => 'secret123',
        ])->json('token');

        // 1. Select Your Role via API
        $this->withHeader('Authorization', "Bearer $userToken")
            ->putJson('/api/profile/role', ['specialty_role' => 'Phishing Hunter'])
            ->assertOk()
            ->assertJsonPath('user.specialty_role', 'Phishing Hunter');

        // 2. Submit Edukasi Topic Request
        $reqResp = $this->withHeader('Authorization', "Bearer $userToken")
            ->postJson('/api/edukasi/requests', [
                'tema' => 'Modus Penipuan QRIS',
                'kategori' => 'Phishing & Penipuan',
                'preferred_type' => 'teks',
                'deskripsi' => 'Membantu warga memeriksa nama merchant QRIS.',
            ])
            ->assertCreated()
            ->assertJsonPath('request.status', 'pending');

        $requestId = $reqResp->json('request.id');

        // 3. Admin approves -> Bot Digika sends automated message via API Chat
        $this->withHeader('Authorization', "Bearer $adminToken")
            ->patchJson("/api/admin/edukasi/requests/{$requestId}/approve", [
                'admin_note' => 'Segera diterbitkan minggu ini!',
            ])
            ->assertOk();

        $this->withHeader('Authorization', "Bearer $userToken")
            ->getJson('/api/chat/unread-count')
            ->assertOk()
            ->assertJsonPath('unread_messages', 1)
            ->assertJsonPath('latest_alert.is_bot', true);

        // 4. 5-Category Leaderboard API (minigame_benar, streak, wa_accuracy, forum, badges)
        $this->getJson('/api/leaderboard')
            ->assertOk()
            ->assertJsonCount(5, 'categories');

        // Suppress unused variable
        $this->assertNotNull($admin->id);
        $this->assertNotNull($user->id);
    }
}

