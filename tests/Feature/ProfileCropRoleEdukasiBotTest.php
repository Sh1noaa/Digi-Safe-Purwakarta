<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\EdukasiRequest;
use App\Models\ModulEdukasi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileCropRoleEdukasiBotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'edukasi_requests',
            'modul_edukasi',
            'chat_messages',
            'friendships',
            'testimoni',
            'jawaban_forum_votes',
            'jawaban_forum',
            'forum',
            'skor_game',
            'soal_minigame',
            'minigame_levels',
            'user_badges',
            'badges',
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
            $table->string('specialty_role', 80)->nullable();
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

        Schema::create('skor_game', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('game_type');
            $table->integer('skor');
            $table->integer('skor_tertinggi');
            $table->unsignedInteger('benar')->default(0);
            $table->unsignedInteger('salah')->default(0);
            $table->timestamp('played_at')->nullable();
        });

        Schema::create('minigame_levels', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('badge_label')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->unsignedInteger('points_per_correct')->default(10);
            $table->unsignedInteger('timer_seconds')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('soal_minigame', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->text('pilihan_json')->nullable();
            $table->string('jawaban_benar')->nullable();
            $table->string('kategori')->nullable();
            $table->string('tingkat')->default('easy');
            $table->string('game_level')->default('phishing');
        });

        Schema::create('forum', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('judul');
            $table->text('pertanyaan')->nullable();
            $table->string('kategori')->default('Umum');
            $table->string('status')->default('terbuka');
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->integer('views')->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum', function (Blueprint $table) {
            $table->id();
            $table->integer('forum_id');
            $table->integer('user_id');
            $table->text('jawaban')->nullable();
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
            $table->integer('user_id')->nullable();
            $table->string('judul');
            $table->text('cerita');
            $table->tinyInteger('rating')->default(5);
            $table->boolean('is_anonymous')->default(false);
            $table->string('status_approval')->default('approved');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('modul_edukasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('konten');
            $table->string('link_url', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('type', 30)->default('link');
            $table->string('gambar_url', 500)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('addressee_id');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('edukasi_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('tema', 180);
            $table->string('kategori', 80)->default('Keamanan Akun');
            $table->string('preferred_type', 20)->default('teks');
            $table->text('deskripsi');
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('user_notified_read')->default(false);
            $table->timestamps();
        });
    }

    public function test_select_your_role_has_max_4_varied_options_and_updates_profile(): void
    {
        $roles = User::availableSpecialtyRoles();
        $this->assertCount(4, $roles);

        $user = User::create([
            'username' => 'warga_purwakarta',
            'email' => 'warga@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Siber',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Select Your Role')
            ->assertSee('Cyber Guardian')
            ->assertSee('Digital Educator')
            ->assertSee('Phishing Hunter')
            ->assertSee('Tech Explorer');

        $this->actingAs($user)
            ->put('/profile/role', ['specialty_role' => 'Cyber Guardian'])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'specialty_role' => 'Cyber Guardian',
        ]);
    }

    public function test_user_can_upload_gif_banner_and_avatar_on_profile(): void
    {
        $user = User::create([
            'username' => 'gifuser',
            'email' => 'gifuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'GIF Tester',
            'created_at' => now(),
        ]);

        // 1x1 valid GIF binary payload
        $gifBytes = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $avatarGif = UploadedFile::fake()->createWithContent('avatar.gif', $gifBytes);
        $bannerGif = UploadedFile::fake()->createWithContent('banner.gif', $gifBytes);

        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'GIF Tester Updated',
                'specialty_role' => 'Tech Explorer',
                'bio' => 'Purwakarta',
                'avatar' => $avatarGif,
                'banner' => $bannerGif,
            ])
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertStringStartsWith('images/avatars/', (string) $user->avatar);
        $this->assertStringStartsWith('images/banners/', (string) $user->banner);
        $this->assertSame('Tech Explorer', $user->specialty_role);

        // Cleanup generated test files
        @unlink(public_path($user->avatar));
        @unlink(public_path($user->banner));
        @unlink(storage_path('app/public/' . str_replace('images/', '', $user->avatar)));
        @unlink(storage_path('app/public/' . str_replace('images/', '', $user->banner)));
    }

    public function test_edukasi_topic_request_and_admin_evaluation_sends_bot_digika_messages(): void
    {
        $admin = User::create([
            'username' => 'admin_test',
            'email' => 'admin@digisafe.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nama_lengkap' => 'Admin Digi-Safe',
            'created_at' => now(),
        ]);

        $member = User::create([
            'username' => 'pelajar1',
            'email' => 'pelajar1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Budi Pelajar',
            'created_at' => now(),
        ]);

        // 1. User mengajukan 2 request tema edukasi
        $this->actingAs($member)
            ->post('/edukasi/request', [
                'tema' => 'Waspada QRIS Palsu di Tempat Umum',
                'kategori' => 'Anti-Phishing',
                'preferred_type' => 'teks',
                'deskripsi' => 'Banyak penipuan stiker QRIS tempelan di warung dan masjid.',
            ])
            ->assertRedirect('/edukasi');

        $this->actingAs($member)
            ->post('/edukasi/request', [
                'tema' => 'Cara Hacking WiFi Tetangga',
                'kategori' => 'Lainnya',
                'preferred_type' => 'teks',
                'deskripsi' => 'Mohon buatkan tutorial cara membobol WiFi sekitar.',
            ])
            ->assertRedirect('/edukasi');

        $reqApprove = EdukasiRequest::where('tema', 'Waspada QRIS Palsu di Tempat Umum')->firstOrFail();
        $reqReject = EdukasiRequest::where('tema', 'Cara Hacking WiFi Tetangga')->firstOrFail();

        // 2. Admin menyetujui request pertama
        $this->actingAs($admin)
            ->patch('/admin/edukasi/requests/' . $reqApprove->id . '/approve', [
                'admin_note' => 'Segera kami terbitkan minggu ini!',
            ])
            ->assertRedirect('/admin/edukasi?tab=requests');

        // 3. Admin menolak request kedua
        $this->actingAs($admin)
            ->patch('/admin/edukasi/requests/' . $reqReject->id . '/reject', [
                'admin_note' => 'Topik melanggar etika keamanan siber.',
            ])
            ->assertRedirect('/admin/edukasi?tab=requests');

        $reqApprove->refresh();
        $reqReject->refresh();
        $this->assertSame('approved', $reqApprove->status);
        $this->assertSame('rejected', $reqReject->status);

        // 4. Bot Digika otomatis terbuat dan mengirim 2 pesan ke Budi Pelajar
        $bot = User::where('email', User::BOT_DIGIKA_EMAIL)->first();
        $this->assertNotNull($bot);
        $this->assertSame('Bot Digika', $bot->nama_lengkap);
        $this->assertSame('images/bot-digika.jpg', $bot->avatar);
        $this->assertTrue($bot->isBot());

        $messages = ChatMessage::where('sender_id', $bot->id)
            ->where('receiver_id', $member->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('MENYETUJUI', $messages[0]->message);
        $this->assertStringContainsString('Bot Digika', $messages[0]->message);
        $this->assertStringContainsString('BELUM DAPAT KAMI SETUJUI', $messages[1]->message);
        $this->assertStringContainsString('Bot Digika', $messages[1]->message);

        // 5. Notifikasi muncul di chat unread-count & summary (termasuk avatar Bot Digika), tapi user tidak bisa membalas bot
        $this->actingAs($member)
            ->getJson('/chat/unread-count')
            ->assertOk()
            ->assertJsonPath('unread_messages', 2)
            ->assertJsonPath('latest_alert.is_bot', true)
            ->assertJsonPath('latest_alert.sender_name', 'Bot Digika')
            ->assertJsonPath('latest_alert.sender_avatar', asset('images/bot-digika.jpg'));

        $this->actingAs($member)
            ->getJson('/chat/summary')
            ->assertOk()
            ->assertJsonPath('threads.0.user.is_bot', true)
            ->assertJsonPath('threads.0.user.avatar_url', asset('images/bot-digika.jpg'));

        $this->actingAs($member)
            ->postJson('/chat/messages/' . $bot->id, ['message' => 'Halo bot'])
            ->assertStatus(422);

        // 6. Bot Digika TIDAK muncul di Beranda marquee, Direktori pendaftar, maupun Cari Warga chat
        $this->get('/')
            ->assertOk()
            ->assertSee('memberMarquee')
            ->assertSee('Budi Pelajar')
            ->assertDontSee('bot_digika');

        $this->get('/pendaftar')
            ->assertOk()
            ->assertSee('Budi Pelajar')
            ->assertDontSee('bot_digika');

        $this->actingAs($member)
            ->getJson('/chat/users')
            ->assertOk()
            ->assertJsonMissing(['username' => 'bot_digika']);
    }

    public function test_dual_environment_files_for_localhost_and_rumahweb_exist(): void
    {
        $this->assertFileExists(base_path('.env.local'));
        $this->assertFileExists(base_path('.env.hosting'));
        $this->assertFileExists(base_path('database/update_rumahweb_2026_11_18.sql'));

        $localEnv = file_get_contents(base_path('.env.local'));
        $hostingEnv = file_get_contents(base_path('.env.hosting'));

        $this->assertStringContainsString('http://127.0.0.1:8000/auth/google/callback', (string) $localEnv);
        $this->assertStringContainsString('https://digisafepurwakarta.web.id/auth/google/callback', (string) $hostingEnv);
        $this->assertStringContainsString('404289059522-3ot7094tdid9hi1hfhaug4m6845p9hfg.apps.googleusercontent.com', (string) $localEnv);
        $this->assertStringContainsString('404289059522-3ot7094tdid9hi1hfhaug4m6845p9hfg.apps.googleusercontent.com', (string) $hostingEnv);
    }
}
