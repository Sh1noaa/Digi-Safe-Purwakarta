<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SocialChatAndRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('friendships');
        Schema::dropIfExists('testimoni');
        Schema::dropIfExists('jawaban_forum');
        Schema::dropIfExists('forum');
        Schema::dropIfExists('skor_game');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('users');

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

        Schema::create('forum', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('judul');
            $table->text('isi');
            $table->string('kategori')->default('Umum');
            $table->string('status')->default('terbuka');
            $table->boolean('is_starred')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('jawaban_forum', function (Blueprint $table) {
            $table->id();
            $table->integer('forum_id');
            $table->integer('user_id');
            $table->text('isi_jawaban');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('judul');
            $table->text('cerita');
            $table->tinyInteger('rating')->default(5);
            $table->boolean('is_anonymous')->default(false);
            $table->string('status')->default('approved');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('addressee_id');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['requester_id', 'addressee_id']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function test_password_checker_and_profile_specialty_role_work(): void
    {
        $this->get('/minigame/password-checker')->assertOk()->assertSee('Password Strength Checker');

        $user = User::create([
            'username' => 'raziefdev',
            'email' => 'raziefdev@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Razief Dev',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Razief Dev',
                'specialty_role' => 'Software Engineer',
                'bio' => 'Suka ngoding & cyber security',
            ])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'specialty_role' => 'Software Engineer',
        ]);

        $this->get('/profile/' . $user->id)->assertOk()->assertSee('Software Engineer');
        $this->get('/leaderboard')->assertOk()->assertSee('Leaderboard');
    }

    public function test_non_friend_chat_requires_receiver_confirmation_and_unlocks_on_accept(): void
    {
        $alice = User::create([
            'username' => 'alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Alice',
            'created_at' => now(),
        ]);

        $bob = User::create([
            'username' => 'bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Bob',
            'created_at' => now(),
        ]);

        // Alice mengirim pesan pertama ke Bob meski belum berteman
        $this->actingAs($alice)
            ->postJson('/chat/messages/' . $bob->id, ['message' => 'Halo Bob, boleh berteman?'])
            ->assertOk()
            ->assertJsonPath('relation', 'pending_outgoing');

        // Bob mendapat notifikasi pesan & permintaan masuk
        $this->actingAs($bob)
            ->getJson('/chat/unread-count')
            ->assertOk()
            ->assertJson([
                'unread_messages' => 1,
                'pending_requests' => 1,
                'total_badge' => 2,
            ]);

        // Bob belum bisa membalas sebelum konfirmasi
        $this->actingAs($bob)
            ->postJson('/chat/messages/' . $alice->id, ['message' => 'Test'])
            ->assertStatus(403);

        // Bob mengonfirmasi permintaan
        $this->actingAs($bob)
            ->postJson('/friends/accept/' . $alice->id)
            ->assertOk()
            ->assertJsonPath('status', 'accepted');

        // Setelah berteman, kedua arah dapat chat bebas
        $this->actingAs($bob)
            ->postJson('/chat/messages/' . $alice->id, ['message' => 'Halo Alice, sudah dikonfirmasi ya!'])
            ->assertOk()
            ->assertJsonPath('relation', 'accepted');
    }

    public function test_declined_chat_request_blocks_further_messages(): void
    {
        $sender = User::create([
            'username' => 'charlie',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Charlie',
            'created_at' => now(),
        ]);

        $target = User::create([
            'username' => 'dina',
            'email' => 'dina@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Dina',
            'created_at' => now(),
        ]);

        $this->actingAs($sender)
            ->postJson('/chat/messages/' . $target->id, ['message' => 'Hai Dina'])
            ->assertOk();

        $this->actingAs($target)
            ->postJson('/friends/decline/' . $sender->id)
            ->assertOk()
            ->assertJsonPath('status', 'declined');

        $this->actingAs($sender)
            ->postJson('/chat/messages/' . $target->id, ['message' => 'Halo lagi'])
            ->assertStatus(403);
    }
}
