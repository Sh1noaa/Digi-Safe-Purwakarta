<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\EdukasiRequest;
use App\Models\ModulEdukasi;
use App\Models\User;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(): View
    {
        SocialSchemaHelper::ensureReady();

        // daftar modul edukasi terbaru
        $moduls = ModulEdukasi::orderByDesc('id')->get();

        // daftar permintaan tema edukasi dari pengguna (pending di atas)
        $edukasiRequests = EdukasiRequest::with(['user', 'reviewer'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest('id')
            ->get();

        return view('admin.edukasi.index', compact('moduls', 'edukasiRequests'));
    }

    public function store(Request $request): RedirectResponse
    {
        // validasi input modul berdasarkan tipe konten
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:teks,link'],
            'konten' => ['required_if:type,teks', 'nullable', 'string'],
            'url' => ['required_if:type,link', 'nullable', 'url:http,https', 'max:500'],
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
            'url' => ['required_if:type,link', 'nullable', 'url:http,https', 'max:500'],
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

    public function approveRequest(Request $request, EdukasiRequest $edukasiRequest): RedirectResponse
    {
        return $this->evaluateRequest($request, $edukasiRequest, 'approved');
    }

    public function rejectRequest(Request $request, EdukasiRequest $edukasiRequest): RedirectResponse
    {
        return $this->evaluateRequest($request, $edukasiRequest, 'rejected');
    }

    private function evaluateRequest(Request $request, EdukasiRequest $edukasiRequest, string $decision): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        self::applyEvaluationDecision(
            $edukasiRequest,
            $decision,
            trim((string) ($validated['admin_note'] ?? '')),
            Auth::id()
        );

        $statusLabel = $decision === 'approved' ? 'disetujui' : 'ditolak';

        return redirect()
            ->route('admin.edukasi.index', ['tab' => 'requests'])
            ->with('success', "Request tema \"{$edukasiRequest->tema}\" berhasil {$statusLabel}. Bot Digika telah mengirim pesan notifikasi otomatis ke pengguna.");
    }

    public static function applyEvaluationDecision(
        EdukasiRequest $edukasiRequest,
        string $decision,
        string $note = '',
        ?int $reviewerId = null
    ): void {
        SocialSchemaHelper::ensureReady();
        $cleanNote = trim($note);

        $edukasiRequest->update([
            'status' => $decision,
            'admin_note' => $cleanNote !== '' ? $cleanNote : null,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'user_notified_read' => false,
        ]);

        $requester = $edukasiRequest->user;
        if (! $requester) {
            return;
        }

        $bot = User::ensureBotDigika();
        $nama = $requester->nama_lengkap ?: $requester->username;
        $tema = $edukasiRequest->tema;
        $kategori = $edukasiRequest->kategori;
        $isEnUser = ($requester->locale ?? 'id') === 'en';

        if ($decision === 'approved') {
            $messageText = $isEnUser
                ? "Hello {$nama}!\n\nThank you for requesting the education topic \"{$tema}\" (Category: {$kategori}). After review and discussion with the Digi-Safe Purwakarta Admin team, we have APPROVED your proposal to be added to our Education Center!"
                : "Halo {$nama}!\n\nTerima kasih telah berpartisipasi mengajukan request tema edukasi \"{$tema}\" (Kategori: {$kategori}). Setelah melalui evaluasi dan rundingan tim Admin Digi-Safe Purwakarta, kami MENYETUJUI usulan tema tersebut untuk ditambahkan ke halaman Edukasi!";

            if ($cleanNote !== '') {
                $messageText .= $isEnUser
                    ? "\n\nTeam Note: {$cleanNote}"
                    : "\n\nCatatan Tim: {$cleanNote}";
            }

            $messageText .= $isEnUser
                ? "\n\nStay tuned for the new learning module. — Automated message by Bot Digika"
                : "\n\nNantikan modul barunya ya, terima kasih sudah ikut menjaga keamanan digital warga! — Pesan otomatis oleh Bot Digika";
        } else {
            $messageText = $isEnUser
                ? "Hello {$nama}.\n\nThank you for submitting the education topic request \"{$tema}\" (Category: {$kategori}). After careful discussion by the Digi-Safe Purwakarta Admin team, we regret that we CANNOT APPROVE this request at the moment because it overlaps with existing modules or falls outside our current curriculum focus."
                : "Halo {$nama}.\n\nTerima kasih telah meluangkan waktu mengajukan request tema edukasi \"{$tema}\" (Kategori: {$kategori}). Setelah dilakukan peninjauan dan rundingan tim Admin Digi-Safe Purwakarta, dengan berat hati usulan tema tersebut BELUM DAPAT KAMI SETUJUI saat ini karena topiknya sudah tercakup pada materi lain atau belum sesuai prioritas kurikulum.";

            if ($cleanNote !== '') {
                $messageText .= $isEnUser
                    ? "\n\nReason / Note: {$cleanNote}"
                    : "\n\nAlasan / Catatan Tim: {$cleanNote}";
            }

            $messageText .= $isEnUser
                ? "\n\nFeel free to submit another interesting digital safety idea anytime! — Automated message by Bot Digika"
                : "\n\nJangan ragu untuk mengajukan ide tema keamanan siber lainnya kapan saja ya! — Pesan otomatis oleh Bot Digika";
        }

        ChatMessage::create([
            'sender_id' => $bot->id,
            'receiver_id' => $requester->id,
            'message' => $messageText,
            'is_read' => false,
        ]);
    }
}
