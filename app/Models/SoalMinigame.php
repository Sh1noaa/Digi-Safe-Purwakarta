<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalMinigame extends Model
{
    protected $table = 'soal_minigame';

    public $timestamps = false;

    protected $fillable = [
        'pertanyaan',
        'pilihan_json',
        'jawaban_benar',
        'kategori',
        'tingkat',
        'game_level',
    ];

    // ekstrak opsi A, B, C, D dari json baik format associative maupun indexed array
    public function getOpsiAttribute(): array
    {
        $raw = json_decode((string) $this->pilihan_json, true);

        if (! is_array($raw)) {
            return ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
        }

        if (isset($raw['A']) || isset($raw['B'])) {
            return [
                'A' => (string) ($raw['A'] ?? ''),
                'B' => (string) ($raw['B'] ?? ''),
                'C' => (string) ($raw['C'] ?? ''),
                'D' => (string) ($raw['D'] ?? ''),
            ];
        }

        $values = array_values($raw);
        return [
            'A' => (string) ($values[0] ?? ''),
            'B' => (string) ($values[1] ?? ''),
            'C' => (string) ($values[2] ?? ''),
            'D' => (string) ($values[3] ?? ''),
        ];
    }

    // normalisasi kunci jawaban menjadi A, B, C, atau D
    public function getKunciHurufAttribute(): string
    {
        $ans = strtoupper(trim((string) $this->jawaban_benar));
        if (in_array($ans, ['A', 'B', 'C', 'D'], true)) {
            return $ans;
        }

        foreach ($this->opsi as $key => $val) {
            if (mb_strtolower(trim($val)) === mb_strtolower(trim((string) $this->jawaban_benar))) {
                return $key;
            }
        }

        return 'A';
    }

    // normalisasi slug game
    public function getGameSlugAttribute(): string
    {
        $g = strtolower((string) ($this->game_level ?: $this->kategori));
        if (str_contains($g, 'defender') || $g === 'level2') {
            return 'cyber-defender';
        }
        if (str_contains($g, 'link') || str_contains($g, 'trap') || $g === 'level3') {
            return 'link-trap';
        }

        return 'phishing';
    }

    public function getGameLabelAttribute(): string
    {
        return match ($this->game_slug) {
            'cyber-defender' => 'Cyber Defender',
            'link-trap' => 'Link or Trap?',
            default => 'Phishing Hunter',
        };
    }

    // normalisasi level ke easy, medium, hard, expert, master, atau custom level
    public function getLevelNormalizeAttribute(): string
    {
        $t = strtolower(trim((string) $this->tingkat));
        return match ($t) {
            'sedang', 'medium' => 'medium',
            'sulit', 'hard' => 'hard',
            'mudah', 'easy', '' => 'easy',
            default => $t,
        };
    }
}
