<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimoni extends Model
{
    protected $table = 'testimoni';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'judul',
        'cerita',
        'rating',
        'mood',
        'is_anonymous',
        'status_approval',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_anonymous' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNamaTampilAttribute(): string
    {
        if ($this->is_anonymous) {
            return app()->getLocale() === 'en' ? 'Anonymous User' : 'Pengguna Anonim';
        }

        return $this->user?->nama_lengkap ?: ($this->user?->username ?: __('ui.testi.citizen_fallback'));
    }

    public function getInisialTampilAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($this->nama_tampil), 0, 1));
    }

    public function getAvatarTampilAttribute(): ?string
    {
        if ($this->is_anonymous) {
            return null;
        }

        return $this->user?->avatar_url;
    }
}
