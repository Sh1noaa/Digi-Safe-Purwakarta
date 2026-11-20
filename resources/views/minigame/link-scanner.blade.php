@extends('layouts.app')

@section('title', app()->getLocale() === 'en' ? 'Link & QR Phishing Scanner - Digi-Safe Purwakarta' : 'Link & QR Phishing Scanner - Digi-Safe Purwakarta')

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $isDark = ($currentTheme ?? 'light') === 'dark';

        $sampleLinks = [
            [
                'label' => $isEn ? 'Fake BRI Tariff Form' : 'Phishing Tarif BRI Rp150rb',
                'url' => 'https://formulir-tarif-bri-id.xyz/batal-autodebet',
                'badge' => 'Phishing',
            ],
            [
                'label' => $isEn ? 'Wedding Invitation .APK' : 'File .APK Undangan Nikah',
                'url' => 'http://undangan-digital-pwk.top/download/Surat_Undangan_Nikah.apk',
                'badge' => 'Malware .APK',
            ],
            [
                'label' => $isEn ? 'Fake DANA Kaget Claim' : 'Klaim DANA Kaget Palsu',
                'url' => 'https://klaim-dana-kaget2026.my.id/login-pin',
                'badge' => 'Phishing',
            ],
            [
                'label' => $isEn ? 'Official Tax Portal (DJP)' : 'Portal Resmi Pajak (DJP)',
                'url' => 'https://djponline.pajak.go.id/account/login',
                'badge' => $isEn ? 'Official' : 'Resmi',
            ],
            [
                'label' => $isEn ? 'Official Digi-Safe Web' : 'Web Resmi Digi-Safe',
                'url' => 'https://digisafepurwakarta.web.id/edukasi',
                'badge' => $isEn ? 'Official' : 'Resmi',
            ],
        ];

        $sampleQrs = [
            [
                'title' => $isEn ? 'QRIS Fake Giveaway Claim' : 'QR Phishing Klaim Hadiah E-Wallet',
                'desc' => $isEn ? 'Simulated QR code pointing to a PIN-harvesting domain' : 'Simulasi QR palsu yang mengarahkan ke web pencuri PIN/OTP',
                'payload' => 'https://klaim-saldo-dana-resmi.xyz/verifikasi-otp',
                'tone' => 'red',
            ],
            [
                'title' => $isEn ? 'QR Fake Parking / E-TLE Ticket' : 'QR Surat Tilang / Parkir Palsu (.APK)',
                'desc' => $isEn ? 'Simulated QR code triggering an .APK download' : 'Simulasi stiker QR palsu yang memicu unduhan file .APK',
                'payload' => 'http://103.42.19.88/Konfirmasi_Tilang_ETLE.apk',
                'tone' => 'amber',
            ],
            [
                'title' => $isEn ? 'QR Official Digi-Safe Portal' : 'QR Portal Edukasi Resmi Digi-Safe',
                'desc' => $isEn ? 'Verified HTTPS link to Digi-Safe Purwakarta' : 'QR aman menuju halaman resmi Digi-Safe Purwakarta',
                'payload' => 'https://digisafepurwakarta.web.id/edukasi',
                'tone' => 'emerald',
            ],
        ];
    @endphp

    <section
        x-data="{
            tab: 'link',
            urlInput: '',
            scanning: false,
            qrStatus: '',
            errorMsg: '',
            result: null,

            async runScan(targetUrl = null) {
                if (targetUrl !== null) {
                    this.urlInput = targetUrl;
                }
                const clean = (this.urlInput || '').trim();
                if (!clean || this.scanning) return;

                this.scanning = true;
                this.errorMsg = '';
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const res = await fetch('{{ route('minigame.link-scanner.scan') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ url: clean })
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.ok) {
                        this.result = data;
                    } else {
                        this.errorMsg = data.message || @js($isEn ? 'Unable to analyze link.' : 'Gagal menganalisis tautan.');
                    }
                } catch (_) {
                    this.errorMsg = @js($isEn ? 'Network error. Please try again.' : 'Gangguan jaringan. Silakan coba lagi.');
                } finally {
                    this.scanning = false;
                }
            },

            async handleQrImageUpload(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                this.qrStatus = @js($isEn ? 'Reading QR code from image...' : 'Membaca kode QR dari gambar...');
                this.errorMsg = '';

                try {
                    const bitmap = await createImageBitmap(file);
                    if ('BarcodeDetector' in window) {
                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        const codes = await detector.detect(bitmap);
                        if (codes && codes.length > 0 && codes[0].rawValue) {
                            const extracted = codes[0].rawValue;
                            this.qrStatus = (@js($isEn ? 'QR Payload Extracted: ' : 'Isi QR Terbaca: ')) + extracted;
                            await this.runScan(extracted);
                            return;
                        }
                    }
                    this.qrStatus = '';
                    this.errorMsg = @js($isEn
                        ? 'Your browser does not support native QR image decoding or no QR code was detected in that image. You can paste the link directly or test one of the simulated QR codes below.'
                        : 'Browser perangkat ini belum mendukung pembacaan gambar QR otomatis atau gambar tidak memuat QR yang jelas. Anda dapat menempelkan tautannya langsung atau menguji sampel QR di bawah.');
                } catch (_) {
                    this.qrStatus = '';
                    this.errorMsg = @js($isEn ? 'Could not decode QR image. Try a clearer screenshot or paste the URL.' : 'Gagal membaca file gambar QR. Coba tangkapan layar yang lebih jelas atau tempel URL-nya.');
                }
            }
        }"
        class="min-h-screen pt-24 sm:pt-28 pb-20 px-4 sm:px-6 lg:px-8 {{ $isDark ? 'bg-[#0B1120]' : 'bg-[#F4F6FB]' }}"
    >
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('minigame.index') }}"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full border transition-colors {{ $isDark ? 'bg-slate-900 border-slate-700 text-slate-200 hover:border-sky' : 'bg-white border-gray-200 text-ink hover:border-sky' }}"
                    >
                        &larr;
                    </a>
                    <div>
                        <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-sky block">
                            {{ $isEn ? 'Forensic URL & Quishing Inspector' : 'Inspektor Forensik Tautan & QR Phishing' }}
                        </span>
                        <h1 class="font-heading font-extrabold text-xl sm:text-2xl {{ $isDark ? 'text-white' : 'text-ink' }}">
                            Link &amp; QR Phishing Scanner
                        </h1>
                    </div>
                </div>

                <!-- Tab Switcher: Link URL vs QR Code -->
                <div class="inline-flex rounded-full border p-1 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-gray-200 shadow-2xs' }}">
                    <button
                        type="button"
                        @click="tab = 'link'"
                        :class="tab === 'link' ? 'bg-ink text-white' : '{{ $isDark ? 'text-slate-300 hover:text-white' : 'text-muted hover:text-ink' }}'"
                        class="rounded-full px-4 py-1.5 text-xs font-heading font-bold transition-colors cursor-pointer"
                    >
                        {{ $isEn ? '1. Paste URL / Link' : '1. Cek Tautan / URL' }}
                    </button>
                    <button
                        type="button"
                        @click="tab = 'qr'"
                        :class="tab === 'qr' ? 'bg-ink text-white' : '{{ $isDark ? 'text-slate-300 hover:text-white' : 'text-muted hover:text-ink' }}'"
                        class="rounded-full px-4 py-1.5 text-xs font-heading font-bold transition-colors cursor-pointer"
                    >
                        {{ $isEn ? '2. Scan QR Code Image' : '2. Pindai Gambar QR' }}
                    </button>
                </div>
            </div>

            <!-- KARTU INPUT UTAMA -->
            <div class="rounded-[26px] border p-5 sm:p-7 shadow-neutral space-y-5 {{ $isDark ? 'bg-[#0F172A] border-slate-800' : 'bg-white border-gray-200/80' }}">

                <!-- MODE 1: PASTE LINK URL -->
                <div x-show="tab === 'link'" class="space-y-4">
                    <div>
                        <label for="scanner-url-input" class="block font-heading font-bold text-xs sm:text-sm {{ $isDark ? 'text-white' : 'text-ink' }} mb-1">
                            {{ $isEn ? 'Paste Suspicious Link from WhatsApp / SMS / Email' : 'Tempel Tautan Mencurigakan dari WhatsApp / SMS / Email' }}
                        </label>
                        <p class="text-xs text-muted">
                            {{ $isEn
                                ? 'Our engine dissects the domain, TLD, brand typosquatting, shortlinks, and hidden .APK payloads without opening the dangerous site.'
                                : 'Mesin pemindai akan membedah keaslian domain, pencatutan nama bank/instansi, shortlink, hingga jebakan file .APK tanpa membuka situs berbahayanya.' }}
                        </p>
                    </div>

                    <form @submit.prevent="runScan()" class="flex flex-col sm:flex-row gap-2.5">
                        <input
                            id="scanner-url-input"
                            type="text"
                            x-model="urlInput"
                            placeholder="https://formulir-tarif-bri-id.xyz/klaim..."
                            class="flex-1 rounded-2xl border px-4 py-3 text-xs sm:text-sm font-mono focus:outline-none focus:border-sky {{ $isDark ? 'bg-slate-900 border-slate-700 text-white placeholder:text-slate-500' : 'bg-[#F8FAFC] border-gray-200 text-ink placeholder:text-gray-400' }}"
                        >
                        <button
                            type="submit"
                            :disabled="scanning || !urlInput.trim()"
                            class="shrink-0 rounded-2xl bg-ink hover:bg-sky text-white font-heading font-extrabold text-xs px-6 py-3.5 min-h-[46px] inline-flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-50"
                        >
                            <span x-show="scanning" style="display: none;" class="w-3.5 h-3.5 border-2 border-lime border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="scanning ? @js($isEn ? 'Analyzing...' : 'Membedah URL...') : @js($isEn ? 'Scan Link Now' : 'Periksa Keamanan Link')"></span>
                        </button>
                    </form>

                    <!-- Contoh Link Cepat (1-Klik Uji Coba) -->
                    <div class="pt-1">
                        <span class="block text-[11px] font-heading font-bold text-muted mb-2">
                            {{ $isEn ? 'Quick test with sample links:' : 'Uji cepat dengan contoh tautan kasus nyata:' }}
                        </span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($sampleLinks as $sLink)
                                <button
                                    type="button"
                                    @click="runScan(@js($sLink['url']))"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-semibold transition-colors cursor-pointer {{ $isDark ? 'bg-slate-800 border-slate-700 text-slate-200 hover:border-sky' : 'bg-[#F8FAFC] border-gray-200 text-ink hover:border-sky' }}"
                                >
                                    <span>{{ $sLink['label'] }}</span>
                                    <span class="rounded-full px-1.5 py-0.5 text-[9px] font-extrabold {{ $sLink['badge'] === 'Resmi' || $sLink['badge'] === 'Official' ? 'bg-emerald-500/15 text-emerald-600' : 'bg-red-500/15 text-red-600' }}">
                                        {{ $sLink['badge'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- MODE 2: PINDAI GAMBAR QR CODE (QUISHING) -->
                <div x-show="tab === 'qr'" style="display: none;" class="space-y-4">
                    <div>
                        <h2 class="font-heading font-bold text-xs sm:text-sm {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'Inspect Suspicious QR Code (Anti-QRishing / Quishing)' : 'Inspeksi Gambar Kode QR Mencurigakan (Anti-QRishing)' }}
                        </h2>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $isEn
                                ? 'Upload a screenshot of a QR code from a poster, flyer, or chat message to reveal and inspect its hidden destination URL before scanning it with your mobile banking app.'
                                : 'Unggah tangkapan layar kode QR dari brosur, stiker parkir, atau pesan WA untuk membongkar URL tersembunyi di dalamnya sebelum dipindai dengan aplikasi m-Banking/e-Wallet.' }}
                        </p>
                    </div>

                    <label class="block rounded-2xl border-2 border-dashed p-6 text-center transition-colors cursor-pointer {{ $isDark ? 'border-slate-700 bg-slate-900/60 hover:border-sky' : 'border-gray-300 bg-[#F8FAFC] hover:border-sky' }}">
                        <input type="file" accept="image/*" class="sr-only" @change="handleQrImageUpload($event)">
                        <div class="w-11 h-11 rounded-2xl bg-sky/10 text-sky flex items-center justify-center mx-auto mb-2.5">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><path d="M14 14h3v3h-3zm4 4h3v3h-3zm0-4h3v2h-3zm-4 4h2v3h-2z" />
                            </svg>
                        </div>
                        <span class="font-heading font-bold text-xs block {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'Click to upload a QR Code image (PNG / JPG / WEBP)' : 'Klik untuk unggah gambar Kode QR (PNG / JPG / WEBP)' }}
                        </span>
                        <span class="text-[11px] text-muted block mt-1">
                            {{ $isEn ? 'Automatically extracts the embedded URL and runs forensic checks' : 'Otomatis mengekstrak URL di dalam QR lalu memeriksa keamanannya' }}
                        </span>
                    </label>

                    <div x-show="qrStatus" style="display: none;" class="rounded-xl bg-sky/10 border border-sky/30 px-4 py-2.5 text-xs font-mono text-sky" x-text="qrStatus"></div>

                    <!-- 3 Kartu Sampel QR Simulasi (Bisa Langsung Diklik) -->
                    <div>
                        <span class="block text-[11px] font-heading font-bold text-muted mb-2">
                            {{ $isEn ? 'Or click a simulated QR code case below to inspect its payload:' : 'Atau klik salah satu simulasi kasus QR di bawah untuk membedah isinya:' }}
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach ($sampleQrs as $sq)
                                <button
                                    type="button"
                                    @click="qrStatus = @js(($isEn ? 'Simulated QR Payload: ' : 'Isi QR Simulasi: ') . $sq['payload']); runScan(@js($sq['payload']))"
                                    class="rounded-2xl border p-3.5 text-left transition-all cursor-pointer flex items-start gap-3 {{ $isDark ? 'bg-slate-900 border-slate-800 hover:border-sky' : 'bg-[#F8FAFC] border-gray-200 hover:border-sky' }}"
                                >
                                    <span class="w-10 h-10 rounded-xl bg-ink text-white flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="font-heading font-bold text-xs block {{ $isDark ? 'text-white' : 'text-ink' }}">{{ $sq['title'] }}</span>
                                        <span class="text-[10.5px] text-muted block mt-0.5 leading-snug">{{ $sq['desc'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pesan Error -->
                <div x-show="errorMsg" style="display: none;" class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-xs font-semibold text-red-700" x-text="errorMsg"></div>
            </div>

            <!-- HASIL PEMBEDAHAN FORENSIK URL / QR -->
            <template x-if="result">
                <div class="rounded-[26px] border p-5 sm:p-7 shadow-neutral space-y-6 {{ $isDark ? 'bg-[#0F172A] border-slate-800' : 'bg-white border-gray-200/80' }}">

                    <!-- Banner Verdict & Skor Risiko -->
                    <div
                        class="rounded-2xl border p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        :class="{
                            'bg-red-50 border-red-300 text-red-950': result.verdict === 'dangerous',
                            'bg-amber-50 border-amber-300 text-amber-950': result.verdict === 'suspicious',
                            'bg-emerald-50 border-emerald-300 text-emerald-950': result.verdict === 'safe'
                        }"
                    >
                        <div class="space-y-1.5">
                            <span
                                class="inline-block rounded-full px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider text-white"
                                :class="{
                                    'bg-red-600': result.verdict === 'dangerous',
                                    'bg-amber-600': result.verdict === 'suspicious',
                                    'bg-emerald-600': result.verdict === 'safe'
                                }"
                                x-text="result.verdict_label"
                            ></span>
                            <h2 class="font-mono font-bold text-xs sm:text-sm break-all" x-text="result.normalized_url"></h2>
                            <p class="text-xs leading-relaxed opacity-90" x-text="result.summary"></p>
                        </div>

                        <div class="shrink-0 text-center rounded-2xl bg-white/90 border border-black/10 px-5 py-3">
                            <span class="block text-[10px] font-heading font-bold uppercase tracking-wider text-muted">
                                {{ $isEn ? 'Risk Score' : 'Skor Risiko' }}
                            </span>
                            <span
                                class="font-heading font-extrabold text-3xl leading-tight"
                                :class="{
                                    'text-red-600': result.verdict === 'dangerous',
                                    'text-amber-600': result.verdict === 'suspicious',
                                    'text-emerald-600': result.verdict === 'safe'
                                }"
                                x-text="result.risk_score + '/100'"
                            ></span>
                        </div>
                    </div>

                    <!-- Pembedahan Anatomi URL -->
                    <div>
                        <h3 class="font-heading font-extrabold text-xs uppercase tracking-wider text-muted mb-3">
                            {{ $isEn ? 'URL Anatomy Dissection' : 'Anatomi Struktur Tautan' }}
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                            <div class="rounded-xl border p-3 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-[#F8FAFC] border-gray-200/80' }}">
                                <span class="text-[10px] font-bold text-muted block">{{ $isEn ? 'Protocol' : 'Protokol' }}</span>
                                <span class="font-mono font-extrabold text-xs {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="result.anatomy.scheme"></span>
                            </div>
                            <div class="rounded-xl border p-3 sm:col-span-2 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-[#F8FAFC] border-gray-200/80' }}">
                                <span class="text-[10px] font-bold text-muted block">Hostname / Domain</span>
                                <span class="font-mono font-extrabold text-xs break-all text-sky" x-text="result.anatomy.host"></span>
                            </div>
                            <div class="rounded-xl border p-3 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-[#F8FAFC] border-gray-200/80' }}">
                                <span class="text-[10px] font-bold text-muted block">TLD</span>
                                <span class="font-mono font-extrabold text-xs {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="result.anatomy.tld"></span>
                            </div>
                            <div class="rounded-xl border p-3 col-span-2 sm:col-span-1 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-[#F8FAFC] border-gray-200/80' }}">
                                <span class="text-[10px] font-bold text-muted block">Path / File</span>
                                <span class="font-mono font-extrabold text-xs break-all {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="result.anatomy.path"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Temuan Forensik -->
                    <div class="space-y-2.5">
                        <h3 class="font-heading font-extrabold text-xs uppercase tracking-wider text-muted">
                            {{ $isEn ? 'Forensic Inspection Findings' : 'Detail Temuan Analisis Keamanan' }}
                        </h3>
                        <template x-for="(item, fIdx) in result.findings" :key="fIdx">
                            <div
                                class="rounded-xl border p-3.5 flex items-start gap-3"
                                :class="{
                                    'bg-red-50/75 border-red-200 text-red-950': item.severity === 'danger',
                                    'bg-amber-50/75 border-amber-200 text-amber-950': item.severity === 'warn',
                                    'bg-emerald-50/75 border-emerald-200 text-emerald-950': item.severity === 'safe'
                                }"
                            >
                                <span
                                    class="w-6 h-6 rounded-full text-white font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5"
                                    :class="{
                                        'bg-red-600': item.severity === 'danger',
                                        'bg-amber-600': item.severity === 'warn',
                                        'bg-emerald-600': item.severity === 'safe'
                                    }"
                                    x-text="item.severity === 'danger' ? '!' : (item.severity === 'warn' ? '?' : '✓')"
                                ></span>
                                <div class="space-y-0.5 text-xs">
                                    <div class="font-heading font-extrabold" x-text="item.title"></div>
                                    <p class="leading-relaxed opacity-90" x-text="item.detail"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

        </div>
    </section>
@endsection
