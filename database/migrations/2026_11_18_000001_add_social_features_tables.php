<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('friendships');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'specialty_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('specialty_role');
            });
        }
    }
};

