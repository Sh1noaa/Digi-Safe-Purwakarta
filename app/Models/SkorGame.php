<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkorGame extends Model
{
    protected $table = 'skor_game';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'game_type',
        'skor',
        'skor_tertinggi',
        'benar',
        'salah',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
            'skor_tertinggi' => 'integer',
            'benar' => 'integer',
            'salah' => 'integer',
            'played_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNamaGameAttribute(): string
    {
        return match ($this->game_type) {
            'phishing', 'level1' => 'Phishing Hunter',
            'cyber-defender', 'level2' => 'Cyber Defender',
            'link-trap', 'level3' => 'Link or Trap?',
            default => ucfirst((string) $this->game_type),
        };
    }

    public function getKategoriBadgeAttribute(): string
    {
        $isEn = app()->getLocale() === 'en';
        return match ($this->game_type) {
            'phishing', 'level1' => $isEn ? 'MESSAGE ANALYSIS' : 'ANALISIS PESAN',
            'cyber-defender', 'level2' => $isEn ? 'SYSTEM DEFENSE' : 'PERTAHANAN SISTEM',
            'link-trap', 'level3' => $isEn ? 'URL INSPECTION' : 'INSPEKSI URL',
            default => 'MINIGAME',
        };
    }
}
