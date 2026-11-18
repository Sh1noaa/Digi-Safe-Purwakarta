<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class JawabanForumVote extends Model
{
    protected $table = 'jawaban_forum_votes';

    protected $fillable = [
        'jawaban_id',
        'user_id',
        'vote_type',
    ];

    public function jawaban(): BelongsTo
    {
        return $this->belongsTo(JawabanForum::class, 'jawaban_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Pastikan tabel jawaban_forum_votes tersedia dengan unique constraint (1 user = 1 vote per pesan).
     */
    public static function ensureTable(): void
    {
        try {
            if (! Schema::hasTable('jawaban_forum_votes')) {
                Schema::create('jawaban_forum_votes', function (Blueprint $table) {
                    $table->id();
                    $table->integer('jawaban_id')->index();
                    $table->integer('user_id')->index();
                    $table->enum('vote_type', ['up', 'down']);
                    $table->timestamps();

                    $table->unique(['jawaban_id', 'user_id'], 'jawaban_vote_unique');
                });
            }
        } catch (\Throwable $e) {
            // Abaikan jika DB belum siap
        }
    }
}

