<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyStreakQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StreakQuestionController extends Controller
{
    public function index(Request $request): View
    {
        DailyStreakQuestion::ensureInitialized();

        $search = trim((string) $request->query('q', ''));
        $filterStatus = (string) $request->query('status', 'semua');

        $allQuestions = DailyStreakQuestion::query()->orderByDesc('id')->get();

        $questions = $allQuestions->filter(function (DailyStreakQuestion $item) use ($search, $filterStatus) {
            if ($filterStatus === 'aktif' && ! $item->is_active) {
                return false;
            }
            if ($filterStatus === 'nonaktif' && $item->is_active) {
                return false;
            }
            if ($search !== '') {
                $needle = Str::lower($search);
                $haystack = Str::lower(implode(' ', [
                    $item->category_id,
                    $item->category_en ?? '',
                    $item->difficulty_id,
                    $item->prompt_id,
                    $item->prompt_en ?? '',
                    $item->explanation_id,
                ]));

                return str_contains($haystack, $needle);
            }

            return true;
        })->values();

        $stats = [
            'total' => $allQuestions->count(),
            'active' => $allQuestions->where('is_active', true)->count(),
            'inactive' => $allQuestions->where('is_active', false)->count(),
        ];

        $categories = $allQuestions->pluck('category_id')->filter()->unique()->sort()->values();

        return view('admin.streak.index', compact(
            'questions',
            'stats',
            'categories',
            'search',
            'filterStatus',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        DailyStreakQuestion::ensureInitialized();

        $validated = $this->validateQuestion($request);

        $slug = Str::slug(Str::limit($validated['category_id'], 24, ''), '_');
        $questionKey = 'custom_' . ($slug !== '' ? $slug : 'streak') . '_' . Str::lower(Str::random(6));

        DailyStreakQuestion::create([
            'question_key' => $questionKey,
            'category_id' => $validated['category_id'],
            'category_en' => ! empty($validated['category_en']) ? $validated['category_en'] : null,
            'difficulty_id' => $validated['difficulty_id'],
            'difficulty_en' => ! empty($validated['difficulty_en']) ? $validated['difficulty_en'] : 'Advanced',
            'prompt_id' => $validated['prompt_id'],
            'prompt_en' => ! empty($validated['prompt_en']) ? $validated['prompt_en'] : null,
            'options_id_json' => [
                'A' => $validated['option_a'],
                'B' => $validated['option_b'],
                'C' => $validated['option_c'],
                'D' => $validated['option_d'],
                'E' => $validated['option_e'],
            ],
            'options_en_json' => $this->extractEnglishOptions($validated),
            'answer' => strtoupper($validated['answer']),
            'explanation_id' => $validated['explanation_id'],
            'explanation_en' => ! empty($validated['explanation_en']) ? $validated['explanation_en'] : null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        $isEn = app()->getLocale() === 'en';

        return redirect()
            ->route('admin.streak.index')
            ->with('success', $isEn
                ? 'New Daily Streak question has been added to the question bank.'
                : 'Soal Daily Streak baru berhasil ditambahkan ke bank soal.');
    }

    public function update(Request $request, DailyStreakQuestion $streakQuestion): RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';
        $validated = $this->validateQuestion($request);
        $nextActive = $request->boolean('is_active', true);

        if (! $nextActive && $streakQuestion->is_active && DailyStreakQuestion::where('is_active', true)->count() <= 1) {
            return redirect()
                ->route('admin.streak.index')
                ->with('error', $isEn
                    ? 'At least 1 Daily Streak question must remain active.'
                    : 'Minimal harus ada 1 soal Daily Streak yang aktif agar tantangan harian tetap berjalan.');
        }

        $streakQuestion->update([
            'category_id' => $validated['category_id'],
            'category_en' => ! empty($validated['category_en']) ? $validated['category_en'] : null,
            'difficulty_id' => $validated['difficulty_id'],
            'difficulty_en' => ! empty($validated['difficulty_en']) ? $validated['difficulty_en'] : 'Advanced',
            'prompt_id' => $validated['prompt_id'],
            'prompt_en' => ! empty($validated['prompt_en']) ? $validated['prompt_en'] : null,
            'options_id_json' => [
                'A' => $validated['option_a'],
                'B' => $validated['option_b'],
                'C' => $validated['option_c'],
                'D' => $validated['option_d'],
                'E' => $validated['option_e'],
            ],
            'options_en_json' => $this->extractEnglishOptions($validated),
            'answer' => strtoupper($validated['answer']),
            'explanation_id' => $validated['explanation_id'],
            'explanation_en' => ! empty($validated['explanation_en']) ? $validated['explanation_en'] : null,
            'is_active' => $nextActive,
        ]);

        return redirect()
            ->route('admin.streak.index')
            ->with('success', $isEn
                ? 'Daily Streak question updated successfully.'
                : 'Soal Daily Streak berhasil diperbarui.');
    }

    public function toggleActive(DailyStreakQuestion $streakQuestion): RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        if ($streakQuestion->is_active && DailyStreakQuestion::where('is_active', true)->count() <= 1) {
            return redirect()
                ->route('admin.streak.index')
                ->with('error', $isEn
                    ? 'Cannot deactivate the last active question.'
                    : 'Tidak dapat menonaktifkan soal terakhir yang aktif.');
        }

        $streakQuestion->update(['is_active' => ! $streakQuestion->is_active]);

        return redirect()
            ->route('admin.streak.index')
            ->with('success', $streakQuestion->is_active
                ? ($isEn ? 'Daily Streak question activated.' : 'Soal Daily Streak diaktifkan.')
                : ($isEn ? 'Daily Streak question deactivated.' : 'Soal Daily Streak dinonaktifkan.'));
    }

    public function destroy(DailyStreakQuestion $streakQuestion): RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        if ($streakQuestion->is_active && DailyStreakQuestion::where('is_active', true)->count() <= 1) {
            return redirect()
                ->route('admin.streak.index')
                ->with('error', $isEn
                    ? 'At least 1 Daily Streak question must remain active before deleting this one.'
                    : 'Minimal harus ada 1 soal Daily Streak yang aktif. Aktifkan soal lain terlebih dahulu sebelum menghapus soal ini.');
        }

        $streakQuestion->delete();

        return redirect()
            ->route('admin.streak.index')
            ->with('success', $isEn
                ? 'Daily Streak question has been deleted.'
                : 'Soal Daily Streak telah dihapus.');
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'string', 'max:100'],
            'category_en' => ['nullable', 'string', 'max:100'],
            'difficulty_id' => ['required', 'string', 'max:40'],
            'difficulty_en' => ['nullable', 'string', 'max:40'],
            'prompt_id' => ['required', 'string', 'max:2000'],
            'prompt_en' => ['nullable', 'string', 'max:2000'],
            'option_a' => ['required', 'string', 'max:500'],
            'option_b' => ['required', 'string', 'max:500'],
            'option_c' => ['required', 'string', 'max:500'],
            'option_d' => ['required', 'string', 'max:500'],
            'option_e' => ['required', 'string', 'max:500'],
            'option_a_en' => ['nullable', 'string', 'max:500'],
            'option_b_en' => ['nullable', 'string', 'max:500'],
            'option_c_en' => ['nullable', 'string', 'max:500'],
            'option_d_en' => ['nullable', 'string', 'max:500'],
            'option_e_en' => ['nullable', 'string', 'max:500'],
            'answer' => ['required', 'in:A,B,C,D,E'],
            'explanation_id' => ['required', 'string', 'max:2000'],
            'explanation_en' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function extractEnglishOptions(array $validated): ?array
    {
        $enOpts = [
            'A' => trim((string) ($validated['option_a_en'] ?? '')),
            'B' => trim((string) ($validated['option_b_en'] ?? '')),
            'C' => trim((string) ($validated['option_c_en'] ?? '')),
            'D' => trim((string) ($validated['option_d_en'] ?? '')),
            'E' => trim((string) ($validated['option_e_en'] ?? '')),
        ];

        $hasAny = count(array_filter($enOpts, fn ($v) => $v !== '')) > 0;

        return $hasAny ? $enOpts : null;
    }
}
