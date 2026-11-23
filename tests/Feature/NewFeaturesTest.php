<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DailyStreakService;
use App\Services\SecurityActivityService;
use App\Services\WaScamSimulatorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'user_daily_streak_logs',
            'user_daily_streaks',
            'security_activity_logs',
            'user_badges',
            'badges',
            'modul_edukasi',
            'jawaban_forum',
            'forum',
            'skor_game',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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

        Schema::create('modul_edukasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->default('Anti-Phishing');
            $table->string('type', 20)->default('teks');
            $table->text('konten')->nullable();
            $table->string('url')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });

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
    }

    public function test_pwa_assets_and_transparent_streak_icons_exist(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('images/pwa-icon-192.png'));
        $this->assertFileExists(public_path('images/pwa-icon-512.png'));
        $this->assertFileExists(public_path('images/streak-fire.png'));
        $this->assertFileExists(public_path('images/streak-freeze.png'));
        $this->assertFileExists(public_path('images/thumb-link-scanner.jpg'));
        $this->assertFileExists(public_path('images/thumb-wa-simulator.jpg'));

        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertIsArray($manifest);
        $this->assertSame('standalone', $manifest['display']);
    }

    public function test_wa_apk_simulator_renders_many_contacts_with_branching_endings(): void
    {
        $contactsId = WaScamSimulatorService::contacts(false);
        $contactsEn = WaScamSimulatorService::contacts(true);

        $this->assertGreaterThanOrEqual(15, count($contactsId));
        $this->assertSame(count($contactsId), count($contactsEn));

        foreach ($contactsId as $contact) {
            $this->assertArrayHasKey('start', $contact['nodes']);
            $this->assertGreaterThanOrEqual(3, count($contact['nodes']), "Contact {$contact['id']} should have at least 3 conversation turns");
            $this->assertGreaterThanOrEqual(2, count($contact['nodes']['start']['choices']));
            $this->assertGreaterThanOrEqual(2, count($contact['endings']));
        }

        // Verify English translation works
        $this->assertStringContainsString('Parcel Courier', $contactsEn[0]['name']);

        $this->get('/minigame/wa-simulator')
            ->assertOk()
            ->assertSee('Simulasi Waspada Penipuan WhatsApp')
            ->assertSee('Lihat_Foto_Resi_JNE.apk');
    }

    public function test_link_and_qr_phishing_scanner_detects_malicious_urls_and_safe_gov_domains(): void
    {
        $this->get('/minigame/link-scanner')
            ->assertOk()
            ->assertSee('Link &amp; QR Phishing Scanner', false);

        // 1. Malicious brand impersonation + .apk download + high-risk TLD
        $dangerRes = $this->postJson('/minigame/link-scanner/scan', [
            'url' => 'http://bri-tarif-baru2026.xyz/Formulir-Pembatalan.apk',
            'source' => 'url',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('dangerous', $dangerRes->json('verdict'));
        $this->assertGreaterThanOrEqual(70, $dangerRes->json('risk_score'));

        // 2. Official Purwakarta government domain over HTTPS
        $safeRes = $this->postJson('/minigame/link-scanner/scan', [
            'url' => 'https://purwakartakab.go.id/berita/keamanan-siber',
            'source' => 'url',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('safe', $safeRes->json('verdict'));
        $this->assertLessThan(25, $safeRes->json('risk_score'));
    }

    public function test_user_can_select_solid_or_gradient_default_banner_color_preset(): void
    {
        $user = User::create([
            'username' => 'warga_banner',
            'email' => 'warga.banner@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Banner',
        ]);

        // 1. Select a solid preset
        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Warga Banner',
                'username' => 'warga_banner',
                'banner_preset' => 'solid-emerald',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('solid-emerald', $user->banner_preset_key);
        $this->assertSame('#059669', $user->banner_style_css);

        // 2. Select a custom hex solid color
        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Warga Banner',
                'username' => 'warga_banner',
                'banner_preset' => 'solid-custom:#7C3AED',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('solid-custom:#7C3AED', $user->banner_preset_key);
        $this->assertSame('#7C3AED', $user->banner_style_css);

        // 3. Select a gradient preset
        $this->actingAs($user)
            ->put('/profile', [
                'nama_lengkap' => 'Warga Banner',
                'username' => 'warga_banner',
                'banner_preset' => 'grad-sunset',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('grad-sunset', $user->banner_preset_key);
        $this->assertStringContainsString('linear-gradient', $user->banner_style_css);
    }

    public function test_edukasi_page_has_text_size_controls_and_reading_mode(): void
    {
        $this->get('/edukasi')
            ->assertOk()
            ->assertSee('Ramah Baca')
            ->assertSee('setFontStep(fontStep + 1)', false)
            ->assertSee('toggleReadingMode()', false);
    }

    public function test_security_activity_log_records_login_and_device_label(): void
    {
        $user = User::create([
            'username' => 'warga_log',
            'email' => 'warga.log@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Log',
        ]);

        $this->post('/login', [
            'email' => 'warga.log@gmail.com',
            'password' => 'password123',
        ])->assertRedirect(route('beranda'));

        $logs = SecurityActivityService::recentForUser($user, 5);
        $this->assertCount(1, $logs);
        $this->assertSame(SecurityActivityService::EVENT_LOGIN, $logs[0]['event']);
    }

    public function test_daily_streak_advanced_abcde_and_24h_freeze_with_2x_recovery(): void
    {
        $user = User::create([
            'username' => 'warga_streak',
            'email' => 'warga.streak@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Streak',
        ]);

        // 1. Advanced question must have 5 options (A-E)
        $initial = DailyStreakService::getStatusForUser($user, 'id');
        $this->assertSame(['A', 'B', 'C', 'D', 'E'], array_keys($initial['question']['options']));
        $this->assertSame(2, $initial['freeze_recovery_left']);

        // Find correct option for today's question from bank
        $bank = DailyStreakService::advancedQuestionsBank(false);
        $todayQ = collect($bank)->firstWhere('key', $initial['question']['key']);
        $this->assertNotNull($todayQ);

        // 2. Answer correctly -> streak becomes 1 (active)
        $res1 = $this->actingAs($user)
            ->postJson('/streak/answer', ['option' => $todayQ['answer']])
            ->assertOk()
            ->assertJson(['ok' => true, 'correct' => true]);

        $this->assertSame(1, $res1->json('streak.current_streak'));
        $this->assertSame('active', $res1->json('streak.status'));
        $this->assertSame(2, $res1->json('streak.freeze_recovery_left'));

        // 3. Simulate 26 hours missed (over 24h) -> status becomes FROZEN with 2x recovery left
        DB::table('user_daily_streaks')->where('user_id', $user->id)->update([
            'last_completed_at' => now()->subHours(26),
            'last_completed_date' => now('Asia/Jakarta')->subDays(2)->format('Y-m-d'),
        ]);

        $frozenState = DailyStreakService::getStatusForUser($user, 'id');
        $this->assertTrue($frozenState['is_frozen']);
        $this->assertSame('frozen', $frozenState['status']);
        $this->assertSame(2, $frozenState['freeze_recovery_left']);

        // 4. Recovery #1: Answer correctly -> restores streak to 2, active again, recovery left = 1
        $qForRecovery = collect($bank)->firstWhere('key', $frozenState['question']['key']);
        $resRec = $this->actingAs($user)
            ->postJson('/streak/answer', ['option' => $qForRecovery['answer']])
            ->assertOk()
            ->assertJson(['ok' => true, 'correct' => true, 'recovered' => true]);

        $this->assertSame(2, $resRec->json('streak.current_streak'));
        $this->assertSame('active', $resRec->json('streak.status'));
        $this->assertSame(1, $resRec->json('streak.freeze_recovery_left'));

        // 5. Simulate using up both recoveries (0 left) and missing 26 hours again -> streak resets (broken)
        DB::table('user_daily_streaks')->where('user_id', $user->id)->update([
            'freeze_recovery_left' => 0,
            'last_completed_at' => now()->subHours(26),
            'last_completed_date' => now('Asia/Jakarta')->subDays(2)->format('Y-m-d'),
        ]);

        $brokenState = DailyStreakService::getStatusForUser($user, 'id');
        $this->assertSame(0, $brokenState['current_streak']);
        $this->assertSame('broken', $brokenState['status']);
        $this->assertSame(2, $brokenState['freeze_recovery_left']);
    }

    public function test_leaderboard_includes_streak_and_wa_accuracy_without_minigame_salah(): void
    {
        $u1 = User::create([
            'username' => 'streak_master',
            'email' => 'streak.master@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Streak Master',
        ]);

        // Ensure streak table exists & insert streak record
        DailyStreakService::ensureTables();
        DB::table('user_daily_streaks')->insert([
            'user_id' => $u1->id,
            'current_streak' => 7,
            'longest_streak' => 12,
            'freeze_recovery_left' => 2,
            'total_completed' => 15,
            'last_completed_date' => now('Asia/Jakarta')->format('Y-m-d'),
            'last_completed_at' => now(),
        ]);

        // Record WA simulator results (2 safe, 1 danger => 67% accuracy)
        $this->actingAs($u1)->postJson('/minigame/wa-simulator/record', [
            'contact_id' => 'kurir_apk',
            'ending_type' => 'safe',
            'score' => 100,
        ])->assertOk();

        $this->actingAs($u1)->postJson('/minigame/wa-simulator/record', [
            'contact_id' => 'tilang_etle',
            'ending_type' => 'safe',
            'score' => 100,
        ])->assertOk();

        $this->actingAs($u1)->postJson('/minigame/wa-simulator/record', [
            'contact_id' => 'cs_bank_palsu',
            'ending_type' => 'danger',
            'score' => 0,
        ])->assertOk();

        $board = \App\Services\LeaderboardService::allCategories(10);
        $keys = collect($board['categories'])->pluck('key')->all();

        $this->assertContains('streak', $keys);
        $this->assertContains('wa_accuracy', $keys);
        $this->assertNotContains('minigame_salah', $keys);

        $streakCat = collect($board['categories'])->firstWhere('key', 'streak');
        $this->assertSame('12 Hari', $streakCat['rows'][0]['metric_main']);

        $waCat = collect($board['categories'])->firstWhere('key', 'wa_accuracy');
        $this->assertSame('67%', $waCat['rows'][0]['metric_main']);

        // Verify streak widget has burn & freeze animations and no "Tes Animasi Api" button
        $widget = (string) file_get_contents(resource_path('views/components/daily-streak-widget.blade.php'));
        $this->assertStringContainsString('streakFlameBurn', $widget);
        $this->assertStringContainsString('streakFreezeShiver', $widget);
        $this->assertStringNotContainsString('Tes Animasi Api', $widget);
    }

    public function test_tentang_page_renders_verified_cyber_facts_section_before_developer_in_id_and_en(): void
    {
        // 1. Indonesian default
        $idResponse = $this->get('/tentang');
        $idResponse->assertOk()
            ->assertSee('id="section-fakta-siber"', false)
            ->assertSee('741.545+')
            ->assertSee('5,2 Miliar+')
            ->assertSee('80,66%')
            ->assertSee('images/logo.png')
            ->assertDontSee('Get Started');

        $htmlId = $idResponse->getContent();
        $this->assertLessThan(
            strpos($htmlId, 'id="section-pengembang"'),
            strpos($htmlId, 'id="section-fakta-siber"'),
            'Cyber facts section must appear before developer section'
        );

        // 2. English + Dark mode
        $user = User::create([
            'username' => 'warga_en_dark',
            'email' => 'warga.endark@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga EN Dark',
            'locale' => 'en',
            'theme' => 'dark',
        ]);

        $this->actingAs($user)
            ->get('/tentang')
            ->assertOk()
            ->assertSee('Verified Field Data')
            ->assertSee('741,545+')
            ->assertSee('5.2 Billion+')
            ->assertSee('80.66%')
            ->assertSee('Spotting')
            ->assertSee('Scam Defense');
    }

    public function test_panic_simulator_renders_interactive_android_os_motion_toggle_and_records_score(): void
    {
        // 1. Indonesian default view (3 cases + drag shade + beginner guide + motion toggle)
        $this->get('/minigame/panic-simulator')
            ->assertOk()
            ->assertSee('60 Detik Selamatkan HP-mu!')
            ->assertSee('Resi_JNE_Viewer')
            ->assertSee('Update Layanan Sistem')
            ->assertSee('Perangkat Tertaut')
            ->assertSee('wa_hijack', false)
            ->assertSee('phishing_bank', false)
            ->assertSee('onStatusBarPointerDown', false)
            ->assertSee('Panduan Pemula', false)
            ->assertSee('1500017')
            ->assertSee('panic-reduced-motion', false)
            ->assertSee('toggleMotion()', false);

        // 2. Minigame Hub links to panic simulator
        $this->get('/minigame')
            ->assertOk()
            ->assertSee(route('minigame.panic-simulator'), false)
            ->assertSee('60 Detik Selamatkan HP-mu!');

        // 3. Authenticated English user + score persistence to skor_game
        $user = User::create([
            'username' => 'warga_panic',
            'email' => 'warga.panic@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'nama_lengkap' => 'Warga Panic',
            'locale' => 'en',
            'theme' => 'dark',
        ]);

        $this->actingAs($user)
            ->get('/minigame/panic-simulator')
            ->assertOk()
            ->assertSee('60 Seconds to Save Your Phone')
            ->assertSee('Linked Devices')
            ->assertSee('Airplane Mode');

        $this->actingAs($user)
            ->postJson('/minigame/panic-simulator/record', [
                'mode' => 'extreme',
                'won' => true,
                'score' => 94,
                'saved_balance' => 4400000,
                'elapsed_seconds' => 28,
                'steps_completed' => 5,
                'mistakes' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'saved' => true,
            ]);

        $record = DB::table('skor_game')
            ->where('user_id', $user->id)
            ->where('game_type', 'panic-simulator')
            ->first();

        $this->assertNotNull($record);
        $this->assertSame(94, (int) $record->skor);
        $this->assertSame(94, (int) $record->skor_tertinggi);
        $this->assertSame(5, (int) $record->benar);
        $this->assertSame(1, (int) $record->salah);
    }
}
