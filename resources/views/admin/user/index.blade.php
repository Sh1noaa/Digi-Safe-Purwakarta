@extends('admin.dashboard')

@section('admin_content')
    <div>
        <!-- header area hak akses user -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-ink">
                    Manajemen Hak Akses Pengguna
                </h1>
                <p class="mt-1 text-xs text-muted">
                    Atur wewenang akun antara User dan Admin. Akun Owner bersifat permanen dan dilindungi sistem.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full bg-[#F8FAFC] border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-ink shrink-0">
                <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                <span>Total: {{ $users->count() }} Pengguna</span>
            </div>
        </div>

        <!-- tabel daftar pengguna -->
        <div class="mt-6 overflow-x-auto md:overflow-visible rounded-2xl border border-gray-200/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-gray-200/80 text-[11px] font-heading font-bold uppercase tracking-wider text-muted">
                        <th class="py-3.5 px-4">Nama & Username</th>
                        <th class="py-3.5 px-4">Alamat Email</th>
                        <th class="py-3.5 px-4 w-36">Role Saat Ini</th>
                        <th class="py-3.5 px-4 w-64 text-right">Aksi Hak Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @foreach ($users as $u)
                        @php
                            $isTargetOwner = $u->isOwner();
                            $isTargetAdmin = $u->isAdmin();
                            $currentAuthIsOwner = Auth::user()->isOwner();
                        @endphp

                        <tr class="{{ $isTargetOwner ? 'bg-lime/15' : 'hover:bg-[#F8FAFC]/80' }} transition-colors align-middle">
                            <!-- nama & avatar inisial -->
                            <td class="py-4 px-4">
                                <a href="{{ route('profile.show', $u->id) }}" class="flex items-center gap-3 group/u">
                                    <x-user-avatar :user="$u" size="md" class="{{ $isTargetOwner ? 'ring-2 ring-lime' : '' }} group-hover/u:scale-105 transition-transform" />
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-ink group-hover/u:text-sky transition-colors truncate">
                                            {{ $u->nama_lengkap ?: $u->username }}
                                        </p>
                                        <p class="text-[11px] text-muted truncate">
                                            &#64;{{ $u->username }}
                                        </p>
                                    </div>
                                </a>
                            </td>

                            <!-- email -->
                            <td class="py-4 px-4 text-muted font-medium">
                                {{ $u->email }}
                            </td>

                            <!-- badge role saat ini -->
                            <td class="py-4 px-4">
                                @if ($isTargetOwner)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-ink text-lime px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider shadow-2xs">
                                        <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                        </svg>
                                        <span>Owner</span>
                                    </span>
                                @elseif ($isTargetAdmin)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-lime text-ink px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        </svg>
                                        <span>Admin</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 text-muted px-3 py-1 text-[10px] font-bold uppercase tracking-wider">
                                        User
                                    </span>
                                @endif
                            </td>

                            <!-- aksi ubah role / proteksi mutlak owner -->
                            <td class="py-4 px-4 text-right">
                                @if ($isTargetOwner)
                                    <!-- aturan mutlak: baris owner di-disable tanpa tombol edit/hapus -->
                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-ink text-lime border border-ink px-3.5 py-2 text-[11px] font-heading font-bold select-none">
                                        <svg class="w-3.5 h-3.5 text-lime shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                        </svg>
                                        <span>Owner (Tidak dapat diubah)</span>
                                    </span>
                                @elseif (! $currentAuthIsOwner && $isTargetAdmin)
                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 text-muted border border-gray-200 px-3 py-1.5 text-[11px] font-semibold select-none">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <span>Hanya Owner dapat mengubah Admin</span>
                                    </span>
                                @else
                                    <form action="{{ route('admin.user.role', $u->id) }}" method="POST" class="inline-flex items-center justify-end gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <x-custom-select
                                            name="role"
                                            :value="$u->role"
                                            variant="compact"
                                            align="right"
                                            widthClass="w-32"
                                            :options="[
                                                ['value' => 'user', 'label' => 'User', 'dot' => '#94A3B8', 'desc' => 'Warga Siber'],
                                                ['value' => 'admin', 'label' => 'Admin', 'dot' => '#84CC16', 'desc' => 'Pengelola Panel'],
                                            ]"
                                        />

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-3.5 py-1.5 transition-colors cursor-pointer"
                                        >
                                            Simpan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
