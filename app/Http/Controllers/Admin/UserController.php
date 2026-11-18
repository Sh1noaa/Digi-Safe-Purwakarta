<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        // daftar pengguna diurutkan dari owner, admin, lalu user (kecuali akun sistem Bot Digika)
        $users = User::humans()
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'user')")
            ->orderBy('id')
            ->get();

        return view('admin.user.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        // proteksi mutlak: akun owner tidak dapat diubah oleh siapapun
        if ($user->isOwner()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'Akun Owner utama bersifat permanen dan tidak dapat diubah.');
        }

        // cegah admin biasa menurunkan hak akses sesama admin
        $currentUser = Auth::user();
        if (! $currentUser->isOwner() && $user->isAdmin()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'Hanya Owner yang memiliki wewenang mengubah hak akses Admin lain.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Hak akses pengguna ' . ($user->nama_lengkap ?: $user->username) . ' berhasil diperbarui menjadi ' . strtoupper($validated['role']) . '.');
    }
}
