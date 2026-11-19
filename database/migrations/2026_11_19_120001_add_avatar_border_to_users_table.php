<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'avatar_border')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar_border', 40)->nullable()->after('banner');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar_border')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar_border');
            });
        }
    }
};
