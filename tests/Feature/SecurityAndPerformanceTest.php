<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Friendship;
use App\Models\SkorGame;
use App\Models\User;
use App\Services\UploadValidationHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityAndPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'api_tokens',
            'chat_messages',
            'friendships',
            'user_follows',
            'user_badges',
            'badges',
            'jawaban_forum_votes',
            'jawaban_forum',
            'forum',
            'skor_game',
            'minigame_levels',
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
    }

    public function test_disposable_and_fake_emails_are_rejected_on_register(): void
    {
        $badEmails = [
            'spammer@mailinator.com',
            'bot@yopmail.com',
            'fake@example.com',
            'typo@gamil.com',
            'a@b.co',
        ];

        foreach ($badEmails as $idx => $email) {
            $resp = $this->postJson('/api/auth/register', [
                'nama_lengkap' => 'Spammer ' . $idx,
                'username' => 'spammer_' . $idx,
                'email' => $email,
                'password' => 'password123',
            ]);

            $resp->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    public function test_guest_cannot_spam_testimoni_endpoints(): void
    {
        $this->post('/testimoni', [
            'rating' => 5,
            'cerita' => 'Spam testimoni tanpa login',
        ])->assertRedirect('/login');

        $this->postJson('/api/testimoni', [
            'rating' => 5,
            'cerita' => 'Spam API testimoni tanpa login',
        ])->assertStatus(401);
    }

    public function test_minigame_rejects_tampered_scores_and_computes_real_100_percent_accuracy(): void
    {
        $user = User::create([
            'username' => 'gamer_valid',
            'email' => 'gamer@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Gamer Valid',
        ]);

        // Skor tidak wajar (999999) harus ditolak
        $this->actingAs($user)
            ->postJson('/minigame/save-score', [
                'game_type' => 'phishing',
                'skor' => 999999,
                'benar' => 10,
                'salah' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['skor']);

        // Skor wajar tanpa salah harus tersimpan & akurasi benar-benar 100%
        $this->actingAs($user)
            ->postJson('/minigame/save-score', [
                'game_type' => 'phishing',
                'skor' => 100,
                'benar' => 10,
                'salah' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->assertDatabaseHas('skor_game', [
            'user_id' => $user->id,
            'benar' => 10,
            'salah' => 0,
        ]);

        $resp = $this->actingAs($user)->get('/minigame');
        $resp->assertOk();
        $stats = $resp->viewData('stats');
        $this->assertSame(100, $stats['akurasi']);
        $this->assertSame(10, $stats['total_benar']);
        $this->assertSame(0, $stats['total_salah']);
    }

    public function test_security_headers_are_attached_to_responses(): void
    {
        $resp = $this->get('/api/health');

        $resp->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_upload_helper_never_trusts_php_client_extension(): void
    {
        // Buat PNG valid 1x1 pixel namun dinamai evil.php
        $tmpPath = tempnam(sys_get_temp_dir(), 'img_');
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($tmpPath, $pngBytes);

        $file = new UploadedFile($tmpPath, 'evil.php', 'image/png', null, true);
        $safeExt = UploadValidationHelper::safeExtension($file);

        @unlink($tmpPath);

        $this->assertSame('png', $safeExt);
    }

    public function test_chat_summary_batch_queries_return_correct_threads_and_unread(): void
    {
        $alice = User::create([
            'username' => 'alice_pwk',
            'email' => 'alice@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Alice',
        ]);
        $bob = User::create([
            'username' => 'bob_pwk',
            'email' => 'bob@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Bob',
        ]);

        // Pastikan tabel sosial dibuat
        $this->actingAs($alice)->getJson('/chat/summary')->assertOk();

        Friendship::create([
            'requester_id' => $alice->id,
            'addressee_id' => $bob->id,
            'status' => 'accepted',
        ]);

        ChatMessage::create([
            'sender_id' => $bob->id,
            'receiver_id' => $alice->id,
            'message' => 'Halo Alice!',
            'is_read' => false,
        ]);

        $resp = $this->actingAs($alice)->getJson('/chat/summary');
        $resp->assertOk()
            ->assertJsonPath('unread_messages', 1)
            ->assertJsonPath('threads.0.user.username', 'bob_pwk')
            ->assertJsonPath('threads.0.last_message', 'Halo Alice!')
            ->assertJsonPath('threads.0.unread_count', 1);
    }

    public function test_quick_preferences_switches_theme_and_locale_for_guest_and_user(): void
    {
        // Guest dapat mengganti tema & bahasa secara instan
        $this->postJson('/preferences/quick', ['theme' => 'dark', 'locale' => 'en'])
            ->assertOk()
            ->assertJsonPath('theme', 'dark')
            ->assertJsonPath('locale', 'en');

        // User login juga tersimpan ke database
        $user = User::create([
            'username' => 'pref_user',
            'email' => 'pref@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Pref User',
        ]);

        $this->actingAs($user)
            ->postJson('/preferences/quick', ['theme' => 'dark', 'locale' => 'en'])
            ->assertOk()
            ->assertJsonPath('theme', 'dark')
            ->assertJsonPath('locale', 'en');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme' => 'dark',
            'locale' => 'en',
        ]);
    }

    public function test_user_can_edit_and_delete_own_forum_and_reply_but_not_others(): void
    {
        $author = User::create([
            'username' => 'penulis_forum',
            'email' => 'penulis@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Penulis Forum',
        ]);
        $other = User::create([
            'username' => 'orang_lain',
            'email' => 'lain@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Orang Lain',
        ]);

        // Penulis membuat diskusi
        $create = $this->actingAs($author)->postJson('/forum', [
            'judul' => 'Judul Awal',
            'kategori' => 'Phishing',
            'pertanyaan' => 'Isi pertanyaan awal.',
        ])->assertOk();

        $forumId = $create->json('forum.id');

        // Orang lain TIDAK boleh edit atau hapus diskusi penulis
        $this->actingAs($other)
            ->putJson("/forum/{$forumId}", [
                'judul' => 'Dibajak',
                'kategori' => 'Phishing',
                'pertanyaan' => 'Coba ubah.',
            ])
            ->assertStatus(403);

        $this->actingAs($other)
            ->deleteJson("/forum/{$forumId}")
            ->assertStatus(403);

        // Penulis BISA edit diskusinya sendiri
        $this->actingAs($author)
            ->putJson("/forum/{$forumId}", [
                'judul' => 'Judul Sudah Direvisi',
                'kategori' => 'Privasi',
                'pertanyaan' => 'Isi pertanyaan yang sudah diperbaiki.',
            ])
            ->assertOk()
            ->assertJsonPath('forum.judul', 'Judul Sudah Direvisi');

        // Orang lain membalas, lalu menghapus balasannya sendiri
        $reply = $this->actingAs($other)->postJson("/forum/{$forumId}/jawaban", [
            'jawaban' => 'Balasan typo saya.',
        ])->assertOk();

        $jawabanId = $reply->json('jawaban.id');

        $this->actingAs($other)
            ->deleteJson("/forum/jawaban/{$jawabanId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('jawaban_forum', ['id' => $jawabanId]);

        // Penulis menghapus diskusinya sendiri
        $this->actingAs($author)
            ->deleteJson("/forum/{$forumId}")
            ->assertOk()
            ->assertJsonPath('deleted_id', $forumId);

        $this->assertDatabaseMissing('forum', ['id' => $forumId]);
    }

    public function test_private_profile_hides_followers_list_from_others_and_hides_oauth_uid(): void
    {
        $privateUser = User::create([
            'username' => 'akun_privat',
            'email' => 'privat@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Akun Privat',
            'oauth_uid' => 'secret-google-sub-123',
            'privacy_settings' => ['is_private_profile' => true],
        ]);
        $stranger = User::create([
            'username' => 'pengunjung_lain',
            'email' => 'pengunjung@purwakarta.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Pengunjung',
        ]);

        // oauth_uid tidak boleh muncul saat model diserialisasi
        $arr = $privateUser->toArray();
        $this->assertArrayNotHasKey('oauth_uid', $arr);
        $this->assertArrayNotHasKey('password', $arr);

        // Tamu dan user lain ditolak melihat daftar followers akun privat
        $this->getJson("/users/{$privateUser->id}/follows")
            ->assertStatus(403)
            ->assertJsonPath('is_private', true);

        $this->actingAs($stranger)
            ->getJson("/users/{$privateUser->id}/follows")
            ->assertStatus(403)
            ->assertJsonPath('is_private', true);

        // Pemilik akun tetap bisa melihat daftar followers miliknya
        $this->actingAs($privateUser)
            ->getJson("/users/{$privateUser->id}/follows")
            ->assertOk();
    }

    public function test_password_change_requires_current_password_and_public_docs_removed(): void
    {
        $user = User::create([
            'username' => 'ganti_sandi',
            'email' => 'sandi@purwakarta.id',
            'password' => Hash::make('oldpassword123'),
            'role' => 'user',
            'nama_lengkap' => 'Ganti Sandi',
        ]);

        // Ganti password tanpa current_password harus gagal
        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Ganti Sandi',
                'password' => 'newpassword999',
            ])
            ->assertSessionHasErrors(['current_password']);

        // Ganti password dengan current_password benar harus berhasil
        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Ganti Sandi',
                'current_password' => 'oldpassword123',
                'password' => 'newpassword999',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('newpassword999', (string) $user->fresh()->password));

        // Pastikan file sensitif tidak lagi ada di public/
        $this->assertDirectoryDoesNotExist(public_path('docs'));
        $this->assertFileDoesNotExist(public_path('build.zip'));

        // Halaman testimoni untuk tamu menampilkan ajakan masuk (bukan form yang gagal 401)
        $this->app['auth']->logout();
        $this->get('/testimoni')
            ->assertOk()
            ->assertSee('Masuk terlebih dahulu untuk menulis ulasan');
    }

    public function test_api_password_requires_current_password_and_border_forum_chat_privacy_guards(): void
    {
        $ownerUser = User::create([
            'username' => 'user_utama',
            'email' => 'utama@purwakarta.id',
            'password' => Hash::make('oldsecret123'),
            'role' => 'user',
            'nama_lengkap' => 'User Utama',
            'bio' => 'Bio lengkap',
            'avatar' => 'images/avatars/dummy.png',
            'banner' => 'images/banners/dummy.png',
        ]);

        $privateTarget = User::create([
            'username' => 'warga_privat',
            'email' => 'wargaprivat@purwakarta.id',
            'password' => Hash::make('oldsecret123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Privat',
            'specialty_role' => 'Cyber Defender',
            'privacy_settings' => ['is_private_profile' => true],
        ]);

        $adminUser = User::create([
            'username' => 'admin_analytics',
            'email' => 'admin_analytics@purwakarta.id',
            'password' => Hash::make('oldsecret123'),
            'role' => 'admin',
            'nama_lengkap' => 'Admin Analytics',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'utama@purwakarta.id',
            'password' => 'oldsecret123',
        ])->assertOk()->json('token');

        // 1. Ganti password via API tanpa current_password harus ditolak (422)
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/profile', [
                'nama_lengkap' => 'User Utama',
                'password' => 'newsecret999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // Ganti password via API dengan current_password salah harus ditolak (422)
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/profile', [
                'nama_lengkap' => 'User Utama',
                'current_password' => 'salah12345',
                'password' => 'newsecret999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // 2. Equip border yang belum terbuka (crimson_flare) via API harus ditolak (403)
        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/profile/border', ['avatar_border' => 'crimson_flare'])
            ->assertStatus(403);

        // Selesaikan 1 sesi minigame agar aqua_crest terbuka, lalu equip via API harus berhasil (200)
        SkorGame::create([
            'user_id' => $ownerUser->id,
            'game_type' => 'phishing',
            'skor' => 60,
            'skor_tertinggi' => 60,
            'benar' => 6,
            'salah' => 4,
            'played_at' => now(),
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/profile/border', ['avatar_border' => 'aqua_crest'])
            ->assertOk()
            ->assertJsonPath('avatar_border', 'aqua_crest');

        // 3. Edit & hapus forum via API hanya boleh oleh pemilik atau admin
        $forumResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/forum', [
                'judul' => 'Diskusi API',
                'kategori' => 'Phishing',
                'pertanyaan' => 'Pertanyaan via API.',
            ])
            ->assertCreated();
        $forumId = $forumResp->json('forum.id');

        $otherToken = $this->postJson('/api/auth/login', [
            'email' => 'wargaprivat@purwakarta.id',
            'password' => 'oldsecret123',
        ])->assertOk()->json('token');

        $this->withHeader('Authorization', "Bearer {$otherToken}")
            ->putJson("/api/forum/{$forumId}", [
                'judul' => 'Bajak',
                'kategori' => 'Phishing',
                'pertanyaan' => 'Bajak.',
            ])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/forum/{$forumId}", [
                'judul' => 'Diskusi API Revisi',
                'kategori' => 'Privasi',
                'pertanyaan' => 'Pertanyaan via API sudah direvisi.',
            ])
            ->assertOk()
            ->assertJsonPath('forum.judul', 'Diskusi API Revisi');

        // 4. Pencarian /chat/users tidak membocorkan specialty_role milik akun privat & aman terhadap wildcard %
        $searchResp = $this->actingAs($ownerUser)
            ->getJson('/chat/users?q=warga_privat')
            ->assertOk();

        $foundPrivate = collect($searchResp->json('users'))->firstWhere('user.username', 'warga_privat');
        $this->assertNotNull($foundPrivate);
        $this->assertNull($foundPrivate['user']['specialty_role']);
        $this->assertTrue($foundPrivate['user']['is_private_profile']);

        // Pencarian karakter % murni tidak boleh mencocokkan semua user
        $wildcardResp = $this->actingAs($ownerUser)
            ->getJson('/chat/users?q=%25%25%25')
            ->assertOk();
        $this->assertCount(0, $wildcardResp->json('users'));

        // 5. Admin API summary menyertakan analytics pertumbuhan user & rating testimoni 5-1
        $adminToken = $this->postJson('/api/auth/login', [
            'email' => 'admin_analytics@purwakarta.id',
            'password' => 'oldsecret123',
        ])->assertOk()->json('token');

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/admin/summary')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'stats',
                'analytics' => [
                    'user_growth',
                    'testimoni_ratings',
                ],
            ]);

        // Suppress unused variable warnings
        $this->assertNotNull($privateTarget->id);
        $this->assertNotNull($adminUser->id);
    }
}
