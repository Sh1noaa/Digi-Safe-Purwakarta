<?php

namespace App\Http\Controllers;

use App\Models\ModulEdukasi;
use App\Services\BilingualHelper;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(): View
    {
        $moduls = ModulEdukasi::orderBy('id')->get();

        $variants = ['solid', 'chart', 'glass', 'glass-dark', 'solid', 'featured'];
        $spans = [
            'lg:col-span-5 lg:row-span-1 min-h-[250px]',
            'lg:col-span-4 lg:row-span-1 min-h-[250px]',
            'lg:col-span-3 lg:row-span-1 min-h-[250px]',
            'lg:col-span-3 lg:row-span-1 min-h-[240px]',
            'lg:col-span-4 lg:row-span-1 min-h-[240px]',
            'lg:col-span-5 lg:row-span-1 min-h-[240px]',
        ];
        $icons = ['journal', 'shield', 'check', 'globe', 'users', 'spark'];

        $cards = $moduls->values()->map(function (ModulEdukasi $modul, int $idx) use ($variants, $spans, $icons) {
            $type = $modul->effective_type;
            $fullKonten = (string) ($modul->konten ?: '');
            $translated = BilingualHelper::translateModule(
                (int) $modul->id,
                (string) $modul->judul,
                Str::limit(strip_tags($fullKonten), 115),
                $fullKonten,
                (string) ($modul->kategori ?: 'Literasi Siber')
            );

            return [
                'id' => $modul->id,
                'title' => $translated['title'],
                'description' => $translated['description'],
                'konten' => $translated['konten'],
                'type' => $type,
                'url' => $modul->effective_url ?: '#',
                'stat' => (string) (($idx + 1) * 4 + 4),
                'tag' => $translated['tag'],
                'variant' => $variants[$idx % count($variants)],
                'span' => $spans[$idx % count($spans)],
                'icon' => $icons[$idx % count($icons)],
            ];
        })->all();

        return view('edukasi', compact('cards'));
    }
}
