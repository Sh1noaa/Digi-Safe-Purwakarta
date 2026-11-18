<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JawabanForum extends Model
{
    protected $table = 'jawaban_forum';

    // tabel jawaban_forum hanya memiliki kolom created_at tanpa updated_at
    public $timestamps = false;

    protected $fillable = [
        'forum_id',
        'user_id',
        'jawaban',
        'is_admin',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(JawabanForumVote::class, 'jawaban_id');
    }
}
