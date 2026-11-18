@extends('admin.dashboard')

@section('admin_content')
    <div>
        <!-- header area moderasi forum -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-ink">
                    Moderasi & Sematan Forum Komunitas
                </h1>
                <p class="mt-1 text-xs text-muted">
                    Klik ikon Bintang untuk menyematkan diskusi penting agar selalu tampil paling atas di halaman Forum pengguna.
                </p>
            </div>

            <a
                href="{{ route('forum.index') }}"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-full bg-[#F8FAFC] border border-gray-200 hover:border-sky hover:text-sky px-4 py-2 text-xs font-heading font-bold text-ink transition-colors shrink-0"
            >
                <span>Lihat Halaman Forum</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="7" y1="17" x2="17" y2="7" />
                    <polyline points="7 7 17 7 17 17" />
                </svg>
            </a>
        </div>

        <!-- daftar thread forum -->
        <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-200/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-gray-200/80 text-[11px] font-heading font-bold uppercase tracking-wider text-muted">
                        <th class="py-3.5 px-4 w-16 text-center">Semat</th>
                        <th class="py-3.5 px-4">Judul & Cuplikan Diskusi</th>
                        <th class="py-3.5 px-4 w-44">Penulis</th>
                        <th class="py-3.5 px-4 w-32">Tanggal</th>
                        <th class="py-3.5 px-4 w-24 text-right">Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse ($forums as $item)
                        @php
                            $authorName = $item->user?->nama_lengkap ?: ($item->user?->username ?: 'Warga Purwakarta');
                            $isOwnerAuthor = (bool) $item->user?->isOwner();
                            $isAdminAuthor = (bool) $item->user?->isAdmin();
                        @endphp
                        <tr class="{{ $item->is_starred ? 'bg-[#F0F8FF]/70' : 'hover:bg-[#F8FAFC]/80' }} transition-colors align-middle">
                            <!-- tombol ikon bintang (starred) -->
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('admin.forum.star', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        title="{{ $item->is_starred ? 'Lepas Sematan' : 'Sematkan ke Atas' }}"
                                        class="w-9 h-9 rounded-xl inline-flex items-center justify-center transition-all cursor-pointer {{ $item->is_starred ? 'bg-ink text-lime shadow-2xs scale-105' : 'bg-gray-100 text-gray-400 hover:bg-sky/15 hover:text-sky' }}"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $item->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </button>
                                </form>
                            </td>

                            <!-- judul & status sematan -->
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    @if ($item->is_starred)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            <span>Disematkan</span>
                                        </span>
                                    @endif
                                    <span class="rounded-full bg-sky/10 text-sky px-2.5 py-0.5 text-[10px] font-bold uppercase">
                                        {{ $item->kategori ?: 'Umum' }}
                                    </span>
                                    <span class="text-[11px] text-muted">
                                        • {{ $item->jawaban_count }} Balasan
                                    </span>
                                </div>
                                <p class="font-heading font-bold text-ink leading-snug">
                                    {{ $item->judul }}
                                </p>
                                <p class="text-[11px] text-muted line-clamp-1 mt-0.5">
                                    {{ $item->pertanyaan }}
                                </p>
                            </td>

                            <!-- penulis -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-heading font-bold text-ink truncate max-w-[115px]">{{ $authorName }}</span>
                                    @if ($isOwnerAuthor)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold shrink-0">
                                            <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                            </svg>
                                            <span>Owner</span>
                                        </span>
                                    @elseif ($isAdminAuthor)
                                        <span class="rounded-full bg-lime text-ink px-2 py-0.5 text-[9px] font-extrabold shrink-0">Admin</span>
                                    @endif
                                </div>
                            </td>

                            <!-- tanggal -->
                            <td class="py-4 px-4 text-[11px] text-muted">
                                {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                <span class="block text-[10px]">{{ $item->created_at ? $item->created_at->diffForHumans() : '' }}</span>
                            </td>

                            <!-- tombol hapus -->
                            <td class="py-4 px-4 text-right">
                                <form
                                    action="{{ route('admin.forum.destroy', $item->id) }}"
                                    method="POST"
                                    @submit.prevent="$dispatch('admin-confirm', { form: $el, title: 'Hapus Thread Forum', message: 'Yakin ingin menghapus thread diskusi ini beserta seluruh balasannya?' })"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-200 hover:border-red-300 hover:bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition-colors cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-muted">
                                Belum ada diskusi forum.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
