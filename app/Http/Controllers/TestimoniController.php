<?php

namespace App\Http\Controllers;

use App\Models\Testimoni;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TestimoniController extends Controller
{
    public function index(): View
    {
        // ambil seluruh data testimoni terbaru beserta relasi user
        $testimonis = Testimoni::with('user')
            ->whereIn('status_approval', ['approved', 'pending'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('testimoni.index', compact('testimonis'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $isEn = app()->getLocale() === 'en';

        // validasi form ulasan
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'cerita' => ['required', 'string', 'max:1500'],
            'is_anonymous' => ['nullable', 'boolean'],
        ], [
            'rating.required' => $isEn ? 'Please select a star rating first.' : 'Silakan pilih rating bintang terlebih dahulu.',
            'cerita.required' => $isEn ? 'Your experience story cannot be empty.' : 'Cerita pengalaman tidak boleh kosong.',
        ]);

        // simpan ulasan dengan status approved agar langsung muncul di daftar testimoni
        $testimoni = Testimoni::create([
            'user_id' => Auth::id(),
            'judul' => mb_substr(trim($validated['cerita']), 0, 50),
            'cerita' => $validated['cerita'],
            'rating' => (int) $validated['rating'],
            'mood' => (string) $validated['rating'],
            'is_anonymous' => $request->boolean('is_anonymous') ? 1 : 0,
            'status_approval' => 'approved',
            'created_at' => now(),
        ]);

        $testimoni->load('user');

        $newItem = [
            'id' => $testimoni->id,
            'user_id' => $testimoni->user_id,
            'profile_url' => (! $testimoni->is_anonymous && $testimoni->user) ? route('profile.show', $testimoni->user->id) : null,
            'nama' => $testimoni->nama_tampil,
            'inisial' => $testimoni->inisial_tampil,
            'avatar' => $testimoni->avatar_tampil,
            'border_url' => (! $testimoni->is_anonymous && $testimoni->user) ? $testimoni->user->avatar_border_url : null,
            'pastel_class' => $testimoni->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
            'is_owner' => (! $testimoni->is_anonymous && (bool) $testimoni->user?->isOwner()),
            'is_anonymous' => (bool) $testimoni->is_anonymous,
            'rating' => (int) ($testimoni->rating ?: 5),
            'judul' => $testimoni->judul,
            'cerita' => $testimoni->cerita,
            'waktu' => __('ui.forum.just_now'),
        ];

        $msg = $isEn
            ? 'Thank you! Your review has been published and is now live.'
            : 'Terima kasih! Ulasanmu telah diterbitkan dan langsung tampil.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'item' => $newItem,
            ]);
        }

        return redirect()->route('testimoni.index')->with('success', $msg);
    }
}
