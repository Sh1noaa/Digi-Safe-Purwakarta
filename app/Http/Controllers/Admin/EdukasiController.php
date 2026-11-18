<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulEdukasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(): View
    {
        // daftar modul edukasi terbaru
        $moduls = ModulEdukasi::orderByDesc('id')->get();

        return view('admin.edukasi.index', compact('moduls'));
    }

    public function store(Request $request): RedirectResponse
    {
        // validasi input modul berdasarkan tipe konten
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url', 'max:500'],
        ]);

        ModulEdukasi::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'type' => $validated['type'],
            'konten' => $validated['type'] === 'teks'
                ? $validated['konten']
                : ($request->input('konten') ?: 'Referensi literasi keamanan digital terverifikasi.'),
            'url' => $validated['type'] === 'link' ? $validated['url'] : null,
            'link_url' => $validated['type'] === 'link' ? ($validated['url'] ?? '') : '',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Modul edukasi baru berhasil ditambahkan.');
    }

    public function update(Request $request, ModulEdukasi $edukasi): RedirectResponse
    {
        // perbarui data modul edukasi
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url', 'max:500'],
        ]);

        $edukasi->update([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'type' => $validated['type'],
            'konten' => $validated['type'] === 'teks'
                ? $validated['konten']
                : ($request->input('konten') ?: $edukasi->konten),
            'url' => $validated['type'] === 'link' ? $validated['url'] : null,
            'link_url' => $validated['type'] === 'link' ? ($validated['url'] ?? '') : '',
        ]);

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Modul edukasi berhasil diperbarui.');
    }

    public function destroy(ModulEdukasi $edukasi): RedirectResponse
    {
        // hapus modul edukasi
        $edukasi->delete();

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Modul edukasi telah dihapus.');
    }
}
