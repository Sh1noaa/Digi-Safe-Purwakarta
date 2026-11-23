@extends('layouts.app')

@section('title', 'Tentang Kami - Digi-Safe Purwakarta')

@section('content')

    <!-- hero section bergaya dokumen editor & floating command bar -->
    <section
        class="about-hero-surface relative pt-28 sm:pt-36 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden"
    >
        <!-- aksen organik lembut di sudut seperti referensi (khusus md+ agar GPU mobile tetap ringan) -->
        <div class="hidden md:block about-hero-orb-1 absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#A5D6A7]/35 blur-3xl pointer-events-none"></div>
        <div class="hidden md:block about-hero-orb-2 absolute -bottom-28 -right-20 w-[420px] h-[420px] rounded-full bg-[#81C784]/40 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto">

            <!-- tab header nama file di kiri atas dokumen -->
            <div class="reveal inline-flex items-center gap-2.5 rounded-t-[20px] bg-white px-6 pt-4 pb-2.5 border-t border-x border-gray-200/80">
                <span class="w-6 h-6 rounded-md bg-sky/15 text-sky flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                </span>
                <span class="font-heading font-bold text-xs sm:text-sm text-ink tracking-tight">
                    {{ __('ui.about.doc_title') }}
                </span>
            </div>

            <!-- container putih dokumen editor -->
            <div
                class="reveal rounded-b-[24px] rounded-tr-[24px] bg-white p-6 sm:p-9 pb-14 border border-gray-200/80 shadow-neutral"
            >
                <!-- toolbar dekoratif editor -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-6 border-b border-gray-100 text-xs text-ink/75 select-none">
                    <div class="flex items-center gap-3">
                        <!-- undo & redo -->
                        <span class="p-1 text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7v6h6" />
                                <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13" />
                            </svg>
                        </span>
                        <span class="p-1 text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 7v6h-6" />
                                <path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7" />
                            </svg>
                        </span>

                        <span class="h-4 w-px bg-gray-200 mx-0.5"></span>

                        <!-- dropdown font & ukuran -->
                        <span class="inline-flex items-center gap-1 font-medium text-ink">
                            <span>Arial</span>
                            <svg class="w-3 h-3 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>

                        <span class="inline-flex items-center gap-1 font-medium text-ink ml-1">
                            <span>14 pt</span>
                            <svg class="w-3 h-3 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <!-- align, bold, italic, underline -->
                        <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="15" y2="12" />
                            <line x1="3" y1="18" x2="18" y2="18" />
                        </svg>
                        <span class="font-bold text-ink">B</span>
                        <span class="italic font-serif text-ink">I</span>
                        <span class="underline text-ink">U</span>

                        <span class="h-4 w-px bg-gray-200 mx-0.5"></span>

                        <span class="inline-flex items-center gap-1.5 font-medium text-ink">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                            </svg>
                            <span>Edit</span>
                        </span>
                    </div>
                </div>

                <!-- isi dokumen -->
                <div>
                    <h1 class="font-heading font-bold text-lg sm:text-xl text-ink mb-4">
                        {{ __('ui.about.heading') }}
                    </h1>

                    <p class="text-sm sm:text-[15px] text-ink/75 leading-[1.9] tracking-[-0.01em]">
                        {!! __('ui.about.body_html') !!}
                    </p>
                </div>
            </div>

            <!-- floating command bar glassmorphism di bawah dokumen -->
            <div
                class="about-command-bar reveal reveal-delay-1 -mt-8 relative z-20 max-w-md mx-auto rounded-[28px] p-5 border border-white"
            >
                <div class="flex items-center text-sm sm:text-base font-medium text-ink px-1">
                    <span>{{ __('ui.about.prompt') }}</span>
                    <span class="inline-block w-[2px] h-5 bg-sky ml-1 animate-pulse"></span>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-9 h-9 rounded-full bg-white text-ink flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                        </span>
                        <span class="w-9 h-9 rounded-full bg-white text-ink flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                        </span>
                    </div>

                    <!-- tombol bulat hitam panah ke bawah -->
                    <a
                        href="#section-fakta-siber"
                        class="w-10 h-10 rounded-full bg-ink hover:bg-sky text-white flex items-center justify-center shadow-sm hover:scale-105 transition-all"
                        aria-label="{{ app()->getLocale() === 'en' ? 'Scroll to Cyber Facts & Data' : 'Scroll ke Fakta & Data Siber' }}"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <polyline points="19 12 12 19 5 12" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </section>

    @php
        $isEn = app()->getLocale() === 'en';
    @endphp

    <!-- section fakta & data ancaman siber (sebelum tentang pengembang) -->
    <section
        id="section-fakta-siber"
        aria-labelledby="fakta-siber-heading"
        x-data="{
            datasets: @js($cyberFacts['datasets']),
            activeIndex: 0,
            get current() {
                return this.datasets[this.activeIndex] || this.datasets[0];
            }
        }"
        class="about-intel-section bg-white border-b border-gray-100"
    >
        <!-- BAGIAN 0: JUDUL STIKER 3D BERGAYA EDITOR VEKTOR ("Mengapa kami membuat website ini") -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 sm:pt-20 pb-2">
            <div class="reveal flex justify-center">
                <div class="about-sticker-stage relative inline-flex flex-col items-center select-none py-4 px-3 sm:px-10 max-w-full">
                    <!-- Aksen Palet Warna Mini Digi-Safe di Atas (Sesuai Referensi) -->
                    <div class="hidden sm:flex items-center gap-0.5 mb-2 ml-20 rounded-sm overflow-hidden shadow-xs" aria-hidden="true">
                        <span class="w-4 h-2.5 bg-[#38BDF8]"></span>
                        <span class="w-4 h-2.5 bg-[#1E88E5]"></span>
                        <span class="w-4 h-2.5 bg-[#1E63E9]"></span>
                        <span class="w-4 h-2.5 bg-[#C6F432]"></span>
                    </div>

                    <!-- Baris 1: Kotak Seleksi Vektor Miring + Tag Lime + Kata Stiker 3D Pertama -->
                    <div class="relative flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                        <!-- Kotak Bounding-Box Seleksi dengan 4 Handle Sudut -->
                        <div class="relative -rotate-[5deg] -mr-1 sm:-mr-3 -mt-6 sm:-mt-10 z-20">
                            <span class="about-sticker-bbox relative inline-block px-3 sm:px-4 py-1 sm:py-1.5 font-heading font-bold text-xs sm:text-base tracking-tight">
                                {{ $isEn ? 'Why we' : 'Mengapa kami' }}
                                <span class="about-sticker-handle -top-1 -left-1" aria-hidden="true"></span>
                                <span class="about-sticker-handle -top-1 -right-1" aria-hidden="true"></span>
                                <span class="about-sticker-handle -bottom-1 -left-1" aria-hidden="true"></span>
                                <span class="about-sticker-handle -bottom-1 -right-1" aria-hidden="true"></span>
                            </span>
                            <!-- Tag Miring Lime Bertumpuk di Sudut Bawah Kiri -->
                            <span class="about-sticker-tag absolute -bottom-5 left-3 sm:left-4 rotate-[9deg] inline-block px-2 sm:px-2.5 py-0.5 rounded-[4px] font-heading font-extrabold text-[11px] sm:text-sm shadow-sm">
                                {{ $isEn ? 'built' : 'membuat' }}
                            </span>
                        </div>

                        <!-- Teks Stiker 3D Utama Baris 1 -->
                        <span class="about-sticker-word font-heading font-extrabold text-[2.35rem] sm:text-[3.75rem] lg:text-[4.4rem] leading-[1.02] tracking-tight" data-text="{{ $isEn ? 'This' : 'Website' }}">
                            {{ $isEn ? 'This' : 'Website' }}
                        </span>
                    </div>

                    <!-- Baris 2: Kata Stiker 3D Kedua (Geser Kanan + Aksen Pena Vektor & Kursor) -->
                    <div class="relative flex items-center justify-center mt-1 sm:-mt-2 sm:pl-24">
                        <!-- Aksen Pen Tool Bezier (Khas Referensi Graphic Design) -->
                        <svg
                            class="hidden sm:block w-14 h-12 -mr-2 -mt-5 about-sticker-bezier shrink-0"
                            viewBox="0 0 64 52"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path d="M6 42 C14 14, 46 14, 56 40" stroke="currentColor" stroke-width="2" stroke-dasharray="3 3" />
                            <line x1="18" y1="16" x2="46" y2="16" stroke="currentColor" stroke-width="1.6" />
                            <rect x="15" y="13" width="6" height="6" fill="#C6F432" stroke="currentColor" stroke-width="1.5" />
                            <rect x="43" y="13" width="6" height="6" fill="#C6F432" stroke="currentColor" stroke-width="1.5" />
                            <circle cx="32" cy="16" r="3.5" fill="#1E63E9" stroke="currentColor" stroke-width="1.5" />
                        </svg>

                        <span class="about-sticker-word font-heading font-extrabold text-[2.35rem] sm:text-[3.75rem] lg:text-[4.4rem] leading-[1.02] tracking-tight" data-text="{{ $isEn ? 'Website' : 'Ini' }}">
                            {{ $isEn ? 'Website' : 'Ini' }}
                        </span>

                        <!-- Aksen Kartu Swatch Mini Digi-Safe di Kanan -->
                        <div class="hidden sm:flex items-end -space-x-2 ml-3 -mt-4" aria-hidden="true">
                            <span class="w-4 h-9 rounded-[4px] bg-[#1E63E9] border-2 border-white -rotate-12 shadow-xs"></span>
                            <span class="w-4 h-9 rounded-[4px] bg-[#38BDF8] border-2 border-white rotate-0 shadow-xs"></span>
                            <span class="w-4 h-9 rounded-[4px] bg-[#C6F432] border-2 border-white rotate-12 shadow-xs"></span>
                        </div>
                    </div>

                    <!-- Tag Kecil Brand di Bawah (Menyerupai Pill Kecil di Referensi) -->
                    <span class="about-sticker-chip mt-2 sm:mt-1 inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-[11px] font-heading font-bold">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt=""
                            width="14"
                            height="14"
                            aria-hidden="true"
                            class="w-3.5 h-3.5 object-contain"
                        >
                        <span>digisafepurwakarta.web.id</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- BAGIAN 1: SPLIT HERO FAKTA & MONITOR DATA INTERAKTIF -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-14 sm:pt-12 sm:pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                <!-- Kolom Kiri: Identitas Digi-Safe + Tipografi Garis Bawah Biru & Lingkaran Aksen -->
                <div class="reveal lg:col-span-5 space-y-5">
                    <div class="inline-flex items-center gap-2.5">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Logo Digi-Safe Purwakarta"
                            width="32"
                            height="32"
                            class="w-8 h-8 object-contain shrink-0"
                        >
                        <span class="about-intel-muted text-xs font-heading font-bold tracking-tight">
                            {{ $isEn ? 'Digi-Safe Purwakarta · Verified Field Data' : 'Digi-Safe Purwakarta · Fakta & Data Resmi' }}
                        </span>
                    </div>

                    <h2
                        id="fakta-siber-heading"
                        class="about-intel-heading font-heading font-extrabold text-[2rem] sm:text-[2.75rem] lg:text-[3.1rem] leading-[1.08] tracking-tight"
                    >
                        <span class="inline-flex items-center gap-2.5 flex-wrap">
                            <span>{{ $isEn ? 'Protect' : 'Lindungi' }}</span>
                            <!-- Motif Lingkaran Ganda Tinta & Lime (Adaptasi Identitas Digi-Safe) -->
                            <span class="inline-flex items-center -space-x-2.5 align-middle" aria-hidden="true">
                                <span class="about-intel-ink-dot w-8 h-8 sm:w-10 sm:h-10 rounded-full inline-block"></span>
                                <span class="about-intel-lime-dot w-8 h-8 sm:w-10 sm:h-10 rounded-full inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                        <polyline points="16 7 22 7 22 13" />
                                    </svg>
                                </span>
                            </span>
                        </span>
                        <span class="block mt-1">
                            <span>{{ $isEn ? 'Local' : 'Ruang' }}</span>
                            <span class="relative inline-block about-intel-blue-word pb-1.5">
                                <span>{{ $isEn ? 'Digital' : 'Digital' }}</span>
                                <svg
                                    class="absolute left-0 -bottom-0.5 w-full h-2.5 pointer-events-none"
                                    viewBox="0 0 180 12"
                                    fill="none"
                                    preserveAspectRatio="none"
                                    aria-hidden="true"
                                >
                                    <path d="M3 8.5C48 2.5 128 2 177 7.5" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" />
                                </svg>
                            </span>
                        </span>
                        <span class="block mt-0.5">
                            {{ $isEn ? 'Safety' : 'Purwakarta' }}
                        </span>
                    </h2>

                    <p class="about-intel-body text-sm sm:text-[15px] leading-relaxed max-w-md">
                        {{ $isEn
                            ? 'Financial fraud and malicious .APK files now target everyday families across Indonesia. Official records from OJK, BSSN, and APJII show that checking links and messages before acting stops most losses.'
                            : 'Penipuan transaksi keuangan dan kiriman file .APK kini menyasar warga hingga tingkat keluarga. Catatan resmi OJK, BSSN, dan APJII menunjukkan kebiasaan memeriksa tautan dan pesan sebelum bertindak mampu mencegah sebagian besar kerugian.' }}
                    </p>

                    <!-- Rujukan Sumber Resmi Ringkas -->
                    <div class="pt-1 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold about-intel-muted">
                        <span>{{ $isEn ? 'Data sources:' : 'Sumber data:' }}</span>
                        <span class="about-intel-source-tag">OJK IASC</span>
                        <span class="about-intel-source-tag">BSSN 2025</span>
                        <span class="about-intel-source-tag">APJII 2025</span>
                    </div>
                </div>

                <!-- Kolom Kanan: Bingkai Panel Analitik Berlapis (Interaktif 3 Lembaga) -->
                <div class="reveal reveal-delay-1 lg:col-span-7">
                    <div class="about-intel-frame relative rounded-[26px] border p-4 sm:p-6">
                        <!-- Bar Atas Jendela + Logo Digi-Safe -->
                        <div class="flex items-center justify-between gap-3 pb-4 mb-4 border-b about-intel-divider">
                            <div class="flex items-center gap-1.5" aria-hidden="true">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            </div>
                            <div class="inline-flex items-center gap-2">
                                <img
                                    src="{{ asset('images/logo.png') }}"
                                    alt=""
                                    width="20"
                                    height="20"
                                    aria-hidden="true"
                                    class="w-5 h-5 object-contain"
                                >
                                <span class="text-[11px] font-heading font-bold about-intel-muted" x-text="current.source"></span>
                            </div>
                        </div>

                        <!-- Baris Atas: Mini Kartu Data Kiri + Kartu Biru Miring Mengambang di Kanan -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center mb-5">
                            <div class="sm:col-span-6 about-intel-subcard rounded-2xl border p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-heading font-bold about-intel-muted" x-text="current.card_tag"></span>
                                    <span class="w-6 h-6 rounded-lg about-intel-icon-box inline-flex items-center justify-center shrink-0" aria-hidden="true">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="mt-2 font-heading font-extrabold text-xl sm:text-2xl about-intel-heading" x-text="current.card_metric"></p>
                                <p class="mt-0.5 text-[11px] about-intel-muted leading-snug" x-text="current.card_sub"></p>
                            </div>

                            <!-- Kartu Biru Miring Mengambang (Menyerupai Kartu Biru Referensi) -->
                            <div class="sm:col-span-6">
                                <div class="about-intel-blue-card rounded-[20px] p-4 sm:p-5 sm:rotate-[2.5deg] sm:hover:rotate-0 transition-transform duration-300 shadow-lg">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-7 h-7 rounded-full bg-white/95 p-1 flex items-center justify-center shrink-0">
                                                <img
                                                    src="{{ asset('images/logo.png') }}"
                                                    alt="Digi-Safe"
                                                    width="20"
                                                    height="20"
                                                    class="w-full h-full object-contain"
                                                >
                                            </span>
                                            <span class="text-xs font-heading font-bold text-white truncate" x-text="current.float_label"></span>
                                        </div>
                                        <span class="w-7 h-7 rounded-full bg-white/20 text-white inline-flex items-center justify-center shrink-0" aria-hidden="true">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-baseline gap-2 flex-wrap">
                                        <span class="font-heading font-extrabold text-2xl sm:text-[1.7rem] text-white leading-none" x-text="current.float_value"></span>
                                        <span class="text-xs font-medium text-blue-100" x-text="current.float_unit"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris Tengah: Angka Utama + 3 Tombol Pengalih Lembaga (Interaktif Penuh) -->
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-5 mb-5 border-b about-intel-divider">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold about-intel-muted" x-text="current.headline_label"></p>
                                <p class="mt-1 font-heading font-extrabold text-3xl sm:text-4xl tracking-tight about-intel-heading" x-text="current.headline_value"></p>
                                <p class="mt-1.5 text-xs about-intel-muted leading-relaxed max-w-sm" x-text="current.headline_note"></p>
                            </div>

                            <!-- 3 Tombol Pengalih Data (OJK / BSSN / APJII) -->
                            <div class="flex items-center gap-2 shrink-0" role="group" aria-label="{{ $isEn ? 'Select data source' : 'Pilih sumber data resmi' }}">
                                <template x-for="(ds, idx) in datasets" :key="ds.key">
                                    <button
                                        type="button"
                                        @click="activeIndex = idx"
                                        :aria-pressed="activeIndex === idx"
                                        :class="activeIndex === idx ? 'about-intel-tab-active' : 'about-intel-tab'"
                                        class="min-w-[52px] min-h-[44px] px-3 py-2 rounded-xl font-heading font-bold text-xs transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky"
                                        x-text="ds.short"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        <!-- Grafik Batang Perbandingan Data -->
                        <div class="pt-1">
                            <div class="grid grid-cols-5 gap-2 sm:gap-3 items-end h-36 sm:h-40 pt-7">
                                <template x-for="(bar, bIdx) in current.bars" :key="current.key + '-' + bIdx">
                                    <div class="flex flex-col items-center justify-end h-full gap-1.5 min-w-0">
                                        <span
                                            class="text-[10px] font-heading font-bold px-2 py-0.5 rounded-md whitespace-nowrap"
                                            :class="bar.active ? 'about-intel-pill-active' : 'about-intel-muted'"
                                            x-text="bar.value"
                                        ></span>
                                        <div
                                            class="w-full max-w-[44px] rounded-t-xl transition-all duration-500"
                                            :class="bar.active ? 'about-intel-bar-active' : 'about-intel-bar'"
                                            :style="'height:' + Math.max(18, bar.pct) + '%'"
                                        ></div>
                                        <span class="text-[10px] sm:text-[11px] font-medium about-intel-muted truncate max-w-full" x-text="bar.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- BAGIAN 2: PITA 3 TOLOK UKUR FAKTA RESMI (PENGGANTI BAR RATING PALSU) -->
        <div class="about-intel-strip border-y py-8 sm:py-10">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs sm:text-sm font-heading font-bold about-intel-heading mb-6">
                    @if ($isEn)
                        <span class="about-intel-blue-word">Verified national data</span> behind Digi-Safe Purwakarta
                    @else
                        <span class="about-intel-blue-word">Fakta resmi nasional</span> yang melandasi Digi-Safe Purwakarta
                    @endif
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                    @foreach ($cyberFacts['benchmarks'] as $bm)
                        <div class="flex items-start sm:items-center gap-3.5 md:justify-center">
                            <span class="font-heading font-extrabold text-2xl sm:text-3xl tracking-tight about-intel-heading shrink-0">
                                {{ $bm['value'] }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-heading font-bold text-xs about-intel-blue-word leading-tight">
                                    {{ $bm['org'] }}
                                </p>
                                <p class="text-xs about-intel-muted leading-snug mt-0.5">
                                    {{ $bm['desc'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: JUDUL TENGAH + KARTU ANALITIK ASIMETRIS DENGAN BANNER LIME MIRING -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="reveal text-center max-w-2xl mx-auto mb-10 sm:mb-12">
                <h2 class="about-intel-heading font-heading font-extrabold text-2xl sm:text-4xl leading-tight tracking-tight">
                    @if ($isEn)
                        <span class="about-intel-blue-word">Spotting</span> Scam Patterns<br class="hidden sm:inline"> Before They Hit
                    @else
                        <span class="about-intel-blue-word">Membaca</span> Pola Penipuan<br class="hidden sm:inline"> Sebelum Terjadi
                    @endif
                </h2>
            </div>

            <div class="reveal about-intel-panel rounded-[28px] border p-6 sm:p-10 lg:p-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                    <!-- Kolom Kiri: Banner Lime Miring + Grafik Risiko Berbasis Data Resmi -->
                    <div class="lg:col-span-6 space-y-0">
                        <!-- Banner Lime Miring (Adaptasi Referensi dengan Logo Digi-Safe & Akses Cepat Fitur) -->
                        <div class="about-intel-lime-card relative z-10 rounded-[20px] p-4 sm:p-5 sm:-rotate-[2deg] sm:hover:rotate-0 transition-transform duration-300 shadow-md">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-white p-0.5 inline-flex items-center justify-center shrink-0">
                                            <img
                                                src="{{ asset('images/logo.png') }}"
                                                alt="Digi-Safe Purwakarta"
                                                width="18"
                                                height="18"
                                                class="w-full h-full object-contain"
                                            >
                                        </span>
                                        <span class="text-xs font-heading font-bold">
                                            {{ $isEn ? 'OJK IASC Critical Finding' : 'Temuan Kritis OJK IASC' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-heading font-extrabold text-2xl sm:text-3xl leading-none">
                                        80% {{ $isEn ? 'Late' : 'Terlambat' }}
                                    </p>
                                    <p class="mt-1 text-[11px] font-medium leading-snug opacity-85">
                                        {{ $isEn ? 'Victims report fraud over 12 hours after transfer.' : 'Korban baru melapor lebih dari 12 jam setelah kejadian.' }}
                                    </p>
                                </div>

                                <!-- 3 Tombol Bulat Aktif ke Fitur Pencegahan Nyata -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <a
                                        href="{{ route('minigame.wa-simulator') }}"
                                        class="about-intel-lime-action w-11 h-11 rounded-full inline-flex flex-col items-center justify-center transition-transform hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0A0A0A]"
                                        aria-label="{{ $isEn ? 'Open WhatsApp Scam Simulator' : 'Buka Simulasi Chat WhatsApp' }}"
                                        title="{{ $isEn ? 'WA Simulator' : 'Simulasi WA' }}"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                        </svg>
                                    </a>
                                    <a
                                        href="{{ route('minigame.link-scanner') }}"
                                        class="about-intel-lime-action w-11 h-11 rounded-full inline-flex flex-col items-center justify-center transition-transform hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0A0A0A]"
                                        aria-label="{{ $isEn ? 'Open Link and QR Phishing Scanner' : 'Buka Pemindai Tautan dan QR' }}"
                                        title="{{ $isEn ? 'Link Scanner' : 'Cek URL & QR' }}"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                    </a>
                                    <a
                                        href="{{ route('edukasi') }}"
                                        class="about-intel-lime-action w-11 h-11 rounded-full inline-flex flex-col items-center justify-center transition-transform hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0A0A0A]"
                                        aria-label="{{ $isEn ? 'Open Cyber Literacy Modules' : 'Buka Modul Edukasi Siber' }}"
                                        title="{{ $isEn ? 'Education' : 'Edukasi' }}"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Grafik Batang Vertikal di Bawah Banner Lime -->
                        <div class="about-intel-subcard -mt-4 pt-9 pb-5 px-5 rounded-[22px] border">
                            <div class="flex items-center justify-between text-[11px] font-semibold about-intel-muted mb-4">
                                <span>{{ $isEn ? 'Risk & Exposure Index (BSSN, APJII, IASC)' : 'Indeks Paparan & Risiko (BSSN, APJII, IASC)' }}</span>
                                <span>0-100%</span>
                            </div>

                            <div class="grid grid-cols-5 gap-2.5 sm:gap-3 items-end h-36 sm:h-40">
                                @foreach ($cyberFacts['riskBars'] as $rb)
                                    <div class="flex flex-col items-center justify-end h-full gap-1.5 min-w-0">
                                        <span class="text-[10px] font-heading font-bold {{ $rb['highlight'] ? 'about-intel-blue-word' : 'about-intel-muted' }}">
                                            {{ $rb['value'] }}
                                        </span>
                                        <div
                                            class="w-full max-w-[40px] rounded-t-xl {{ $rb['highlight'] ? 'about-intel-bar-accent' : 'about-intel-bar' }}"
                                            style="height: {{ $rb['pct'] }}%;"
                                        ></div>
                                        <span class="text-[10px] font-medium about-intel-muted truncate max-w-full">
                                            {{ $rb['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Narasi Berbasis Bukti + Checklist 2x2 -->
                    <div class="lg:col-span-6 space-y-5">
                        <h3 class="about-intel-heading font-heading font-extrabold text-2xl sm:text-3xl leading-tight tracking-tight">
                            @if ($isEn)
                                Hands-On <span class="about-intel-blue-word">Scam Defense</span> for Purwakarta Residents
                            @else
                                Latihan <span class="about-intel-blue-word">Praktik Nyata</span> untuk Warga Purwakarta
                            @endif
                        </h3>

                        <p class="about-intel-body text-sm leading-relaxed">
                            {{ $isEn
                                ? 'BSSN\'s 2025 report notes that human error and social engineering remain the largest entry points for cybercrime. Digi-Safe Purwakarta pairs verified case studies with interactive drills so residents build muscle memory before facing real scammers.'
                                : 'Laporan BSSN 2025 mencatat kelalaian pengguna dan rekayasa sosial masih menjadi celah terbesar kejahatan siber. Digi-Safe Purwakarta memadukan studi kasus nyata dengan latihan interaktif agar warga terbiasa memverifikasi ancaman sebelum berhadapan dengan penipu.' }}
                        </p>

                        <!-- Checklist 2x2 Berbasis Fitur Nyata Digi-Safe -->
                        <ul class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach ($cyberFacts['checklist'] as $item)
                                <li class="flex items-start gap-2.5">
                                    <span class="about-intel-check w-5 h-5 rounded-full inline-flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </span>
                                    <span class="text-xs sm:text-[13px] font-medium about-intel-heading leading-snug">
                                        {{ $item }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- section pengembang -->
    <section id="section-pengembang" class="py-20 sm:py-28 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- kolom kiri: foto bulat pengembang & identitas visual -->
                <div class="reveal lg:col-span-5">
                    <div class="relative rounded-[26px] bg-pastel-green border border-[#DCEEDC] p-8 flex flex-col items-center text-center shadow-neutral">
                        <div class="w-28 h-28 rounded-full overflow-hidden ring-8 ring-white shadow-md bg-white shrink-0">
                            <img
                                src="{{ asset('images/developer-razief.jpg') }}"
                                alt="{{ $developer['nama'] }}"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover object-top"
                            >
                        </div>

                        <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-1 text-[11px] font-bold text-ink shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-sky"></span>
                            <span>{{ app()->getLocale() === 'en' ? 'Creator & Lead Developer' : 'Kreator & Pengembang Utama' }}</span>
                        </span>

                        <p class="mt-3 font-heading font-bold text-base text-ink">
                            {{ $developer['nama'] }}
                        </p>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $developer['asal'] }}
                        </p>

                        <div class="mt-6 pt-5 border-t border-ink/10 w-full flex flex-wrap items-center justify-center gap-2 text-[10px] font-bold text-ink/75">
                            <span class="rounded-full bg-white px-3 py-1">Laravel 12</span>
                            <span class="rounded-full bg-white px-3 py-1">Tailwind CSS</span>
                            <span class="rounded-full bg-white px-3 py-1">Alpine.js</span>
                            <span class="rounded-full bg-white px-3 py-1">Cyber Security</span>
                        </div>
                    </div>
                </div>

                <!-- kolom kanan: informasi pengembang & social media links -->
                <div class="reveal reveal-delay-1 lg:col-span-7 space-y-5">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                        <span class="text-sky">&bull;</span> {{ __('ui.about.creator_badge') }}
                    </span>

                    <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-ink tracking-tight">
                        {{ __('ui.about.dev_heading') }}
                    </h2>

                    <div class="space-y-1">
                        <h3 class="font-heading font-bold text-lg sm:text-xl text-sky">
                            {{ $developer['nama'] }}
                        </h3>
                        <p class="text-xs sm:text-sm font-semibold text-ink/80">
                            {{ $developer['asal'] }}
                        </p>
                    </div>

                    <p class="text-xs sm:text-sm text-muted leading-relaxed">
                        {{ $developer['deskripsi'] }}
                    </p>

                    <!-- tautan sosial media (dibuka di tab baru) -->
                    <div class="pt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ($developer['socials'] as $soc)
                            <a
                                href="{{ $soc['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-2xl border border-gray-200 hover:border-ink bg-white p-3.5 flex items-center gap-3 hover:-translate-y-0.5 transition-all group"
                            >
                                <span class="w-9 h-9 rounded-xl bg-pastel-green text-ink group-hover:bg-ink group-hover:text-lime flex items-center justify-center shrink-0 transition-colors">
                                    @if ($soc['type'] === 'instagram')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                                        </svg>
                                    @elseif ($soc['type'] === 'tiktok')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <line x1="3" y1="12" x2="21" y2="12" />
                                            <path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18" />
                                        </svg>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-medium text-muted block">{{ $soc['label'] }}</span>
                                    <span class="font-heading font-bold text-xs text-ink truncate block">{{ $soc['handle'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- section lokasi (embed maps) -->
    <section class="py-16 sm:py-24 bg-[#F9FBF9] border-y border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                        <span class="text-sky">&bull;</span> {{ __('ui.about.location_kicker') }}
                    </span>
                    <h2 class="mt-2 font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                        {{ __('ui.about.location_title') }}
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-muted">
                        {{ __('ui.about.location_desc') }}
                    </p>
                </div>

                <a
                    href="https://maps.app.goo.gl/yXAFWWacptxSEoU36"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 hover:border-ink px-4 py-2.5 text-xs font-bold text-ink shadow-2xs transition-colors self-start sm:self-auto"
                >
                    <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span>{{ __('ui.about.open_gmaps') }}</span>
                </a>
            </div>

            <!-- embed google maps -->
            <div class="reveal rounded-[20px] overflow-hidden border border-gray-200 bg-white shadow-neutral">
                <iframe
                    src="https://maps.google.com/maps?q=SMKN%202%20Purwakarta&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Peta Lokasi SMK Negeri 2 Purwakarta"
                    class="w-full h-[340px] sm:h-[400px] block"
                ></iframe>
            </div>

        </div>
    </section>

    <!-- section visi & misi / pilar perjalanan -->
    <section class="py-20 sm:py-28 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal text-center max-w-xl mx-auto mb-12">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                    <span class="text-sky">&bull;</span> {{ __('ui.about.vision_badge') }}
                </span>
                <h2 class="mt-2 font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                    {{ __('ui.about.vision_heading') }}
                </h2>
                <p class="mt-2 text-xs sm:text-sm text-muted">
                    {{ __('ui.about.vision_sub') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($visiMisi as $idx => $pilar)
                    <div class="reveal rounded-[22px] bg-white border border-gray-100 p-6 sm:p-7 flex flex-col justify-between shadow-2xs hover:-translate-y-1 hover:shadow-neutral transition-all duration-300">
                        <div>
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-pastel-green text-[#1B5E20] font-heading font-extrabold text-xs">
                                {{ $pilar['tahap'] }}
                            </span>
                            <h3 class="mt-5 font-heading font-bold text-base sm:text-lg text-ink">
                                {{ $pilar['judul'] }}
                            </h3>
                            <p class="mt-2 text-xs text-muted leading-relaxed">
                                {{ $pilar['deskripsi'] }}
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-[11px] font-semibold text-ink/70">
                            <span>{{ __('ui.about.vision_badge_footer') }}</span>
                            <span class="w-2 h-2 rounded-full bg-lime"></span>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

@endsection
