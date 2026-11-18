@extends('admin.dashboard')

@section('admin_content')
    <div
        x-data="{
            subTab: 'soal',
            formOpen: false,
            isEdit: false,
            editAction: '',
            form: {
                pertanyaan: '',
                pilihan_a: '',
                pilihan_b: '',
                pilihan_c: '',
                pilihan_d: '',
                jawaban_benar: 'A',
                game_level: 'phishing',
                tingkat: 'easy'
            },
            levelFormOpen: false,
            isEditLevel: false,
            levelEditAction: '',
            levelForm: {
                nama_level: '',
                slug: '',
                badge_label: 'LEVEL BARU',
                deskripsi: '',
                waktu_detik: 12,
                poin_benar: 20,
                penalti_salah: 5,
                hp_awal: 3,
                urutan: {{ $levels->count() + 1 }},
                warna: 'sky',
                is_active: true
            },
            openCreate() {
                this.isEdit = false;
                this.editAction = '{{ route('admin.minigame.store') }}';
                this.form = {
                    pertanyaan: '',
                    pilihan_a: '',
                    pilihan_b: '',
                    pilihan_c: '',
                    pilihan_d: '',
                    jawaban_benar: 'A',
                    game_level: '{{ $filterGame !== 'semua' ? $filterGame : 'phishing' }}',
                    tingkat: '{{ $filterLevel !== 'semua' ? $filterLevel : ($levels->first()->slug ?? 'easy') }}'
                };
                this.formOpen = true;
            },
            openEdit(soal, updateUrl) {
                this.isEdit = true;
                this.editAction = updateUrl;
                this.form = {
                    pertanyaan: soal.pertanyaan || '',
                    pilihan_a: soal.pilihan_a || '',
                    pilihan_b: soal.pilihan_b || '',
                    pilihan_c: soal.pilihan_c || '',
                    pilihan_d: soal.pilihan_d || '',
                    jawaban_benar: soal.jawaban_benar || 'A',
                    game_level: soal.game_level || 'phishing',
                    tingkat: soal.tingkat || 'easy'
                };
                this.formOpen = true;
            },
            openCreateLevel() {
                this.isEditLevel = false;
                this.levelEditAction = '{{ route('admin.minigame.level.store') }}';
                this.levelForm = {
                    nama_level: '',
                    slug: '',
                    badge_label: 'LEVEL {{ $levels->count() + 1 }} • CUSTOM',
                    deskripsi: '',
                    waktu_detik: 10,
                    poin_benar: 25,
                    penalti_salah: 8,
                    hp_awal: 3,
                    urutan: {{ $levels->count() + 1 }},
                    warna: 'sky',
                    is_active: true
                };
                this.levelFormOpen = true;
            },
            openEditLevel(lvl, updateUrl) {
                this.isEditLevel = true;
                this.levelEditAction = updateUrl;
                this.levelForm = {
                    nama_level: lvl.nama_level || '',
                    slug: lvl.slug || '',
                    badge_label: lvl.badge_label || 'LEVEL',
                    deskripsi: lvl.deskripsi || '',
                    waktu_detik: lvl.waktu_detik || 15,
                    poin_benar: lvl.poin_benar || 10,
                    penalti_salah: lvl.penalti_salah || 5,
                    hp_awal: lvl.hp_awal || 3,
                    urutan: lvl.urutan || 1,
                    warna: lvl.warna || 'emerald',
                    is_active: !!lvl.is_active
                };
                this.levelFormOpen = true;
            }
        }"
    >
        <!-- Header & Switcher Sub-Tab (Bank Soal vs Kelola Level) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-ink">
                    Manajemen Soal &amp; Level Minigame
                </h1>
                <p class="mt-1 text-xs text-muted">
                    Kelola tingkatan level kesulitan dan bank soal untuk Phishing Hunter, Cyber Defender, serta Link or Trap.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <div class="inline-flex rounded-full bg-[#F8FAFC] border border-gray-200 p-1">
                    <button
                        type="button"
                        @click="subTab = 'soal'"
                        :class="subTab === 'soal' ? 'bg-ink text-white shadow-2xs' : 'text-muted hover:text-ink'"
                        class="rounded-full px-4 py-1.5 text-xs font-heading font-bold transition-all cursor-pointer"
                    >
                        Bank Soal ({{ $soals->count() }})
                    </button>
                    <button
                        type="button"
                        @click="subTab = 'level'"
                        :class="subTab === 'level' ? 'bg-ink text-white shadow-2xs' : 'text-muted hover:text-ink'"
                        class="rounded-full px-4 py-1.5 text-xs font-heading font-bold transition-all cursor-pointer"
                    >
                        Kelola Level ({{ $levels->count() }})
                    </button>
                </div>

                <button
                    x-show="subTab === 'soal'"
                    type="button"
                    @click="openCreate()"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-5 py-2.5 shadow-xs hover:-translate-y-0.5 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Soal</span>
                </button>

                <button
                    x-show="subTab === 'level'"
                    style="display: none;"
                    type="button"
                    @click="openCreateLevel()"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-lime hover:bg-sky text-ink hover:text-white font-heading font-extrabold text-xs px-5 py-2.5 shadow-xs hover:-translate-y-0.5 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Level Baru</span>
                </button>
            </div>
        </div>

        <!-- Ringkasan Cepat Level Aktif (Klik untuk filter atau edit) -->
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
            @foreach ($levels as $lvlItem)
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-3 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="inline-block rounded-full border px-2 py-0.5 text-[9px] font-extrabold uppercase {{ $lvlItem->badge_classes }}">
                            {{ $lvlItem->badge_label }}
                        </span>
                        @if (! $lvlItem->is_active)
                            <span class="text-[9px] font-bold text-gray-400">Nonaktif</span>
                        @endif
                    </div>
                    <div class="mt-2">
                        <p class="font-heading font-bold text-xs text-ink truncate">{{ $lvlItem->nama_level }}</p>
                        <p class="text-[10px] text-muted mt-0.5">{{ $lvlItem->waktu_detik }}d • +{{ $lvlItem->poin_benar }} pts • {{ $levelCounts[$lvlItem->slug] ?? 0 }} soal</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sub-tab: Manajemen Level Minigame -->
        <div x-show="subTab === 'level'" style="display: none;" class="mt-6 space-y-5">

            <!-- Form Tambah / Edit Level -->
            <div
                x-show="levelFormOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;"
                class="rounded-2xl bg-[#F8FAFC] border border-sky/30 p-5 sm:p-6"
            >
                <div class="flex items-center justify-between pb-4 border-b border-gray-200/80 mb-4">
                    <div>
                        <h2 class="font-heading font-bold text-base text-ink" x-text="isEditLevel ? 'Edit Konfigurasi Level Minigame' : 'Tambah Level Minigame Baru'"></h2>
                        <p class="text-[11px] text-muted">Atur durasi waktu, poin jawaban benar, penalti, dan nyawa awal untuk level ini.</p>
                    </div>
                    <button
                        type="button"
                        @click="levelFormOpen = false"
                        class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-ink flex items-center justify-center cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="levelEditAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEditLevel">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Nama Level</label>
                            <input
                                type="text"
                                name="nama_level"
                                x-model="levelForm.nama_level"
                                required
                                placeholder="Contoh: Master Siber"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Label Badge</label>
                            <input
                                type="text"
                                name="badge_label"
                                x-model="levelForm.badge_label"
                                required
                                placeholder="Contoh: LEVEL 6 • LEGEND"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs font-semibold text-ink uppercase focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Warna Tema Badge</label>
                            <select
                                name="warna"
                                x-model="levelForm.warna"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs font-semibold text-ink focus:outline-none focus:border-sky"
                            >
                                <option value="emerald">Hijau (Emerald)</option>
                                <option value="sky">Biru (Sky)</option>
                                <option value="amber">Kuning/Oranye (Amber)</option>
                                <option value="purple">Ungu (Purple)</option>
                                <option value="red">Merah (Red)</option>
                                <option value="lime">Lime Neon</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Waktu / Soal (Detik)</label>
                            <input
                                type="number"
                                name="waktu_detik"
                                x-model="levelForm.waktu_detik"
                                min="3"
                                max="120"
                                required
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-bold text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Poin Jawaban Benar</label>
                            <input
                                type="number"
                                name="poin_benar"
                                x-model="levelForm.poin_benar"
                                min="5"
                                max="200"
                                required
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-bold text-emerald-700 focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Penalti Salah</label>
                            <input
                                type="number"
                                name="penalti_salah"
                                x-model="levelForm.penalti_salah"
                                min="0"
                                max="100"
                                required
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-bold text-red-600 focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">HP Awal (Defender)</label>
                            <input
                                type="number"
                                name="hp_awal"
                                x-model="levelForm.hp_awal"
                                min="1"
                                max="10"
                                required
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-bold text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Urutan Tampil</label>
                            <input
                                type="number"
                                name="urutan"
                                x-model="levelForm.urutan"
                                min="1"
                                max="99"
                                required
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-bold text-ink focus:outline-none focus:border-sky"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Deskripsi Tantangan Level</label>
                        <textarea
                            name="deskripsi"
                            x-model="levelForm.deskripsi"
                            rows="2"
                            required
                            placeholder="Jelaskan karakteristik tantangan pada level ini..."
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-none"
                        ></textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-ink cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                x-model="levelForm.is_active"
                                class="rounded border-gray-300 text-sky focus:ring-sky"
                            >
                            <span>Aktifkan level ini agar bisa dimainkan oleh user</span>
                        </label>

                        <div class="flex items-center gap-2.5">
                            <button
                                type="button"
                                @click="levelFormOpen = false"
                                class="rounded-full border border-gray-200 bg-white px-5 py-2 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 transition-colors cursor-pointer"
                            >
                                <span x-text="isEditLevel ? 'Simpan Perubahan Level' : 'Tambahkan Level'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Level Minigame -->
            <div class="overflow-x-auto rounded-2xl border border-gray-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-gray-200/80 text-[11px] font-heading font-bold uppercase tracking-wider text-muted">
                            <th class="py-3.5 px-4">Level &amp; Deskripsi</th>
                            <th class="py-3.5 px-4 w-44">Parameter Permainan</th>
                            <th class="py-3.5 px-4 w-28 text-center">Status</th>
                            <th class="py-3.5 px-4 w-36 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach ($levels as $lvl)
                            @php
                                $lvlPayload = [
                                    'nama_level' => $lvl->nama_level,
                                    'slug' => $lvl->slug,
                                    'badge_label' => $lvl->badge_label,
                                    'deskripsi' => $lvl->deskripsi,
                                    'waktu_detik' => $lvl->waktu_detik,
                                    'poin_benar' => $lvl->poin_benar,
                                    'penalti_salah' => $lvl->penalti_salah,
                                    'hp_awal' => $lvl->hp_awal,
                                    'urutan' => $lvl->urutan,
                                    'warna' => $lvl->warna,
                                    'is_active' => (bool) $lvl->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors align-top">
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase {{ $lvl->badge_classes }}">
                                            {{ $lvl->badge_label }}
                                        </span>
                                        <span class="font-heading font-bold text-sm text-ink">{{ $lvl->nama_level }}</span>
                                        <span class="text-[10px] font-mono text-muted">({{ $lvl->slug }})</span>
                                    </div>
                                    <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                        {{ $lvl->deskripsi }}
                                    </p>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex items-center justify-between">
                                            <span class="text-muted">Timer Soal:</span>
                                            <strong class="text-ink">{{ $lvl->waktu_detik }} detik</strong>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-muted">Poin / Penalti:</span>
                                            <span><strong class="text-emerald-600">+{{ $lvl->poin_benar }}</strong> / <strong class="text-red-500">-{{ $lvl->penalti_salah }}</strong></span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-muted">HP Defender:</span>
                                            <strong class="text-ink">{{ $lvl->hp_awal }} HP</strong>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    @if ($lvl->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 border border-gray-200 px-2.5 py-1 text-[10px] font-bold text-muted">
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                    <span class="block text-[10px] text-muted mt-1">{{ $levelCounts[$lvl->slug] ?? 0 }} Soal DB</span>
                                </td>

                                <td class="py-4 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEditLevel(@js($lvlPayload), '{{ route('admin.minigame.level.update', $lvl->id) }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-200 hover:border-sky hover:text-sky px-2.5 py-1.5 text-xs font-semibold text-ink transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            <span>Edit</span>
                                        </button>

                                        <form
                                            action="{{ route('admin.minigame.level.destroy', $lvl->id) }}"
                                            method="POST"
                                            @submit.prevent="$dispatch('admin-confirm', { form: $el, title: 'Hapus Level Minigame', message: 'Yakin ingin menghapus level {{ addslashes($lvl->nama_level) }}?' })"
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sub-tab: Manajemen Bank Soal Minigame -->
        <div x-show="subTab === 'soal'">

            <!-- Bar Filter Game & Level -->
            <form method="GET" action="{{ route('admin.minigame.index') }}" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-3.5">
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-ink">Game:</label>
                        <select
                            name="game"
                            onchange="this.form.submit()"
                            class="rounded-xl bg-white border border-gray-200 px-3 py-1.5 text-xs font-semibold text-ink focus:outline-none focus:border-sky"
                        >
                            <option value="semua" {{ $filterGame === 'semua' ? 'selected' : '' }}>Semua Game</option>
                            <option value="phishing" {{ $filterGame === 'phishing' ? 'selected' : '' }}>Phishing Hunter</option>
                            <option value="cyber-defender" {{ $filterGame === 'cyber-defender' ? 'selected' : '' }}>Cyber Defender</option>
                            <option value="link-trap" {{ $filterGame === 'link-trap' ? 'selected' : '' }}>Link or Trap</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-ink">Level:</label>
                        <select
                            name="level"
                            onchange="this.form.submit()"
                            class="rounded-xl bg-white border border-gray-200 px-3 py-1.5 text-xs font-semibold text-ink focus:outline-none focus:border-sky"
                        >
                            <option value="semua" {{ $filterLevel === 'semua' ? 'selected' : '' }}>Semua Level ({{ $levels->count() }} Level)</option>
                            @foreach ($levels as $lvlOption)
                                <option value="{{ $lvlOption->slug }}" {{ $filterLevel === $lvlOption->slug ? 'selected' : '' }}>
                                    {{ $lvlOption->badge_label }} — {{ $lvlOption->nama_level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <span class="text-xs font-semibold text-muted">
                    Menampilkan <strong class="text-ink">{{ $soals->count() }}</strong> soal
                </span>
            </form>

            <!-- Form Tambah / Edit Soal -->
            <div
                x-show="formOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;"
                class="mt-5 rounded-2xl bg-[#F8FAFC] border border-sky/25 p-5 sm:p-6"
            >
                <div class="flex items-center justify-between pb-4 border-b border-gray-200/80 mb-4">
                    <h2 class="font-heading font-bold text-base text-ink" x-text="isEdit ? 'Edit Soal Minigame' : 'Tambah Soal Minigame Baru'"></h2>
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

                <form :action="editAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Pilih Game</label>
                            <select
                                name="game_level"
                                x-model="form.game_level"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs font-semibold text-ink focus:outline-none focus:border-sky"
                            >
                                <option value="phishing">Phishing Hunter</option>
                                <option value="cyber-defender">Cyber Defender</option>
                                <option value="link-trap">Link or Trap</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Level Kesulitan</label>
                            <select
                                name="tingkat"
                                x-model="form.tingkat"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs font-semibold text-ink focus:outline-none focus:border-sky"
                            >
                                @foreach ($levels as $lvlOpt)
                                    <option value="{{ $lvlOpt->slug }}">
                                        {{ $lvlOpt->badge_label }} ({{ $lvlOpt->nama_level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">Jawaban Benar</label>
                            <select
                                name="jawaban_benar"
                                x-model="form.jawaban_benar"
                                class="w-full rounded-xl bg-lime/35 border border-lime px-3.5 py-2.5 text-xs font-extrabold text-ink focus:outline-none focus:border-ink"
                            >
                                <option value="A">Pilihan A</option>
                                <option value="B">Pilihan B</option>
                                <option value="C">Pilihan C</option>
                                <option value="D">Pilihan D</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Pertanyaan / Kasus Siber</label>
                        <textarea
                            name="pertanyaan"
                            x-model="form.pertanyaan"
                            rows="3"
                            required
                            placeholder="Tuliskan skenario pertanyaan di sini..."
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-none"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Pilihan A</label>
                            <input
                                type="text"
                                name="pilihan_a"
                                x-model="form.pilihan_a"
                                required
                                placeholder="Opsi jawaban A..."
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Pilihan B</label>
                            <input
                                type="text"
                                name="pilihan_b"
                                x-model="form.pilihan_b"
                                required
                                placeholder="Opsi jawaban B..."
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Pilihan C</label>
                            <input
                                type="text"
                                name="pilihan_c"
                                x-model="form.pilihan_c"
                                required
                                placeholder="Opsi jawaban C..."
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">Pilihan D</label>
                            <input
                                type="text"
                                name="pilihan_d"
                                x-model="form.pilihan_d"
                                required
                                placeholder="Opsi jawaban D..."
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky"
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
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Soal'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Soal Minigame -->
            <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-gray-200/80 text-[11px] font-heading font-bold uppercase tracking-wider text-muted">
                            <th class="py-3.5 px-4">Pertanyaan &amp; Opsi</th>
                            <th class="py-3.5 px-4 w-40">Game &amp; Level</th>
                            <th class="py-3.5 px-4 w-24 text-center">Kunci</th>
                            <th class="py-3.5 px-4 w-36 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @php
                            $levelsBySlug = $levels->keyBy('slug');
                        @endphp
                        @forelse ($soals as $soal)
                            @php
                                $opsi = $soal->opsi;
                                $kunci = $soal->kunci_huruf;
                                $lvl = $soal->level_normalize;
                                $lvlObj = $levelsBySlug->get($lvl);
                                $lvlBadge = $lvlObj ? $lvlObj->badge_classes : match ($lvl) {
                                    'master', 'hard' => 'bg-red-50 text-red-600 border-red-200',
                                    'expert' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'medium' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                };
                                $lvlText = $lvlObj ? $lvlObj->badge_label : strtoupper($lvl);
                                $soalPayload = [
                                    'pertanyaan' => $soal->pertanyaan,
                                    'pilihan_a' => $opsi['A'],
                                    'pilihan_b' => $opsi['B'],
                                    'pilihan_c' => $opsi['C'],
                                    'pilihan_d' => $opsi['D'],
                                    'jawaban_benar' => $kunci,
                                    'game_level' => $soal->game_slug,
                                    'tingkat' => $lvl,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors align-top">
                                <td class="py-4 px-4">
                                    <p class="font-heading font-bold text-ink leading-snug">
                                        {{ $soal->pertanyaan }}
                                    </p>
                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] text-muted">
                                        @foreach (['A', 'B', 'C', 'D'] as $letter)
                                            <div class="rounded-lg px-2.5 py-1 {{ $kunci === $letter ? 'bg-lime/30 text-ink font-semibold border border-lime' : 'bg-gray-50' }}">
                                                <span class="font-bold">{{ $letter }}.</span> {{ $opsi[$letter] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <span class="font-heading font-bold text-ink block">{{ $soal->game_label }}</span>
                                    <span class="mt-1 inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $lvlBadge }}">
                                        {{ $lvlText }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex w-8 h-8 rounded-xl bg-ink text-lime font-heading font-extrabold text-xs items-center justify-center">
                                        {{ $kunci }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEdit(@js($soalPayload), '{{ route('admin.minigame.update', $soal->id) }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-200 hover:border-sky hover:text-sky px-2.5 py-1.5 text-xs font-semibold text-ink transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            <span>Edit</span>
                                        </button>

                                        <form
                                            action="{{ route('admin.minigame.destroy', $soal->id) }}"
                                            method="POST"
                                            @submit.prevent="$dispatch('admin-confirm', { form: $el, title: 'Hapus Soal Minigame', message: 'Yakin ingin menghapus soal minigame ini?' })"
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-muted">
                                    Tidak ada soal untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
