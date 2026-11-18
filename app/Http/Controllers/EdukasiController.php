<?php

namespace App\Http\Controllers;

use App\Models\EdukasiRequest;
use App\Models\ModulEdukasi;
use App\Services\BilingualHelper;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(): View
    {
        SocialSchemaHelper::ensureReady();

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

        $myRequests = collect();
        $unreadDecisionsCount = 0;

        if (Auth::check()) {
            $myRequests = EdukasiRequest::where('user_id', Auth::id())
                ->latest('id')
                ->limit(15)
                ->get();

            $unreadDecisionsCount = $myRequests
                ->whereIn('status', ['approved', 'rejected'])
                ->where('user_notified_read', false)
                ->count();
        }

        return view('edukasi', compact('cards', 'myRequests', 'unreadDecisionsCount'));
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        SocialSchemaHelper::ensureReady();
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'tema' => ['required', 'string', 'min:4', 'max:180'],
            'kategori' => ['required', 'string', 'max:80'],
            'preferred_type' => ['nullable', 'string', 'in:teks,link'],
            'deskripsi' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'tema.required' => $isEn ? 'Please enter the education topic you want to request.' : 'Tema atau judul edukasi wajib diisi.',
            'tema.min' => $isEn ? 'Topic title must be at least 4 characters.' : 'Tema edukasi minimal 4 karakter.',
            'deskripsi.required' => $isEn ? 'Please describe why this topic is needed.' : 'Jelaskan alasan atau detail materi yang ingin ditambahkan.',
            'deskripsi.min' => $isEn ? 'Description must be at least 10 characters.' : 'Penjelasan singkat minimal 10 karakter.',
        ]);

        EdukasiRequest::create([
            'user_id' => Auth::id(),
            'tema' => trim($validated['tema']),
            'kategori' => trim($validated['kategori']),
            'preferred_type' => $validated['preferred_type'] ?? 'teks',
            'deskripsi' => trim($validated['deskripsi']),
            'status' => 'pending',
            'user_notified_read' => false,
        ]);

        return redirect()
            ->route('edukasi')
            ->with('success', $isEn
                ? 'Your education topic request has been sent! Bot Digika will message you once the Admin team reviews it.'
                : 'Request tema edukasi berhasil dikirim! Tim Admin akan mengevaluasinya dan Bot Digika akan mengirim pesan otomatis kepada Anda.');
    }

    public function markRequestsSeen(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();

        if (Auth::check()) {
            EdukasiRequest::where('user_id', Auth::id())
                ->whereIn('status', ['approved', 'rejected'])
                ->where('user_notified_read', false)
                ->update(['user_notified_read' => true]);
        }

        return response()->json(['success' => true]);
    }
}
