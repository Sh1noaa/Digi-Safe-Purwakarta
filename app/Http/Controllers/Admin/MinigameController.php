<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MinigameLevel;
use App\Models\SoalMinigame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MinigameController extends Controller
{
    public function index(Request $request): View
    {
        MinigameLevel::ensureInitialized();

        $filterGame = $request->query('game', 'semua');
        $filterLevel = $request->query('level', 'semua');

        $levels = MinigameLevel::orderBy('urutan')->orderBy('id')->get();
        $allSoals = SoalMinigame::orderByDesc('id')->get();

        $soals = $allSoals->filter(function (SoalMinigame $soal) use ($filterGame, $filterLevel) {
            $matchGame = $filterGame === 'semua' || $soal->game_slug === $filterGame;
            $matchLevel = $filterLevel === 'semua' || $soal->level_normalize === $filterLevel;

            return $matchGame && $matchLevel;
        })->values();

        // hitung jumlah soal per level
        $levelCounts = [];
        foreach ($levels as $lvl) {
            $levelCounts[$lvl->slug] = $allSoals->filter(fn (SoalMinigame $s) => $s->level_normalize === $lvl->slug)->count();
        }

        return view('admin.minigame.index', compact('soals', 'levels', 'levelCounts', 'filterGame', 'filterLevel'));
    }

    public function store(Request $request): RedirectResponse
    {
        MinigameLevel::ensureInitialized();
        $validSlugs = MinigameLevel::pluck('slug')->all();

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
            'pilihan_a' => ['required', 'string', 'max:255'],
            'pilihan_b' => ['required', 'string', 'max:255'],
            'pilihan_c' => ['required', 'string', 'max:255'],
            'pilihan_d' => ['required', 'string', 'max:255'],
            'jawaban_benar' => ['required', 'in:A,B,C,D'],
            'game_level' => ['required', 'in:phishing,cyber-defender,link-trap'],
            'tingkat' => ['required', 'string', Rule::in($validSlugs ?: ['easy', 'medium', 'hard', 'expert', 'master'])],
        ]);

        $kategoriLabel = match ($validated['game_level']) {
            'cyber-defender' => 'Cyber Defender',
            'link-trap' => 'Link or Trap',
            default => 'Phishing Hunter',
        };

        SoalMinigame::create([
            'pertanyaan' => $validated['pertanyaan'],
            'pilihan_json' => json_encode([
                'A' => $validated['pilihan_a'],
                'B' => $validated['pilihan_b'],
                'C' => $validated['pilihan_c'],
                'D' => $validated['pilihan_d'],
            ]),
            'jawaban_benar' => $validated['jawaban_benar'],
            'kategori' => $kategoriLabel,
            'tingkat' => $validated['tingkat'],
            'game_level' => $validated['game_level'],
        ]);

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Soal minigame baru berhasil disimpan.');
    }

    public function update(Request $request, SoalMinigame $minigame): RedirectResponse
    {
        MinigameLevel::ensureInitialized();
        $validSlugs = MinigameLevel::pluck('slug')->all();

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
            'pilihan_a' => ['required', 'string', 'max:255'],
            'pilihan_b' => ['required', 'string', 'max:255'],
            'pilihan_c' => ['required', 'string', 'max:255'],
            'pilihan_d' => ['required', 'string', 'max:255'],
            'jawaban_benar' => ['required', 'in:A,B,C,D'],
            'game_level' => ['required', 'in:phishing,cyber-defender,link-trap'],
            'tingkat' => ['required', 'string', Rule::in($validSlugs ?: ['easy', 'medium', 'hard', 'expert', 'master'])],
        ]);

        $kategoriLabel = match ($validated['game_level']) {
            'cyber-defender' => 'Cyber Defender',
            'link-trap' => 'Link or Trap',
            default => 'Phishing Hunter',
        };

        $minigame->update([
            'pertanyaan' => $validated['pertanyaan'],
            'pilihan_json' => json_encode([
                'A' => $validated['pilihan_a'],
                'B' => $validated['pilihan_b'],
                'C' => $validated['pilihan_c'],
                'D' => $validated['pilihan_d'],
            ]),
            'jawaban_benar' => $validated['jawaban_benar'],
            'kategori' => $kategoriLabel,
            'tingkat' => $validated['tingkat'],
            'game_level' => $validated['game_level'],
        ]);

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Soal minigame berhasil diperbarui.');
    }

    public function destroy(SoalMinigame $minigame): RedirectResponse
    {
        $minigame->delete();

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Soal minigame telah dihapus.');
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        MinigameLevel::ensureInitialized();

        $validated = $request->validate([
            'nama_level' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:50'],
            'badge_label' => ['required', 'string', 'max:60'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'waktu_detik' => ['required', 'integer', 'min:3', 'max:120'],
            'poin_benar' => ['required', 'integer', 'min:5', 'max:200'],
            'penalti_salah' => ['required', 'integer', 'min:0', 'max:100'],
            'hp_awal' => ['required', 'integer', 'min:1', 'max:10'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
            'warna' => ['required', 'in:emerald,sky,amber,purple,red,lime'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['slug'] ?: $validated['nama_level']);
        if ($slug === '') {
            $slug = 'level-' . (MinigameLevel::count() + 1);
        }

        // pastikan slug unik
        $baseSlug = $slug;
        $counter = 2;
        while (MinigameLevel::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        MinigameLevel::create([
            'slug' => $slug,
            'nama_level' => $validated['nama_level'],
            'badge_label' => strtoupper($validated['badge_label']),
            'deskripsi' => $validated['deskripsi'],
            'waktu_detik' => (int) $validated['waktu_detik'],
            'poin_benar' => (int) $validated['poin_benar'],
            'penalti_salah' => (int) $validated['penalti_salah'],
            'hp_awal' => (int) $validated['hp_awal'],
            'urutan' => (int) $validated['urutan'],
            'warna' => $validated['warna'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Level minigame baru "' . $validated['nama_level'] . '" berhasil ditambahkan.');
    }

    public function updateLevel(Request $request, MinigameLevel $level): RedirectResponse
    {
        $validated = $request->validate([
            'nama_level' => ['required', 'string', 'max:100'],
            'badge_label' => ['required', 'string', 'max:60'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'waktu_detik' => ['required', 'integer', 'min:3', 'max:120'],
            'poin_benar' => ['required', 'integer', 'min:5', 'max:200'],
            'penalti_salah' => ['required', 'integer', 'min:0', 'max:100'],
            'hp_awal' => ['required', 'integer', 'min:1', 'max:10'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
            'warna' => ['required', 'in:emerald,sky,amber,purple,red,lime'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $level->update([
            'nama_level' => $validated['nama_level'],
            'badge_label' => strtoupper($validated['badge_label']),
            'deskripsi' => $validated['deskripsi'],
            'waktu_detik' => (int) $validated['waktu_detik'],
            'poin_benar' => (int) $validated['poin_benar'],
            'penalti_salah' => (int) $validated['penalti_salah'],
            'hp_awal' => (int) $validated['hp_awal'],
            'urutan' => (int) $validated['urutan'],
            'warna' => $validated['warna'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Level "' . $level->nama_level . '" berhasil diperbarui.');
    }

    public function destroyLevel(MinigameLevel $level): RedirectResponse
    {
        if (MinigameLevel::count() <= 1) {
            return redirect()
                ->route('admin.minigame.index')
                ->with('error', 'Minimal harus ada 1 level aktif di dalam sistem minigame.');
        }

        $nama = $level->nama_level;
        $level->delete();

        return redirect()
            ->route('admin.minigame.index')
            ->with('success', 'Level "' . $nama . '" telah dihapus.');
    }
}
