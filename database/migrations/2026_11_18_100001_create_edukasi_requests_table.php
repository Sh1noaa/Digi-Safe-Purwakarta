<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('edukasi_requests')) {
            Schema::create('edukasi_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('tema', 180);
                $table->string('kategori', 80)->default('Keamanan Akun');
                $table->string('preferred_type', 20)->default('teks');
                $table->text('deskripsi');
                $table->string('status', 20)->default('pending')->index();
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->boolean('user_notified_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edukasi_requests');
    }
};
