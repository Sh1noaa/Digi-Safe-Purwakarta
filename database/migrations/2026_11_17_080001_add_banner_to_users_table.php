<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tambah kolom banner pada tabel users
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'banner')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('banner', 255)->nullable()->after('avatar');
            });
        }

        // setujui ulasan testimoni yang masih pending agar langsung tampil
        if (Schema::hasTable('testimoni')) {
            DB::table('testimoni')
                ->where('status_approval', 'pending')
                ->update(['status_approval' => 'approved']);
        }

        // siapkan daftar achievement badge minigame di tabel badges
        if (Schema::hasTable('badges')) {
            $achievements = [
                [
                    'nama_badge' => 'Speed Runner (<10d)',
                    'deskripsi' => 'Menyelesaikan tantangan minigame dengan rata-rata waktu di bawah 10 detik.',
                    'icon' => 'bolt',
                    'kriteria_skor' => 50,
                ],
                [
                    'nama_badge' => 'Tanpa Salah (Flawless)',
                    'deskripsi' => 'Menyelesaikan satu sesi permainan penuh tanpa jawaban salah sedikit pun.',
                    'icon' => 'check',
                    'kriteria_skor' => 60,
                ],
                [
                    'nama_badge' => 'Phishing Hunter',
                    'deskripsi' => 'Menang dalam simulasi Phishing Hunter dengan skor minimal 60 poin.',
                    'icon' => 'shield',
                    'kriteria_skor' => 60,
                ],
                [
                    'nama_badge' => 'Cyber Defender',
                    'deskripsi' => 'Berhasil mempertahankan sistem di Cyber Defender dengan skor minimal 60 poin.',
                    'icon' => 'defender',
                    'kriteria_skor' => 60,
                ],
                [
                    'nama_badge' => 'Link Inspector',
                    'deskripsi' => 'Menang dalam inspeksi tautan Link or Trap dengan skor minimal 60 poin.',
                    'icon' => 'link',
                    'kriteria_skor' => 60,
                ],
                [
                    'nama_badge' => 'Master Siber',
                    'deskripsi' => 'Mencapai skor tinggi 100+ poin dalam satu sesi permainan.',
                    'icon' => 'star',
                    'kriteria_skor' => 100,
                ],
            ];

            foreach ($achievements as $ach) {
                $exists = DB::table('badges')->where('nama_badge', $ach['nama_badge'])->exists();
                if (! $exists) {
                    DB::table('badges')->insert($ach);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'banner')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('banner');
            });
        }
    }
};
