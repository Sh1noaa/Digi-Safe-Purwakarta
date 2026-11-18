<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SocialSchemaHelper
{
    private static bool $ensured = false;

    /**
     * Memastikan tabel pertemanan, pesan chat, dan kolom specialty_role tersedia.
     */
    public static function ensureReady(): void
    {
        if (self::$ensured && Schema::hasTable('edukasi_requests') && Schema::hasColumn('users', 'specialty_role')) {
            return;
        }
        self::$ensured = true;

        try {
            if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'specialty_role')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('specialty_role', 80)->nullable();
                });
            }

            if (! Schema::hasTable('friendships')) {
                Schema::create('friendships', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('requester_id');
                    $table->unsignedBigInteger('addressee_id');
                    $table->string('status', 20)->default('pending');
                    $table->timestamps();

                    $table->unique(['requester_id', 'addressee_id']);
                    $table->index(['addressee_id', 'status']);
                    $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
                    $table->foreign('addressee_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }

            if (! Schema::hasTable('chat_messages')) {
                Schema::create('chat_messages', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('sender_id');
                    $table->unsignedBigInteger('receiver_id');
                    $table->text('message');
                    $table->boolean('is_read')->default(false);
                    $table->timestamps();

                    $table->index(['sender_id', 'receiver_id']);
                    $table->index(['receiver_id', 'is_read']);
                    $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
                    $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }

            if (Schema::hasTable('skor_game')) {
                Schema::table('skor_game', function (Blueprint $table) {
                    if (! Schema::hasColumn('skor_game', 'benar')) {
                        $table->unsignedInteger('benar')->default(0);
                    }
                    if (! Schema::hasColumn('skor_game', 'salah')) {
                        $table->unsignedInteger('salah')->default(0);
                    }
                });
            }

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
        } catch (\Throwable $e) {
            // Abaikan bila koneksi DB belum siap
        }
    }
}

