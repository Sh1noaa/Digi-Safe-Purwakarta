<?php

namespace Tests\Feature;

use App\Mail\AccountOtpMail;
use App\Models\Forum;
use App\Models\SkorGame;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountOtpSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mail_relay.webhook_url' => null,
            'services.mail_relay.brevo_api_key' => null,
        ]);

        foreach ([
            'account_otps',
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
    }

    public function test_forgot_password_page_and_login_link_render(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Lupa Password? Reset via OTP Gmail')
            ->assertSee('/lupa-password');

        $this->get('/lupa-password')
            ->assertOk()
            ->assertSee('Reset Password Akun')
            ->assertSee('Kirim OTP ke Gmail');
    }

    public function test_user_can_reset_password_via_gmail_otp(): void
    {
        Mail::fake();

        $user = User::create([
            'username' => 'warga_reset',
            'email' => 'warga.reset@gmail.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Reset',
        ]);

        // 1. Send OTP to Gmail
        $sendRes = $this->postJson('/lupa-password/send-otp', [
            'email' => 'warga.reset@gmail.com',
        ]);

        $sendRes->assertOk()->assertJson(['ok' => true]);

        $capturedOtp = null;
        Mail::assertSent(AccountOtpMail::class, function (AccountOtpMail $mail) use (&$capturedOtp) {
            $capturedOtp = $mail->otpCode;

            return $mail->hasTo('warga.reset@gmail.com')
                && $mail->purpose === 'password_reset'
                && preg_match('/^\d{6}$/', $mail->otpCode) === 1;
        });

        $this->assertNotNull($capturedOtp);

        // 2. Resend cooldown prevents immediate spam
        $this->postJson('/lupa-password/send-otp', [
            'email' => 'warga.reset@gmail.com',
        ])->assertStatus(429)->assertJson(['ok' => false]);

        // 3. Wrong OTP fails
        $this->postJson('/lupa-password/reset', [
            'email' => 'warga.reset@gmail.com',
            'otp' => $capturedOtp === '000000' ? '111111' : '000000',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertStatus(422)->assertJson(['ok' => false]);

        // 4. Valid OTP resets password
        $this->postJson('/lupa-password/reset', [
            'email' => 'warga.reset@gmail.com',
            'otp' => $capturedOtp,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertOk()->assertJson(['ok' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('newsecret123', $user->password));

        // 5. Reusing same OTP is rejected
        $this->postJson('/lupa-password/reset', [
            'email' => 'warga.reset@gmail.com',
            'otp' => $capturedOtp,
            'password' => 'anothersecret123',
            'password_confirmation' => 'anothersecret123',
        ])->assertStatus(422)->assertJson(['ok' => false]);

        // 6. Once previous OTP is consumed, requesting a new OTP with the same Gmail succeeds immediately without cooldown block
        $this->postJson('/lupa-password/send-otp', [
            'email' => 'warga.reset@gmail.com',
        ])->assertOk()->assertJson(['ok' => true]);
    }

    public function test_authenticated_user_can_delete_account_via_gmail_otp(): void
    {
        Mail::fake();

        $user = User::create([
            'username' => 'warga_hapus',
            'email' => 'warga.hapus@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Hapus',
        ]);

        SkorGame::create([
            'user_id' => $user->id,
            'game_type' => 'phishing',
            'skor' => 80,
            'skor_tertinggi' => 80,
            'benar' => 8,
            'salah' => 2,
            'played_at' => now(),
        ]);

        Forum::create([
            'user_id' => $user->id,
            'judul' => 'Diskusi sementara',
            'kategori' => 'Privasi',
            'pertanyaan' => 'Akan ikut terhapus saat akun dihapus.',
            'created_at' => now(),
        ]);

        // 0. Must fail when password is missing or wrong BEFORE sending OTP
        $this->actingAs($user)
            ->postJson('/profile/delete-account/send-otp', [
                'current_password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        Mail::assertNothingSent();

        // 1. Send delete account OTP with valid account password
        $this->actingAs($user)
            ->postJson('/profile/delete-account/send-otp', [
                'current_password' => 'password123',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $capturedOtp = null;
        Mail::assertSent(AccountOtpMail::class, function (AccountOtpMail $mail) use (&$capturedOtp) {
            $capturedOtp = $mail->otpCode;

            return $mail->hasTo('warga.hapus@gmail.com')
                && $mail->purpose === 'delete_account'
                && preg_match('/^\d{6}$/', $mail->otpCode) === 1;
        });

        $this->assertNotNull($capturedOtp);

        // 2. Reject without confirmation checkbox
        $this->actingAs($user)
            ->deleteJson('/profile/delete-account', [
                'current_password' => 'password123',
                'otp' => $capturedOtp,
                'confirm_delete' => false,
            ])
            ->assertStatus(422);

        // 2b. Reject wrong password on final deletion endpoint (no bypass)
        $this->actingAs($user)
            ->deleteJson('/profile/delete-account', [
                'current_password' => 'wrong-password',
                'otp' => $capturedOtp,
                'confirm_delete' => true,
            ])
            ->assertStatus(422);

        // 3. Reject wrong OTP
        $this->actingAs($user)
            ->deleteJson('/profile/delete-account', [
                'current_password' => 'password123',
                'otp' => $capturedOtp === '999999' ? '888888' : '999999',
                'confirm_delete' => true,
            ])
            ->assertStatus(422);

        // 4. Valid password + valid OTP deletes user & related data and logs out
        $this->actingAs($user)
            ->deleteJson('/profile/delete-account', [
                'current_password' => 'password123',
                'otp' => $capturedOtp,
                'confirm_delete' => true,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertGuest();
        $this->assertNull(User::find($user->id));
        $this->assertSame(0, DB::table('skor_game')->where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('forum')->where('user_id', $user->id)->count());
    }

    public function test_owner_account_is_protected_from_self_deletion(): void
    {
        Mail::fake();

        $owner = User::create([
            'username' => 'owner_digisafe',
            'email' => 'owner@digisafe.id',
            'password' => Hash::make('ownerpass123'),
            'role' => 'owner',
            'nama_lengkap' => 'Owner Digi-Safe',
        ]);

        $this->actingAs($owner)
            ->postJson('/profile/delete-account/send-otp')
            ->assertStatus(403)
            ->assertJson(['ok' => false]);

        Mail::assertNothingSent();
        $this->assertNotNull(User::find($owner->id));
    }

    public function test_reset_password_rejects_non_gmail_and_other_users_email_when_logged_in(): void
    {
        Mail::fake();

        $nonGmailUser = User::create([
            'username' => 'warga_yahoo',
            'email' => 'warga@yahoo.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Yahoo',
        ]);

        $userA = User::create([
            'username' => 'warga_a',
            'email' => 'warga.a@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga A',
        ]);

        $userB = User::create([
            'username' => 'warga_b',
            'email' => 'warga.b@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga B',
        ]);

        // Non-Gmail email rejected
        $this->postJson('/lupa-password/send-otp', [
            'email' => $nonGmailUser->email,
        ])->assertStatus(422)->assertJson(['ok' => false]);

        // Logged-in user cannot request OTP for another user's Gmail
        $this->actingAs($userA)
            ->postJson('/lupa-password/send-otp', [
                'email' => $userB->email,
            ])
            ->assertStatus(403)
            ->assertJson(['ok' => false]);

        Mail::assertNothingSent();
    }

    public function test_otp_sends_via_https_port_443_webhook_relay_when_configured(): void
    {
        config([
            'services.mail_relay.webhook_url' => 'https://script.google.com/macros/s/fake-deployment-id/exec',
            'services.mail_relay.webhook_secret' => 'secret-token-123',
        ]);

        Http::fake([
            'https://script.google.com/*' => Http::response(['ok' => true], 200),
        ]);

        $user = User::create([
            'username' => 'warga_https',
            'email' => 'warga.https@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga HTTPS',
        ]);

        $this->postJson('/lupa-password/send-otp', [
            'email' => 'warga.https@gmail.com',
        ])->assertOk()->assertJson(['ok' => true]);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://script.google.com/')
                && $request['to'] === 'warga.https@gmail.com'
                && $request['secret'] === 'secret-token-123'
                && str_contains((string) $request['html'], 'Digi-Safe Purwakarta');
        });
    }

    public function test_otp_resend_allows_earlier_active_code_or_newest_code_and_consumes_all(): void
    {
        Mail::fake();

        $user = User::create([
            'username' => 'warga_resend',
            'email' => 'warga.resend@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Resend',
        ]);

        // 1. First OTP
        $this->postJson('/lupa-password/send-otp', ['email' => $user->email])->assertOk();
        $code1 = null;
        Mail::assertSent(AccountOtpMail::class, function ($mail) use (&$code1) {
            $code1 = $mail->otpCode;

            return true;
        });

        // Fast forward 25 seconds past cooldown
        DB::table('account_otps')->where('email', $user->email)->update([
            'created_at' => now()->subSeconds(25),
        ]);

        // 2. Resend OTP
        $this->postJson('/lupa-password/send-otp', ['email' => $user->email])->assertOk();
        $code2 = null;
        Mail::assertSent(AccountOtpMail::class, function ($mail) use (&$code2, $code1) {
            if ($mail->otpCode !== $code1) {
                $code2 = $mail->otpCode;
            }

            return true;
        });

        $this->assertNotNull($code1);
        $this->assertNotNull($code2);
        $this->assertNotSame($code1, $code2);

        // 3. User submits earlier code ($code1) that arrived first in their inbox -> Accepted!
        $this->postJson('/lupa-password/reset', [
            'email' => $user->email,
            'otp' => $code1,
            'password' => 'brandnewpass123',
            'password_confirmation' => 'brandnewpass123',
        ])->assertOk()->assertJson(['ok' => true]);

        // 4. All active OTPs are now consumed, neither code1 nor code2 can be used again
        $this->postJson('/lupa-password/reset', [
            'email' => $user->email,
            'otp' => $code2,
            'password' => 'anotherpass123',
            'password_confirmation' => 'anotherpass123',
        ])->assertStatus(422);

        $this->assertSame(0, DB::table('account_otps')->where('email', $user->email)->whereNull('consumed_at')->count());
    }

    public function test_auth_language_toggle_and_otp_email_locale_and_brand_logo(): void
    {
        Mail::fake();

        $user = User::create([
            'username' => 'warga_bilingual',
            'email' => 'warga.bilingual@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Bilingual',
        ]);

        // Switch session locale to EN via quick preferences
        $this->post('/preferences/quick', ['locale' => 'en'])->assertRedirect();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign In Now')
            ->assertSee('Forgot Password? Reset via Gmail OTP');

        $this->get('/register')
            ->assertOk()
            ->assertSee('Create Your Digi-Safe Account');

        // Send OTP while locale is EN -> email must be English with brand logo & no blue header banner
        $this->postJson('/lupa-password/send-otp', [
            'email' => $user->email,
            'locale' => 'en',
        ])->assertOk()->assertJson(['ok' => true]);

        Mail::assertSent(AccountOtpMail::class, function (AccountOtpMail $mail) {
            $html = $mail->render();

            return $mail->localeCode === 'en'
                && $mail->envelope()->subject === 'Password Reset Code - Digi-Safe Purwakarta'
                && str_contains($html, 'Reset your password')
                && str_contains($html, '/images/logo.png')
                && ! str_contains($html, 'background-color:#1E63E9');
        });
    }

    public function test_security_hardening_reserved_username_oauth_fail_closed_headers_and_privacy_merge(): void
    {
        // 1. Sensitive auth pages must have no-store Cache-Control and COOP header
        $loginRes = $this->get('/login')->assertOk();
        $this->assertStringContainsString('no-store', (string) $loginRes->headers->get('Cache-Control'));
        $this->assertSame('same-origin-allow-popups', $loginRes->headers->get('Cross-Origin-Opener-Policy'));

        // 2. Reserved usernames are rejected on API register
        $this->postJson('/api/auth/register', [
            'nama_lengkap' => 'Fake Admin',
            'username' => 'Admin',
            'email' => 'fakeadmin@gmail.com',
            'password' => 'strongpass123',
        ])->assertStatus(422)->assertJsonValidationErrors(['username']);

        // 3. Google API token login fails closed when GOOGLE_CLIENT_ID is empty
        config(['services.google.client_id' => '']);
        $this->postJson('/api/auth/google', [
            'id_token' => 'arbitrary-third-party-token',
        ])->assertStatus(503);

        // 4. Saving profile privacy settings preserves google_onboarded & has_local_password flags
        $googleUser = User::create([
            'username' => 'warga_google_test',
            'email' => 'warga.google.test@gmail.com',
            'password' => Hash::make('secretpass123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Google',
            'oauth_provider' => 'google',
            'oauth_uid' => 'google-uid-123',
            'privacy_settings' => [
                'google_onboarded' => true,
                'has_local_password' => true,
            ],
        ]);

        $this->actingAs($googleUser)
            ->put('/profile/settings', [
                'locale' => 'en',
                'theme' => 'dark',
                'hide_email' => '1',
            ])
            ->assertRedirect(route('profile.edit'));

        $googleUser->refresh();
        $this->assertTrue((bool) data_get($googleUser->privacy_settings, 'google_onboarded'));
        $this->assertTrue((bool) data_get($googleUser->privacy_settings, 'has_local_password'));
        $this->assertTrue((bool) data_get($googleUser->privacy_settings, 'hide_email'));
    }
}
