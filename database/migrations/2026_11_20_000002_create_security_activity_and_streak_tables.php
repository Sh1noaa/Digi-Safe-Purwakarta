<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('security_activity_logs')) {
            Schema::create('security_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('event', 48)->index();
                $table->string('ip_address', 64)->nullable();
                $table->string('device_label', 120)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        if (! Schema::hasTable('user_daily_streaks')) {
            Schema::create('user_daily_streaks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedInteger('current_streak')->default(0);
                $table->unsignedInteger('longest_streak')->default(0);
                $table->unsignedTinyInteger('freeze_recovery_left')->default(2);
                $table->unsignedInteger('total_completed')->default(0);
                $table->string('status', 20)->default('idle');
                $table->timestamp('last_completed_at')->nullable();
                $table->string('last_completed_date', 10)->nullable();
                $table->text('weekly_days_json')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_daily_streak_logs')) {
            Schema::create('user_daily_streak_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('challenge_date', 10)->index();
                $table->string('question_key', 64);
                $table->string('selected_option', 2);
                $table->boolean('is_correct')->default(false);
                $table->boolean('used_freeze_recovery')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'challenge_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_streak_logs');
        Schema::dropIfExists('user_daily_streaks');
        Schema::dropIfExists('security_activity_logs');
    }
};
