<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tambah kolom is_starred pada tabel forum
        if (Schema::hasTable('forum') && ! Schema::hasColumn('forum', 'is_starred')) {
            Schema::table('forum', function (Blueprint $table) {
                $table->boolean('is_starred')->default(false)->after('is_hidden');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('forum') && Schema::hasColumn('forum', 'is_starred')) {
            Schema::table('forum', function (Blueprint $table) {
                $table->dropColumn('is_starred');
            });
        }
    }
};
