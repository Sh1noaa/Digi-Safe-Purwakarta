<?php

namespace Tests\Feature;

use App\Models\DailyStreakQuestion;
use App\Models\User;
use App\Services\DailyStreakService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStreakQuestionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'daily_streak_questions',
            'user_daily_streak_logs',
            'user_daily_streaks',
            'edukasi_requests',
            'modul_edukasi',
            'forum',
            'testimoni',
            'skor_game',
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
            $table->string('locale', 10)->default('id');
            $table->string('theme', 20)->default('light');
            $table->json('privacy_settings')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('testimoni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('pesan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    private function makeUser(string $role = 'admin'): User
    {
        return User::create([
            'username' => $role . '_' . uniqid(),
            'email' => $role . '_' . uniqid() . '@gmail.com',
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'nama_lengkap' => ucfirst($role) . ' Tester',
        ]);
    }

    public function test_admin_can_view_and_auto_seed_streak_questions(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.streak.index'))
            ->assertOk()
            ->assertSee('Bank Soal Tantangan Harian (Daily Streak)')
            ->assertSee('Forensik Malware Android');

        $this->assertGreaterThanOrEqual(12, DailyStreakQuestion::count());
    }

    public function test_non_admin_cannot_access_streak_question_management(): void
    {
        $user = $this->makeUser('user');

        $this->actingAs($user)
            ->get(route('admin.streak.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_toggle_and_delete_streak_question(): void
    {
        $admin = $this->makeUser('admin');
        DailyStreakQuestion::ensureInitialized();

        $this->actingAs($admin)
            ->post(route('admin.streak.store'), [
                'category_id' => 'Keamanan DNS',
                'difficulty_id' => 'Pakar',
                'prompt_id' => 'Apa fungsi utama DNSSEC pada domain pemerintahan?',
                'option_a' => 'Mengenkripsi seluruh isi halaman HTML',
                'option_b' => 'Memvalidasi keaslian catatan DNS dengan tanda tangan kriptografis untuk mencegah DNS spoofing',
                'option_c' => 'Mempercepat kecepatan download video',
                'option_d' => 'Menghapus kebutuhan sertifikat TLS/HTTPS',
                'option_e' => 'Memblokir semua trafik dari perangkat mobile',
                'answer' => 'B',
                'explanation_id' => 'DNSSEC menambahkan tanda tangan digital pada DNS record sehingga resolver dapat memverifikasi keaslian respons DNS.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.streak.index'));

        $created = DailyStreakQuestion::where('category_id', 'Keamanan DNS')->first();
        $this->assertNotNull($created);
        $this->assertSame('B', $created->answer);
        $this->assertTrue($created->is_active);

        // Pastikan soal baru langsung tersedia di DailyStreakService
        $bank = DailyStreakService::advancedQuestionsBank(false);
        $this->assertTrue(collect($bank)->contains('key', $created->question_key));

        // Update soal
        $this->actingAs($admin)
            ->put(route('admin.streak.update', $created), [
                'category_id' => 'Keamanan DNS Lanjutan',
                'difficulty_id' => 'Pakar',
                'prompt_id' => 'Apa fungsi utama DNSSEC pada domain pemerintahan (revisi)?',
                'option_a' => 'Opsi A',
                'option_b' => 'Opsi B',
                'option_c' => 'Opsi C benar',
                'option_d' => 'Opsi D',
                'option_e' => 'Opsi E',
                'answer' => 'C',
                'explanation_id' => 'Penjelasan revisi DNSSEC.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.streak.index'));

        $created->refresh();
        $this->assertSame('Keamanan DNS Lanjutan', $created->category_id);
        $this->assertSame('C', $created->answer);

        // Toggle nonaktif
        $this->actingAs($admin)
            ->patch(route('admin.streak.toggle', $created))
            ->assertRedirect(route('admin.streak.index'));

        $created->refresh();
        $this->assertFalse($created->is_active);
        $bankAfterToggle = DailyStreakService::advancedQuestionsBank(false);
        $this->assertFalse(collect($bankAfterToggle)->contains('key', $created->question_key));

        // Hapus soal
        $this->actingAs($admin)
            ->delete(route('admin.streak.destroy', $created))
            ->assertRedirect(route('admin.streak.index'));

        $this->assertDatabaseMissing('daily_streak_questions', ['id' => $created->id]);
    }
}
