<?php

namespace App\Models;

use App\Services\DailyStreakService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DailyStreakQuestion extends Model
{
    protected $table = 'daily_streak_questions';

    protected $fillable = [
        'question_key',
        'category_id',
        'category_en',
        'difficulty_id',
        'difficulty_en',
        'prompt_id',
        'prompt_en',
        'options_id_json',
        'options_en_json',
        'answer',
        'explanation_id',
        'explanation_en',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'options_id_json' => 'array',
        'options_en_json' => 'array',
        'is_active' => 'boolean',
    ];

    public static function ensureInitialized(): void
    {
        try {
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

            if (self::query()->count() === 0) {
                $idBank = DailyStreakService::defaultQuestionsBank(false);
                $enBank = collect(DailyStreakService::defaultQuestionsBank(true))->keyBy('key');

                foreach ($idBank as $item) {
                    $enItem = $enBank->get($item['key']);
                    self::query()->firstOrCreate(
                        ['question_key' => $item['key']],
                        [
                            'category_id' => $item['category'],
                            'category_en' => $enItem['category'] ?? $item['category'],
                            'difficulty_id' => $item['difficulty'],
                            'difficulty_en' => $enItem['difficulty'] ?? 'Advanced',
                            'prompt_id' => $item['prompt'],
                            'prompt_en' => $enItem['prompt'] ?? null,
                            'options_id_json' => $item['options'],
                            'options_en_json' => $enItem['options'] ?? null,
                            'answer' => strtoupper($item['answer']),
                            'explanation_id' => $item['explanation'],
                            'explanation_en' => $enItem['explanation'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }
        } catch (\Throwable) {
            // Abaikan jika DB belum siap
        }
    }

    public function getOptionsIdAttribute(): array
    {
        return self::normalizeOptions($this->options_id_json);
    }

    public function getOptionsEnAttribute(): array
    {
        return self::normalizeOptions($this->options_en_json);
    }

    public function toBankItem(bool $isEn = false): array
    {
        $optsId = $this->options_id;
        $optsEn = $this->options_en;
        $hasCompleteEnOpts = count(array_filter($optsEn, fn ($v) => trim((string) $v) !== '')) === 5;

        return [
            'key' => $this->question_key,
            'category' => ($isEn && filled($this->category_en)) ? $this->category_en : $this->category_id,
            'difficulty' => ($isEn && filled($this->difficulty_en)) ? $this->difficulty_en : $this->difficulty_id,
            'prompt' => ($isEn && filled($this->prompt_en)) ? $this->prompt_en : $this->prompt_id,
            'options' => ($isEn && $hasCompleteEnOpts) ? $optsEn : $optsId,
            'answer' => strtoupper((string) $this->answer),
            'explanation' => ($isEn && filled($this->explanation_en)) ? $this->explanation_en : $this->explanation_id,
        ];
    }

    private static function normalizeOptions(mixed $raw): array
    {
        $decoded = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return [
            'A' => (string) ($decoded['A'] ?? ''),
            'B' => (string) ($decoded['B'] ?? ''),
            'C' => (string) ($decoded['C'] ?? ''),
            'D' => (string) ($decoded['D'] ?? ''),
            'E' => (string) ($decoded['E'] ?? ''),
        ];
    }
}
