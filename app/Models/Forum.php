<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Forum extends Model
{
    protected $table = 'forum';

    // tabel forum hanya memiliki kolom created_at tanpa updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'judul',
        'pertanyaan',
        'kategori',
        'status',
        'is_hidden',
        'is_starred',
        'created_at',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'is_starred' => 'boolean',
            'views' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanForum::class, 'forum_id')->orderBy('created_at', 'asc');
    }
}
