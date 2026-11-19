<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\MinigameLevel;
use App\Models\Testimoni;
use App\Models\User;
use App\Services\SocialSchemaHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RevisionFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'user_follows',
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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('diperoleh_pada')->nullable();
        });

        Schema::create('skor_game', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('game_level')->default('phishing');
            $table->string('tingkat')->default('mudah');
            $table->integer('skor')->default(0);
            $table->integer('skor_tertinggi')->default(0);
            $table->integer('waktu_tercepat')->default(0);
            $table->unsignedInteger('benar')->default(0);
            $table->unsignedInteger('salah')->default(0);
            $table->timestamp('dimainkan_pada')->nullable();
        });

        Schema::create('soal_minigame', function (Blueprint $table) {
            $table->id();
            $table->string('game_level')->default('phishing');
            $table->string('tingkat')->default('mudah');
            $table->text('pertanyaan');
            $table->string('opsi_a')->default('A');
            $table->string('opsi_b')->default('B');
            $table->string('opsi_c')->default('C');
            $table->string('opsi_d')->default('D');
            $table->string('jawaban_benar', 1)->default('A');
            $table->text('penjelasan')->nullable();
            $table->timestamps();
        });

        Schema::create('forum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('judul');
            $table->string('kategori')->default('Umum');
            $table->text('pertanyaan');
            $table->boolean('is_starred')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forum_id');
            $table->unsignedBigInteger('user_id');
            $table->text('jawaban');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('judul')->nullable();
            $table->text('cerita');
            $table->integer('rating')->default(5);
            $table->string('mood')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status_approval')->default('approved');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('modul_edukasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->default('Umum');
            $table->text('konten')->nullable();
            $table->string('url_eksternal')->nullable();
            $table->string('tipe_konten')->default('teks');
            $table->string('gambar')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        SocialSchemaHelper::ensureReady();
        MinigameLevel::ensureInitialized();
    }

    private function makeUser(string $username, string $role = 'user'): User
    {
        return User::create([
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
            'nama_lengkap' => ucfirst($username),
        ]);
    }

    public function test_follow_unfollow_and_followers_following_list(): void
    {
        $alice = $this->makeUser('alice');
        $bob = $this->makeUser('bob');

        // Toggle follow
        $this->actingAs($alice)
            ->postJson(route('users.follow.toggle', $bob->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'following' => true,
                'followers_count' => 1,
                'my_following_count' => 1,
            ]);

        // Daftar followers & following
        $this->getJson(route('users.follows.list', $bob->id))
            ->assertOk()
            ->assertJsonPath('followers_count', 1)
            ->assertJsonPath('followers.0.username', 'alice');

        // Tidak boleh follow diri sendiri
        $this->actingAs($alice)
            ->postJson(route('users.follow.toggle', $alice->id))
            ->assertStatus(422);

        // Toggle unfollow
        $this->actingAs($alice)
            ->postJson(route('users.follow.toggle', $bob->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'following' => false,
                'followers_count' => 0,
            ]);
    }

    public function test_beranda_displays_real_testimoni_from_database(): void
    {
        $user = $this->makeUser('warga_asli');
        Testimoni::create([
            'user_id' => $user->id,
            'judul' => 'Ulasan Nyata',
            'cerita' => 'Ulasan asli dari warga Purwakarta tentang simulasi anti phishing Digi-Safe!',
            'rating' => 5,
            'mood' => '5',
            'is_anonymous' => 0,
            'status_approval' => 'approved',
            'created_at' => now(),
        ]);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Ulasan asli dari warga Purwakarta tentang simulasi anti phishing Digi-Safe!');
    }

    public function test_admin_can_pin_and_unpin_forum_while_regular_user_cannot(): void
    {
        $admin = $this->makeUser('admin_pwk', 'admin');
        $user = $this->makeUser('warga_biasa', 'user');

        $forum = Forum::create([
            'user_id' => $user->id,
            'judul' => 'Waspada Link Undangan APK',
            'kategori' => 'Phishing',
            'pertanyaan' => 'Bagaimana cara mengecek file APK palsu di grup WA?',
            'is_starred' => false,
            'created_at' => now(),
        ]);

        // User biasa ditolak
        $this->actingAs($user)
            ->patchJson(route('admin.forum.star', $forum->id))
            ->assertStatus(403);

        // Admin bisa pin
        $this->actingAs($admin)
            ->patchJson(route('admin.forum.star', $forum->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_starred' => true]);

        // Admin bisa unpin
        $this->actingAs($admin)
            ->patchJson(route('admin.forum.star', $forum->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_starred' => false]);

        // Halaman admin panel & custom-select render tanpa error
        $this->actingAs($admin)->get(route('admin.forum.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.minigame.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.user.index'))->assertOk();
    }

    public function test_chat_messages_include_whatsapp_style_date_month_year(): void
    {
        $alice = $this->makeUser('chat_alice');
        $bob = $this->makeUser('chat_bob');

        $sendRes = $this->actingAs($alice)
            ->postJson(route('chat.send', $bob->id), ['message' => 'Halo Bob, cek tanggal ya!'])
            ->assertOk();

        $sendRes->assertJsonStructure([
            'relation',
            'message' => ['id', 'sender_id', 'is_mine', 'message', 'time', 'date_key', 'date_label', 'full_datetime'],
        ]);

        $msgData = $sendRes->json('message');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $msgData['date_key']);
        $this->assertStringContainsString((string) now('Asia/Jakarta')->year, $msgData['date_label']);

        $this->actingAs($bob)
            ->getJson(route('chat.messages', $alice->id))
            ->assertOk()
            ->assertJsonPath('messages.0.date_key', $msgData['date_key'])
            ->assertJsonPath('messages.0.date_label', $msgData['date_label']);
    }
}
