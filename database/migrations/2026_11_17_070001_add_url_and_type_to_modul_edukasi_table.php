<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ubah enum role pada tabel users agar mendukung owner, admin, dan user
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'user') DEFAULT 'user'");
        }

        // tambah kolom url dan type pada tabel modul_edukasi
        if (Schema::hasTable('modul_edukasi')) {
            Schema::table('modul_edukasi', function (Blueprint $table) {
                if (! Schema::hasColumn('modul_edukasi', 'type')) {
                    $table->string('type', 30)->default('link')->after('kategori');
                }
                if (! Schema::hasColumn('modul_edukasi', 'url')) {
                    $table->string('url', 500)->nullable()->after('type');
                }
            });
        }

        // perluas kolom tingkat pada soal_minigame agar mendukung easy, medium, hard
        if (Schema::hasTable('soal_minigame')) {
            DB::statement("ALTER TABLE soal_minigame MODIFY COLUMN tingkat VARCHAR(30) DEFAULT 'easy'");
            DB::statement("ALTER TABLE soal_minigame MODIFY COLUMN game_level VARCHAR(50) DEFAULT 'phishing'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('modul_edukasi')) {
            Schema::table('modul_edukasi', function (Blueprint $table) {
                if (Schema::hasColumn('modul_edukasi', 'url')) {
                    $table->dropColumn('url');
                }
                if (Schema::hasColumn('modul_edukasi', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
};
