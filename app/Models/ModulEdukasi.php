<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulEdukasi extends Model
{
    protected $table = 'modul_edukasi';

    public $timestamps = false;

    protected $fillable = [
        'judul',
        'konten',
        'link_url',
        'url',
        'type',
        'gambar_url',
        'kategori',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEffectiveTypeAttribute(): string
    {
        if (! empty($this->type)) {
            return $this->type;
        }

        return (! empty($this->url) || ! empty($this->link_url)) ? 'link' : 'teks';
    }

    public function getEffectiveUrlAttribute(): ?string
    {
        return $this->url ?: ($this->link_url ?: null);
    }
}
