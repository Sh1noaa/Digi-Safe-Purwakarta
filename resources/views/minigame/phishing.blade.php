@extends('layouts.app')

@section('title', 'Phishing Hunter - Minigame Digi-Safe Purwakarta')

@section('content')

    <section x-data="phishingHunterGame()" class="min-h-screen bg-[#F7F8FC] pt-24 sm:pt-28 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <!-- navigasi balik ke hub -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <a href="{{ route('minigame.index') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-4 py-2 text-xs font-semibold text-ink hover:border-ink transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>{{ __('ui.game.back_to_hub') }}</span>
                </a>

                <div class="flex items-center gap-2">
                    @if (Auth::check() && Auth::user()->hasAdminAccess())
                        <a href="{{ route('admin.minigame.index', ['game' => 'phishing']) }}"
                            class="rounded-full bg-ink text-lime text-xs font-bold px-3.5 py-1.5 hover:bg-sky hover:text-white transition-colors">
                            {{ __('ui.game.manage_levels_admin') }}
                        </a>
                    @endif
                    <span class="rounded-full bg-[#DBEAFE] text-sky text-xs font-bold px-3.5 py-1.5">
                        Phishing Hunter • {{ $levels->count() }} Level
                    </span>
                </div>
            </div>

            <!-- layar pilih level -->
            <div x-show="state === 'menu'"
                class="rounded-[28px] bg-white border border-gray-100 p-7 sm:p-10 shadow-neutral text-center">
                <div class="w-14 h-14 rounded-2xl bg-sky/10 text-sky flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>

                <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink">
                    Phishing Hunter
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-muted max-w-lg mx-auto leading-relaxed">
                    {{ __('ui.game.phishing_intro') }}
                </p>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-left">
                    @foreach ($levels as $lvl)
                        <button type="button" @click="startGame(@js($lvl->slug))"
                            class="rounded-2xl border border-gray-200 hover:border-sky bg-[#F8FAFC]/60 hover:bg-white p-5 transition-all hover:-translate-y-1 shadow-2xs cursor-pointer group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase {{ $lvl->badge_classes }}">
                                        {{ $lvl->badge_label }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink">
                                        <svg class="w-3.5 h-3.5 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                        <span>{{ $lvl->waktu_detik }}{{ __('ui.game.sec_short') }}</span>
                                    </span>
                                </div>
                                <h3
                                    class="mt-3 font-heading font-extrabold text-base text-ink group-hover:text-sky transition-colors">
                                    {{ $lvl->nama_level }}
                                </h3>
                                <p class="mt-1 text-xs text-muted leading-relaxed">
                                    {{ $lvl->deskripsi }}
                                </p>
                            </div>

                            <div
                                class="mt-4 pt-3 border-t border-gray-200/70 flex items-center justify-between text-[11px] font-bold">
                                <span class="text-emerald-600">{{ __('ui.game.points_per_correct', ['pts' => $lvl->poin_benar]) }}</span>
                                <span class="text-sky group-hover:translate-x-1 transition-transform">{{ __('ui.game.start') }} &rarr;</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- layar permainan aktif -->
            <div x-show="state === 'playing'" style="display: none;" class="space-y-5">

                <!-- bar status skor & timer sticky agar tetap terlihat saat bermain di HP -->
                <div class="sticky top-[68px] sm:top-[76px] z-30 space-y-2 bg-[#F7F8FC]/95 py-1.5 -mx-1 px-1 rounded-2xl">
                    <div
                        class="rounded-2xl bg-white border border-gray-100 p-3.5 sm:p-4 flex flex-wrap items-center justify-between gap-3 sm:gap-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[#DBEAFE] text-sky px-3 py-1 text-xs font-extrabold uppercase" x-text="activeLevelConfig.badge_label || level.toUpperCase()"></span>
                            <span class="text-xs font-semibold text-muted" x-text="'{{ __('ui.game.question_label') }} ' + (currentIndex + 1) + ' / ' + questions.length"></span>
                        </div>

                        <div class="flex items-center gap-4 sm:gap-5">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <polyline points="12 7 12 12 15 15" />
                                </svg>
                                <span
                                    class="font-heading font-extrabold text-sm tabular-nums"
                                    :class="timeLeft <= 4 ? 'text-red-600' : 'text-ink'"
                                    x-text="timeLeft + '{{ __('ui.game.sec_short') }}'"
                                ></span>
                            </div>

                            <div class="rounded-full bg-lime px-3.5 py-1 text-xs font-extrabold text-ink tabular-nums" x-text="'{{ __('ui.game.score_label') }}: ' + score"></div>
                        </div>
                    </div>

                    <!-- bar progres timer -->
                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full transition-all duration-300 rounded-full"
                            :class="timeLeft <= 4 ? 'bg-red-500' : 'bg-sky'"
                            :style="'width: ' + ((timeLeft / maxTime) * 100) + '%'"></div>
                    </div>
                </div>

                <!-- card pesan email / chat / soal pilihan ganda -->
                <div class="rounded-[24px] bg-white border border-gray-100 p-6 sm:p-8 shadow-neutral">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span
                                class="w-10 h-10 rounded-full bg-sky/10 text-sky font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-heading font-bold text-sm text-ink truncate"
                                    x-text="currentQuestion().sender"></p>
                                <p class="text-xs text-muted truncate font-mono" x-text="currentQuestion().address"></p>
                            </div>
                        </div>

                        <span class="shrink-0 rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase text-muted"
                            x-text="currentQuestion().channel"></span>
                    </div>

                    <div class="py-5">
                        <h2 class="font-heading font-bold text-base sm:text-lg text-ink" x-text="currentQuestion().subject">
                        </h2>
                        <div class="mt-3 rounded-2xl bg-[#F7F8FC] border border-gray-100 p-4 text-xs sm:text-sm text-ink/90 leading-relaxed"
                            x-text="currentQuestion().body"></div>
                    </div>

                    <!-- Mode Soal Pilihan Ganda (A, B, C, D) dari Bank Soal Admin -->
                    <template x-if="currentQuestion().mode === 'mcq' && !answered">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <template x-for="letter in ['A', 'B', 'C', 'D']" :key="letter">
                                <button type="button" @click="submitAnswer(letter)"
                                    class="rounded-2xl bg-[#F8FAFC] hover:bg-sky hover:text-white border border-gray-200 p-3.5 text-left text-xs font-semibold text-ink flex items-start gap-2.5 transition-all cursor-pointer">
                                    <span
                                        class="w-6 h-6 rounded-lg bg-ink text-lime font-heading font-extrabold text-[11px] flex items-center justify-center shrink-0"
                                        x-text="letter"></span>
                                    <span class="leading-snug" x-text="currentQuestion().options[letter]"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <!-- Mode Tombol Pilihan Asli vs Phishing -->
                    <template x-if="currentQuestion().mode !== 'mcq' && !answered">
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <button type="button" @click="submitAnswer('asli')"
                                class="rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-heading font-bold text-sm py-4 px-6 flex items-center justify-center gap-2 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>{{ __('ui.game.genuine_safe') }}</span>
                            </button>

                            <button type="button" @click="submitAnswer('phishing')"
                                class="rounded-2xl bg-red-600 hover:bg-red-700 text-white font-heading font-bold text-sm py-4 px-6 flex items-center justify-center gap-2 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <span>{{ __('ui.game.phishing_trap') }}</span>
                            </button>
                        </div>
                    </template>

                    <!-- card penjelasan setelah menjawab -->
                    <div x-show="answered" style="display: none;" class="mt-3 rounded-2xl p-4 border"
                        :class="isCorrect ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-red-50 border-red-200 text-red-900'">
                        <div class="flex items-center justify-between">
                            <span class="font-heading font-bold text-xs uppercase tracking-wider" x-text="feedbackTitle"></span>
                            <span class="text-xs font-bold" x-text="isCorrect ? ('+' + activeLevelConfig.poin_benar + ' pts') : ('-' + activeLevelConfig.penalti_salah + ' pts')"></span>
                        </div>
                        <p class="mt-1.5 text-xs leading-relaxed" x-text="currentQuestion().explanation"></p>

                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                @click="nextQuestion()"
                                class="inline-flex items-center gap-2 rounded-full bg-ink text-white text-xs font-bold px-5 py-2.5 cursor-pointer"
                            >
                                <span x-text="currentIndex + 1 < questions.length ? '{{ __('ui.game.next_question') }}' : '{{ __('ui.game.view_final_result') }}'"></span>
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- layar hasil akhir -->
            <div x-show="state === 'finished'" style="display: none;"
                class="rounded-[28px] bg-white border border-gray-100 p-8 sm:p-10 shadow-neutral text-center">
                <div class="w-16 h-16 rounded-full bg-lime text-ink flex items-center justify-center mx-auto mb-4 font-heading font-extrabold text-xl"
                    x-text="accuracy() + '%'"></div>
                <h2 class="font-heading font-extrabold text-2xl text-ink">{{ __('ui.game.session_finished') }}</h2>
                <p class="mt-1 text-xs text-muted" x-text="saveStatus"></p>

                <div class="mt-6 grid grid-cols-3 gap-3 max-w-md mx-auto">
                    <div class="rounded-2xl bg-[#F7F8FC] p-4">
                        <span class="text-[10px] font-semibold text-muted block">{{ __('ui.game.total_score') }}</span>
                        <span class="font-heading font-extrabold text-xl text-ink mt-1 block" x-text="score"></span>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <span class="text-[10px] font-semibold text-emerald-700 block">{{ __('ui.game.correct') }}</span>
                        <span class="font-heading font-extrabold text-xl text-emerald-700 mt-1 block"
                            x-text="correctCount"></span>
                    </div>
                    <div class="rounded-2xl bg-red-50 p-4">
                        <span class="text-[10px] font-semibold text-red-600 block">{{ __('ui.game.wrong') }}</span>
                        <span class="font-heading font-extrabold text-xl text-red-600 mt-1 block"
                            x-text="wrongCount"></span>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" @click="state = 'menu'"
                        class="rounded-full bg-lime text-ink font-bold text-xs px-6 py-3 cursor-pointer">
                        {{ __('ui.game.choose_other_level') }}
                    </button>
                    <a href="{{ route('minigame.index') }}"
                        class="rounded-full bg-ink text-white font-bold text-xs px-6 py-3">
                        {{ __('ui.game.back_to_dashboard') }}
                    </a>
                </div>
            </div>

        </div>
    </section>

    <script>
        function phishingHunterGame() {
            const isEn = @js(app()->getLocale() === 'en');
            const levelsMap = @js($levels->keyBy('slug'));
            const dbQuestions = @js($dbSoals);

            const bankSoalId = {
                easy: [
                    {
                        sender: 'Kurir Ekspedisi Cepat',
                        address: '+62 813-9900-1122 (WhatsApp)',
                        channel: 'Chat WA',
                        subject: 'Foto Resi Paket Anda',
                        body: 'Halo kak, paket sudah sampai di gudang Purwakarta tapi alamat kurang jelas. Silakan buka file LIHAT-RESI-PAKET.apk untuk cek foto paketnya ya.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Kurir resmi tidak pernah mengirim resi berformat .apk (aplikasi Android pencuri SMS/OTP).'
                    },
                    {
                        sender: 'Diskominfo Purwakarta',
                        address: 'info@purwakartakab.go.id',
                        channel: 'Email Resmi',
                        subject: 'Undangan Sosialisasi Literasi Digital Desa',
                        body: 'Yth. Pengurus Karang Taruna, berikut jadwal kegiatan edukasi keamanan siber minggu depan di Aula Kecamatan. Informasi lengkap dapat dilihat pada portal resmi purwakartakab.go.id.',
                        answer: 'asli',
                        explanation: 'Pesan Asli. Menggunakan domain pemerintah resmi (.go.id) dan tidak meminta data rahasia seperti PIN atau OTP.'
                    },
                    {
                        sender: 'Info Undian Berhadiah',
                        address: 'pemenang-resmi@hadiah-bank-bri.xyz',
                        channel: 'Email',
                        subject: 'SELAMAT! Anda Memenangkan Rp 50.000.000',
                        body: 'Selamat! Nomor rekening Anda terpilih mendapat hadiah utama. Segera masukkan PIN, nomor kartu ATM, dan kode OTP Anda pada tautan berikut dalam 10 menit.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Menggunakan domain palsu (.xyz), menciptakan kepanikan waktu, dan meminta PIN serta kode OTP.'
                    },
                    {
                        sender: 'PT PLN (Persero)',
                        address: 'pln123@pln.co.id',
                        channel: 'Email Resmi',
                        subject: 'Informasi Tagihan Listrik Bulanan',
                        body: 'Pelanggan Yth, tagihan listrik bulan ini telah terbit. Anda dapat melakukan pembayaran melalui aplikasi PLN Mobile atau kanal perbankan resmi.',
                        answer: 'asli',
                        explanation: 'Pesan Asli. Berasal dari domain resmi pln.co.id dan mengarahkan pembayaran ke aplikasi resmi tanpa tautan mencurigakan.'
                    },
                    {
                        sender: 'Admin Sosial Media',
                        address: 'security-alert@instagrarn-verify.com',
                        channel: 'Email',
                        subject: 'Akun Anda Akan Dihapus Dalam 24 Jam',
                        body: 'Kami mendeteksi pelanggaran hak cipta. Klik di sini dan masukkan password akun Anda sekarang agar akun tidak diblokir permanen.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Perhatikan ejaan domain "instagrarn" (menggunakan huruf r dan n) untuk menipu mata.'
                    }
                ],
                medium: [
                    {
                        sender: 'Direktorat Jenderal Pajak',
                        address: 'efiling@pajak-online-djp.com',
                        channel: 'Email',
                        subject: 'Pembaruan Data NPWP & Meterai Elektronik',
                        body: 'Sehubungan pembaruan sistem pajak, wajib pajak diminta mengunduh formulir Pembaruan_NPWP.exe terlampir dan login menggunakan kredensial DJP Online.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Domain resmi pajak adalah pajak.go.id, bukan .com, dan lampiran .exe adalah program berbahaya.'
                    },
                    {
                        sender: 'Google Security',
                        address: 'no-reply@accounts.google.com',
                        channel: 'Email Resmi',
                        subject: 'Peringatan Keamanan: Login Baru di Perangkat Windows',
                        body: 'Akun Google Anda baru saja digunakan untuk login di perangkat Windows. Jika ini memang Anda, abaikan email ini. Jika bukan, periksa aktivitas di myaccount.google.com.',
                        answer: 'asli',
                        explanation: 'Pesan Asli dari sistem keamanan Google (accounts.google.com) yang tidak meminta Anda membalas password.'
                    },
                    {
                        sender: 'HRD Rekrutmen BUMN',
                        address: 'panitia.seleksi@gmail.com',
                        channel: 'Email',
                        subject: 'Panggilan Tes Interview Kerja di Bali',
                        body: 'Anda dinyatakan lolos administrasi. Peserta wajib mentransfer biaya tiket pesawat Rp 2.500.000 ke rekening pribadi bendahara travel sebelum pukul 17.00 WIB.',
                        answer: 'phishing',
                        explanation: 'Jebakan Penipuan! Rekrutmen resmi tidak menggunakan email gratisan (@gmail.com) dan tidak pernah meminta transfer uang tiket di muka.'
                    },
                    {
                        sender: 'BPJS Kesehatan',
                        address: 'info@bpjs-kesehatan.go.id',
                        channel: 'Email Resmi',
                        subject: 'Ringkasan Layanan Mobile JKN',
                        body: 'Terima kasih telah memanfaatkan layanan antrean online melalui aplikasi Mobile JKN. Jaga selalu kerahasiaan kata sandi akun Anda.',
                        answer: 'asli',
                        explanation: 'Pesan Asli dari domain resmi .go.id dan justru mengingatkan pengguna menjaga kerahasiaan kata sandi.'
                    },
                    {
                        sender: 'CS Dompet Digital',
                        address: '+62 857-0011-8899 (Telegram)',
                        channel: 'Telegram',
                        subject: 'Pembatalan Transaksi Paylater',
                        body: 'Ada transaksi Rp 4.800.000 di akun Anda. Untuk membatalkan, sebutkan 4 digit kode OTP yang baru saja dikirim via SMS ke nomor HP Anda.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Petugas resmi tidak pernah menghubungi lewat nomor pribadi untuk meminta kode OTP.'
                    }
                ],
                hard: [
                    {
                        sender: 'Tim IT Internal Kampus',
                        address: 'helpdesk@upi-purwakarta.ac-id.org',
                        channel: 'Email',
                        subject: 'Wajib: Migrasi SSO Mahasiswa & Dosen',
                        body: 'Seluruh civitas akademika diwajibkan melakukan sinkronisasi password SSO melalui portal upi-purwakarta.ac-id.org/login sebelum akses SIAKAD dinonaktifkan malam ini.',
                        answer: 'phishing',
                        explanation: 'Jebakan Spear-Phishing! Domain menggunakan akhiran ".ac-id.org" (bukan ".ac.id" resmi kampus).'
                    },
                    {
                        sender: 'GitHub Security',
                        address: 'noreply@github.com',
                        channel: 'Email Resmi',
                        subject: '[GitHub] Your Dependabot alerts for digi-safe-purwakarta',
                        body: 'We found 1 potential vulnerability in your repository dependencies. Visit your repository security tab on github.com to review the advisory.',
                        answer: 'asli',
                        explanation: 'Pesan Asli. Notifikasi standar dari noreply@github.com yang mengarahkan pengguna membuka dashboard resmi.'
                    },
                    {
                        sender: 'Bendahara Kecamatan',
                        address: 'keuangan@purwakartakab.go.id (Reply-To: pencairan.dana99@yahoo.com)',
                        channel: 'Email',
                        subject: 'Revisi Nomor Rekening Pencairan Dana Kegiatan',
                        body: 'Mohon transfer dana kegiatan literasi desa hari ini langsung dialihkan ke rekening baru atas nama pribadi berikut karena rekening dinas sedang gangguan.',
                        answer: 'phishing',
                        explanation: 'Jebakan Business Email Compromise! Terdapat alamat Reply-To palsu (@yahoo.com) dan permintaan mendadak mengubah rekening dinas ke rekening pribadi.'
                    },
                    {
                        sender: 'Layanan Sertifikat Elektronik BSSN',
                        address: 'info@bssn.go.id',
                        channel: 'Email Resmi',
                        subject: 'Pemberitahuan Masa Berlaku Sertifikat Elektronik',
                        body: 'Sertifikat elektronik Anda akan berakhir dalam 30 hari. Silakan lakukan pembaruan melalui aplikasi resmi instansi Anda masing-masing.',
                        answer: 'asli',
                        explanation: 'Pesan Asli dari domain resmi bssn.go.id tanpa tautan eksternal mencurigakan.'
                    },
                    {
                        sender: 'Notifikasi Dokumen Bersama',
                        address: 'drive-shares-dm@google-cloud-docs.net',
                        channel: 'Email',
                        subject: 'Laporan_Anggaran_2026.pdf telah dibagikan kepada Anda',
                        body: 'Dokumen terenkripsi memerlukan autentikasi ulang. Klik tombol Buka Dokumen dan masukkan email serta password kerja Anda.',
                        answer: 'phishing',
                        explanation: 'Jebakan Phishing! Domain pengirim "google-cloud-docs.net" bukan milik Google resmi.'
                    }
                ],
                expert: [
                    {
                        sender: 'Security Operations Center',
                        address: 'soc-alert@microsoft-security-auth.com',
                        channel: 'Email OAuth',
                        subject: 'Persetujuan Aplikasi Enterprise Cloud Backup',
                        body: 'Aplikasi "CloudSync Validator" meminta izin membaca seluruh email, kontak, dan mengganti aturan penerusan (forwarding rules) akun Microsoft 365 Anda.',
                        answer: 'phishing',
                        explanation: 'Jebakan Illicit Consent Grant (OAuth Phishing)! Penyerang meminta izin akses penuh lewat aplikasi palsu tanpa perlu tahu password Anda.'
                    },
                    {
                        sender: 'CSIRT Purwakarta',
                        address: 'csirt@purwakartakab.go.id',
                        channel: 'Email Resmi',
                        subject: 'Buletin Keamanan Kerentanan Browser Terbaru',
                        body: 'Dihimbau kepada seluruh pengelola sistem informasi daerah untuk memperbarui peramban Chrome/Edge ke versi terbaru melalui menu Pengaturan > Tentang Chrome.',
                        answer: 'asli',
                        explanation: 'Pesan Asli. Menginstruksikan pembaruan langsung melalui menu internal aplikasi resmi, bukan dari tautan unduhan luar.'
                    },
                    {
                        sender: 'Vendor Pengadaan IT',
                        address: 'billing@mitra-teknologi.co.id (Return-Path: spoof@darkmailer.ru)',
                        channel: 'Email',
                        subject: 'Tagihan Termin 2 & Lampiran Faktur_Pajak.iso',
                        body: 'Terlampir file disk image Faktur_Pajak.iso. Mohon klik dua kali file di dalamnya untuk melihat rincian perubahan nomor rekening virtual account kami.',
                        answer: 'phishing',
                        explanation: 'Jebakan Malware & BEC! File .iso digunakan untuk melewati filter Mark-of-the-Web Windows dan menyembunyikan trojan.'
                    }
                ],
                master: [
                    {
                        sender: 'Notifikasi MFA Push',
                        address: 'authenticator-daemon@corp-sso.id',
                        channel: 'Push Notification',
                        subject: 'Permintaan Persetujuan Login pukul 02.15 WIB (14x Beruntun)',
                        body: 'Seseorang mencoba masuk ke akun Anda dan mengirimkan notifikasi Approve 2FA berulang kali di tengah malam disertai pesan WA mengaku tim IT meminta Anda menekan tombol Approve.',
                        answer: 'phishing',
                        explanation: 'Serangan MFA Fatigue (Push Bombing)! Penyerang membanjiri notifikasi 2FA berharap target menekan Approve karena lelah atau tertipu.'
                    },
                    {
                        sender: 'Automated Backup System',
                        address: 'backup-noreply@internal.purwakartakab.go.id',
                        channel: 'System Log',
                        subject: 'Laporan Integritas Snapshot Harian SHA-256 Verified',
                        body: 'Snapshot database harian berhasil diverifikasi pada penyimpanan offline terisolasi. Tidak ada tindakan login atau klik tautan yang diperlukan.',
                        answer: 'asli',
                        explanation: 'Log Sistem Asli. Bersifat informatif, terverifikasi, dan tidak menuntut aksi autentikasi eksternal.'
                    }
                ]
            };

            const bankSoalEn = {
                easy: [
                    {
                        sender: 'Express Parcel Courier',
                        address: '+62 813-9900-1122 (WhatsApp)',
                        channel: 'WhatsApp Chat',
                        subject: 'Your Parcel Receipt Photo',
                        body: 'Hi there, your parcel arrived at the Purwakarta warehouse but the address is unclear. Please open LIHAT-RESI-PAKET.apk to inspect your parcel photo.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Official couriers never send receipts as .apk Android installer files (used to steal SMS/OTP codes).'
                    },
                    {
                        sender: 'Purwakarta Diskominfo',
                        address: 'info@purwakartakab.go.id',
                        channel: 'Official Email',
                        subject: 'Village Digital Literacy Workshop Invitation',
                        body: 'Dear Youth Organization members, here is next week’s cyber safety workshop schedule at the District Hall. Full info is available on purwakartakab.go.id.',
                        answer: 'asli',
                        explanation: 'Genuine Message. Uses an official government domain (.go.id) and never asks for confidential credentials like PIN or OTP.'
                    },
                    {
                        sender: 'Grand Prize Committee',
                        address: 'winner@hadiah-bank-bri.xyz',
                        channel: 'Email',
                        subject: 'CONGRATS! You Won Rp 50,000,000',
                        body: 'Congratulations! Your bank account was selected for our grand prize. Enter your ATM PIN, card number, and OTP code on this link within 10 minutes.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Uses a fake domain (.xyz), creates artificial urgency, and asks for your PIN and OTP code.'
                    },
                    {
                        sender: 'PT PLN (Persero)',
                        address: 'pln123@pln.co.id',
                        channel: 'Official Email',
                        subject: 'Monthly Electricity Bill Statement',
                        body: 'Dear Customer, this month’s electricity bill is ready. Please complete payment via the official PLN Mobile app or authorized banking channels.',
                        answer: 'asli',
                        explanation: 'Genuine Message. Sent from official domain pln.co.id and directs payment to official apps without suspicious links.'
                    },
                    {
                        sender: 'Social Media Support',
                        address: 'security-alert@instagrarn-verify.com',
                        channel: 'Email',
                        subject: 'Your Account Will Be Deleted Within 24 Hours',
                        body: 'We detected a copyright violation. Click here and enter your account password now to prevent permanent account suspension.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Notice the misspelled domain "instagrarn" (using r + n instead of m) to deceive the eye.'
                    }
                ],
                medium: [
                    {
                        sender: 'Directorate General of Taxes',
                        address: 'efiling@pajak-online-djp.com',
                        channel: 'Email',
                        subject: 'Tax ID & Electronic Stamp Update',
                        body: 'Due to system upgrades, taxpayers must download the attached Pembaruan_NPWP.exe form and sign in with DJP Online credentials.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Official tax domain is pajak.go.id, not .com, and .exe attachments are dangerous executables.'
                    },
                    {
                        sender: 'Google Security',
                        address: 'no-reply@accounts.google.com',
                        channel: 'Official Email',
                        subject: 'Security Alert: New Sign-In on Windows Device',
                        body: 'Your Google Account was just signed in on a Windows device. If this was you, ignore this email. Otherwise, review activity at myaccount.google.com.',
                        answer: 'asli',
                        explanation: 'Genuine Message from Google security (accounts.google.com) that never asks you to reply with your password.'
                    },
                    {
                        sender: 'BUMN Recruitment HR',
                        address: 'panitia.seleksi@gmail.com',
                        channel: 'Email',
                        subject: 'Job Interview Invitation in Bali',
                        body: 'You passed initial screening. Candidates must transfer Rp 2,500,000 for flight tickets to our travel treasurer’s personal bank account before 17:00 WIB.',
                        answer: 'phishing',
                        explanation: 'Scam Trap! Official corporate hiring never uses free @gmail.com addresses or demands upfront travel transfers.'
                    },
                    {
                        sender: 'BPJS Kesehatan',
                        address: 'info@bpjs-kesehatan.go.id',
                        channel: 'Official Email',
                        subject: 'Mobile JKN Service Summary',
                        body: 'Thank you for using the online queue feature in the Mobile JKN app. Always keep your account password confidential.',
                        answer: 'asli',
                        explanation: 'Genuine Message from official .go.id domain reminding users to protect their passwords.'
                    },
                    {
                        sender: 'E-Wallet Support',
                        address: '+62 857-0011-8899 (Telegram)',
                        channel: 'Telegram',
                        subject: 'PayLater Transaction Cancellation',
                        body: 'There is a Rp 4,800,000 charge on your account. To cancel it, share the 4-digit OTP code just sent via SMS to your phone.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Official support never contacts from personal numbers asking for SMS OTP codes.'
                    }
                ],
                hard: [
                    {
                        sender: 'Campus IT Helpdesk',
                        address: 'helpdesk@upi-purwakarta.ac-id.org',
                        channel: 'Email',
                        subject: 'Mandatory: Student & Lecturer SSO Migration',
                        body: 'All campus members must synchronize SSO passwords via upi-purwakarta.ac-id.org/login before portal access is deactivated tonight.',
                        answer: 'phishing',
                        explanation: 'Spear-Phishing Trap! The domain ends in ".ac-id.org" instead of official university domain ".ac.id".'
                    },
                    {
                        sender: 'GitHub Security',
                        address: 'noreply@github.com',
                        channel: 'Official Email',
                        subject: '[GitHub] Your Dependabot alerts for digi-safe-purwakarta',
                        body: 'We found 1 potential vulnerability in your repository dependencies. Visit your repository security tab on github.com to review the advisory.',
                        answer: 'asli',
                        explanation: 'Genuine Message. Standard notification from noreply@github.com directing users to the official dashboard.'
                    },
                    {
                        sender: 'District Treasurer',
                        address: 'keuangan@purwakartakab.go.id (Reply-To: pencairan.dana99@yahoo.com)',
                        channel: 'Email',
                        subject: 'Urgent Bank Account Revision for Event Disbursement',
                        body: 'Please redirect today’s digital literacy event funds to the personal bank account below because our agency account is under maintenance.',
                        answer: 'phishing',
                        explanation: 'Business Email Compromise Trap! Fake Reply-To (@yahoo.com) and urgent request to divert agency funds to a personal account.'
                    },
                    {
                        sender: 'BSSN Electronic Certificate Service',
                        address: 'info@bssn.go.id',
                        channel: 'Official Email',
                        subject: 'Electronic Certificate Expiration Notice',
                        body: 'Your electronic certificate expires in 30 days. Please renew it directly through your agency’s official portal.',
                        answer: 'asli',
                        explanation: 'Genuine Message from official bssn.go.id domain with no suspicious external links.'
                    },
                    {
                        sender: 'Shared Document Notification',
                        address: 'drive-shares-dm@google-cloud-docs.net',
                        channel: 'Email',
                        subject: 'Budget_Report_2026.pdf was shared with you',
                        body: 'Encrypted document requires re-authentication. Click Open Document and enter your work email and password.',
                        answer: 'phishing',
                        explanation: 'Phishing Trap! Sender domain "google-cloud-docs.net" does not belong to Google.'
                    }
                ],
                expert: [
                    {
                        sender: 'Security Operations Center',
                        address: 'soc-alert@microsoft-security-auth.com',
                        channel: 'OAuth Email',
                        subject: 'Approve Enterprise Cloud Backup Application',
                        body: 'App "CloudSync Validator" requests permission to read all emails, contacts, and modify forwarding rules on your Microsoft 365 account.',
                        answer: 'phishing',
                        explanation: 'Illicit Consent Grant (OAuth Phishing)! Attackers hijack full email access via rogue app permissions without stealing passwords.'
                    },
                    {
                        sender: 'Purwakarta CSIRT',
                        address: 'csirt@purwakartakab.go.id',
                        channel: 'Official Email',
                        subject: 'Security Advisory: Latest Browser Vulnerability Patch',
                        body: 'All regional system administrators are advised to update Chrome/Edge via Settings > About Chrome immediately.',
                        answer: 'asli',
                        explanation: 'Genuine Message. Advises updating via internal official browser settings rather than an external download link.'
                    },
                    {
                        sender: 'IT Procurement Vendor',
                        address: 'billing@mitra-teknologi.co.id (Return-Path: spoof@darkmailer.ru)',
                        channel: 'Email',
                        subject: 'Phase 2 Invoice & Tax_Invoice.iso Attachment',
                        body: 'Attached is the disk image Tax_Invoice.iso. Double-click the file inside to view our updated virtual account bank details.',
                        answer: 'phishing',
                        explanation: 'Malware & BEC Trap! .iso disk images are used to bypass Windows Mark-of-the-Web protections and conceal trojans.'
                    }
                ],
                master: [
                    {
                        sender: 'MFA Push Alert',
                        address: 'authenticator-daemon@corp-sso.id',
                        channel: 'Push Notification',
                        subject: 'Login Approval Request at 02:15 AM (14x Repeated)',
                        body: 'Someone is attempting to sign in and flooding your phone with 2FA Approve prompts alongside a WhatsApp message claiming to be IT support telling you to tap Approve.',
                        answer: 'phishing',
                        explanation: 'MFA Fatigue (Push Bombing) Attack! Attackers spam 2FA prompts hoping the victim taps Approve out of exhaustion or trickery.'
                    },
                    {
                        sender: 'Automated Backup System',
                        address: 'backup-noreply@internal.purwakartakab.go.id',
                        channel: 'System Log',
                        subject: 'Daily Snapshot Integrity Report SHA-256 Verified',
                        body: 'Daily database snapshot verified on isolated offline storage. No login or external link action is required.',
                        answer: 'asli',
                        explanation: 'Genuine System Log. Informational, verified, and requires no external authentication.'
                    }
                ]
            };

            const bankSoal = isEn ? bankSoalEn : bankSoalId;

            return {
                state: 'menu',
                level: 'easy',
                activeLevelConfig: {
                    badge_label: 'LEVEL 1 • EASY',
                    waktu_detik: 15,
                    poin_benar: 10,
                    penalti_salah: 5
                },
                questions: [],
                currentIndex: 0,
                score: 0,
                correctCount: 0,
                wrongCount: 0,
                maxTime: 15,
                timeLeft: 15,
                timerId: null,
                answered: false,
                isCorrect: false,
                feedbackTitle: '',
                saveStatus: isEn ? 'Saving score...' : 'Menyimpan skor...',

                startGame(selectedLevel) {
                    this.level = selectedLevel;
                    const cfg = levelsMap[selectedLevel] || {
                        badge_label: 'LEVEL • ' + selectedLevel.toUpperCase(),
                        waktu_detik: 15,
                        poin_benar: 10,
                        penalti_salah: 5
                    };
                    this.activeLevelConfig = cfg;
                    this.maxTime = parseInt(cfg.waktu_detik || 15, 10);

                    const basePool = bankSoal[selectedLevel] ? [...bankSoal[selectedLevel]] : [...bankSoal.medium];

                    const customFromDb = (dbQuestions || [])
                        .filter(q => q.level === selectedLevel)
                        .map(q => ({
                            mode: 'mcq',
                            sender: isEn ? 'Digi-Safe Admin Question Bank' : 'Bank Soal Admin Digi-Safe',
                            address: 'cases@digisafe.purwakarta.id',
                            channel: cfg.badge_label || (isEn ? 'CYBER CASE' : 'KASUS SIBER'),
                            subject: isEn ? 'Digital Security Case Analysis' : 'Analisis Kasus Keamanan Digital',
                            body: q.pertanyaan,
                            options: q.opsi,
                            answer: q.kunci,
                            explanation: (isEn ? 'Correct answer is (' : 'Jawaban tepat adalah (') + q.kunci + '): ' + q.kunci_teks
                        }));

                    this.questions = [...customFromDb, ...basePool];
                    this.currentIndex = 0;
                    this.score = 0;
                    this.correctCount = 0;
                    this.wrongCount = 0;
                    this.totalTimeSpent = 0;
                    this.state = 'playing';
                    this.startQuestionTimer();
                },

                currentQuestion() {
                    return this.questions[this.currentIndex] || {};
                },

                startQuestionTimer() {
                    this.answered = false;
                    this.timeLeft = this.maxTime;
                    if (this.timerId) clearInterval(this.timerId);

                    this.timerId = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.timerId);
                            this.handleTimeOut();
                        }
                    }, 1000);
                },

                handleTimeOut() {
                    if (this.answered) return;
                    this.answered = true;
                    this.totalTimeSpent = (this.totalTimeSpent || 0) + this.maxTime;
                    this.isCorrect = false;
                    this.wrongCount++;
                    this.score = Math.max(0, this.score - parseInt(this.activeLevelConfig.penalti_salah || 5, 10));
                    this.feedbackTitle = isEn ? 'Time Up!' : 'Waktu Habis!';
                },

                submitAnswer(choice) {
                    if (this.answered) return;
                    if (this.timerId) clearInterval(this.timerId);
                    this.answered = true;
                    this.totalTimeSpent = (this.totalTimeSpent || 0) + Math.max(1, this.maxTime - this.timeLeft);

                    if (choice === this.currentQuestion().answer) {
                        this.isCorrect = true;
                        this.correctCount++;
                        this.score += parseInt(this.activeLevelConfig.poin_benar || 10, 10);
                        this.feedbackTitle = isEn ? 'Spot On!' : 'Tepat Sekali!';
                    } else {
                        this.isCorrect = false;
                        this.wrongCount++;
                        this.score = Math.max(0, this.score - parseInt(this.activeLevelConfig.penalti_salah || 5, 10));
                        this.feedbackTitle = isEn ? 'Incorrect!' : 'Kurang Tepat!';
                    }
                },

                nextQuestion() {
                    if (this.currentIndex + 1 < this.questions.length) {
                        this.currentIndex++;
                        this.startQuestionTimer();
                    } else {
                        this.finishGame();
                    }
                },

                accuracy() {
                    const total = this.correctCount + this.wrongCount;
                    return total > 0 ? Math.round((this.correctCount / total) * 100) : 0;
                },

                finishGame() {
                    if (this.timerId) clearInterval(this.timerId);
                    this.state = 'finished';
                    this.saveStatus = isEn ? 'Saving score to server...' : 'Menyimpan skor ke server...';

                    fetch('{{ route('minigame.save-score') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            game_type: 'phishing',
                            skor: this.score,
                            benar: this.correctCount,
                            salah: this.wrongCount,
                            avg_time_sec: this.questions.length > 0 ? (this.totalTimeSpent / this.questions.length) : 8
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.saveStatus = data.message || (isEn ? 'Completed!' : 'Selesai!');
                    })
                    .catch(() => {
                        this.saveStatus = isEn ? 'Score calculated.' : 'Skor selesai dihitung.';
                    });
                }
            };
        }
    </script>

@endsection