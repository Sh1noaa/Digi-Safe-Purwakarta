@extends('admin.dashboard')

@section('admin_content')
    <div
        x-data="{
            formOpen: false,
            isEdit: false,
            editAction: '',
            form: {
                judul: '',
                kategori: 'Panduan Publik',
                type: 'teks',
                konten: '',
                url: ''
            },
            openCreate() {
                this.isEdit = false;
                this.editAction = '{{ route('admin.edukasi.store') }}';
                this.form = {
                    judul: '',
                    kategori: 'Panduan Publik',
                    type: 'teks',
                    konten: '',
                    url: ''
                };
                this.formOpen = true;
            },
            openEdit(modul, updateUrl) {
                this.isEdit = true;
                this.editAction = updateUrl;
                this.form = {
                    judul: modul.judul || '',
                    kategori: modul.kategori || 'Panduan Publik',
                    type: modul.type || 'link',
                    konten: modul.konten || '',
                    url: modul.url || ''
                };
                this.formOpen = true;
            }
        }"
    >
        <!-- header area panel kiri & tombol tambah modul di pojok kanan atas -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-ink">
                    Manajemen Modul Edukasi
                </h1>
                <p class="mt-1 text-xs text-muted">
                    Kelola materi literasi tipe Teks Manual (pop-up bacaan) maupun Link Eksternal (jurnal & rujukan resmi).
                </p>
            </div>

            <button
                type="button"
                @click="openCreate()"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-5 py-2.5 shadow-xs hover:-translate-y-0.5 transition-all cursor-pointer shrink-0"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Tambah Modul</span>
            </button>
        </div>

        <!-- form tambah / edit modul (collapsible / modal di atas list) -->
        <div
            x-show="formOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;"
            class="mt-6 rounded-2xl bg-[#F8FAFC] border border-sky/25 p-5 sm:p-6"
        >
            <div class="flex items-center justify-between pb-4 border-b border-gray-200/80 mb-4">
                <h2 class="font-heading font-bold text-base text-ink" x-text="isEdit ? 'Edit Modul Edukasi' : 'Tambah Modul Edukasi Baru'"></h2>
                <button
                    type="button"
                    @click="formOpen = false"
                    class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-ink flex items-center justify-center cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- form simpan -->
            <form :action="editAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- toggle tipe konten -->
                <div>
                    <label class="block text-xs font-bold text-ink mb-2">Pilih Tipe Konten</label>
                    <div class="inline-flex rounded-xl bg-white border border-gray-200 p-1 gap-1">
                        <button
                            type="button"
                            @click="form.type = 'teks'"
                            :class="form.type === 'teks' ? 'bg-ink text-lime font-bold shadow-2xs' : 'text-muted hover:text-ink font-semibold'"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs transition-all cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                            </svg>
                            <span>Teks Manual</span>
                        </button>

                        <button
                            type="button"
                            @click="form.type = 'link'"
                            :class="form.type === 'link' ? 'bg-sky text-white font-bold shadow-2xs' : 'text-muted hover:text-ink font-semibold'"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs transition-all cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                            <span>Link Eksternal</span>
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="form.type">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Judul Modul</label>
                        <input
                            type="text"
                            name="judul"
                            x-model="form.judul"
                            required
                            placeholder="Contoh: Panduan Proteksi Akun WhatsApp"
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Kategori</label>
                        <input
                            type="text"
                            name="kategori"
                            x-model="form.kategori"
                            required
                            placeholder="Contoh: Panduan Publik / Jurnal Akademik / Etika Siber"
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>
                </div>

                <!-- input khusus tipe teks manual -->
                <div x-show="form.type === 'teks'">
                    <label class="block text-xs font-bold text-ink mb-1.5">Isi Konten Modul (Ditampilkan pada Pop-up Halaman Edukasi)</label>
                    <textarea
                        name="konten"
                        x-model="form.konten"
                        rows="5"
                        :required="form.type === 'teks'"
                        placeholder="Tuliskan penjelasan lengkap materi edukasi keamanan digital di sini..."
                        class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-y"
                    ></textarea>
                </div>

                <!-- input khusus tipe link eksternal -->
                <div x-show="form.type === 'link'" style="display: none;" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">URL Tautan Eksternal</label>
                        <input
                            type="url"
                            name="url"
                            x-model="form.url"
                            :required="form.type === 'link'"
                            placeholder="https://literasidigital.id/..."
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Ringkasan Singkat (Opsional)</label>
                        <input
                            type="text"
                            name="konten"
                            x-model="form.konten"
                            placeholder="Deskripsi singkat 1 kalimat untuk kartu modul..."
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        @click="formOpen = false"
                        class="rounded-full border border-gray-200 bg-white px-5 py-2 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 transition-colors cursor-pointer"
                    >
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Modul'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- daftar modul card grid -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($moduls as $modul)
                @php
                    $isTeks = $modul->effective_type === 'teks';
                    $modulPayload = [
                        'id' => $modul->id,
                        'judul' => $modul->judul,
                        'kategori' => $modul->kategori,
                        'type' => $modul->effective_type,
                        'konten' => $modul->konten,
                        'url' => $modul->effective_url,
                    ];
                @endphp

                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 flex flex-col justify-between hover:border-sky/40 transition-all">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider {{ $isTeks ? 'bg-lime text-ink' : 'bg-sky/15 text-sky' }}">
                                @if ($isTeks)
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                    <span>Teks Manual</span>
                                @else
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                        <polyline points="15 3 21 3 21 9" />
                                        <line x1="10" y1="14" x2="21" y2="3" />
                                    </svg>
                                    <span>Link Eksternal</span>
                                @endif
                            </span>

                            <span class="rounded-full bg-white border border-gray-200 px-2.5 py-0.5 text-[10px] font-bold text-muted">
                                {{ $modul->kategori ?: 'Umum' }}
                            </span>
                        </div>

                        <h3 class="font-heading font-bold text-sm sm:text-base text-ink leading-snug">
                            {{ $modul->judul }}
                        </h3>

                        <p class="mt-2 text-xs text-muted leading-relaxed line-clamp-2">
                            {{ $modul->konten }}
                        </p>

                        @if (! $isTeks && $modul->effective_url)
                            <a
                                href="{{ $modul->effective_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2.5 inline-flex items-center gap-1 text-[11px] font-semibold text-sky hover:underline truncate max-w-full"
                            >
                                <span class="truncate">{{ $modul->effective_url }}</span>
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="7" y1="17" x2="17" y2="7" />
                                    <polyline points="7 7 17 7 17 17" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    <!-- tombol aksi edit & hapus -->
                    <div class="mt-5 pt-3.5 border-t border-gray-200/70 flex items-center justify-between gap-2">
                        <span class="text-[10px] text-muted">
                            ID #{{ $modul->id }}
                        </span>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="openEdit(@js($modulPayload), '{{ route('admin.edukasi.update', $modul->id) }}')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-gray-200 hover:border-sky hover:text-sky px-3 py-1.5 text-xs font-semibold text-ink transition-colors cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                <span>Edit</span>
                            </button>

                            <form
                                action="{{ route('admin.edukasi.destroy', $modul->id) }}"
                                method="POST"
                                @submit.prevent="$dispatch('admin-confirm', { form: $el, title: 'Hapus Modul Edukasi', message: 'Yakin ingin menghapus modul edukasi ini secara permanen?' })"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-gray-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition-colors cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-200 p-10 text-center">
                    <p class="font-heading font-bold text-sm text-ink">Belum ada modul edukasi.</p>
                    <p class="text-xs text-muted mt-1">Klik tombol "Tambah Modul" di kanan atas untuk membuat materi pertama.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
