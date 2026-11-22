<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_streak_questions')) {
            Schema::create('daily_streak_questions', function (Blueprint $table) {
                $table->id();
                $table->string('question_key', 64)->unique();
                $table->string('category_id', 100);
                $table->string('category_en', 100)->nullable();
                $table->string('difficulty_id', 40)->default('Lanjutan');
                $table->string('difficulty_en', 40)->default('Advanced');
                $table->text('prompt_id');
                $table->text('prompt_en')->nullable();
                $table->text('options_id_json');
                $table->text('options_en_json')->nullable();
                $table->string('answer', 2);
                $table->text('explanation_id');
                $table->text('explanation_en')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_streak_questions');
    }
};
