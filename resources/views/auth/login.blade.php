<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Digi-Safe Purwakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-white min-h-screen py-3 sm:py-5 px-2.5 sm:px-5 flex flex-col justify-center antialiased selection:bg-lime selection:text-ink">

    <!-- Container dengan pembatas seperti hero awan di Beranda (tidak full layar tanpa batas) -->
    <section class="w-full max-w-[1420px] mx-auto">
        <div class="relative rounded-[24px] sm:rounded-[32px] border border-sky/25 shadow-neutral overflow-hidden py-8 sm:py-11 px-4 sm:px-8 lg:px-12"
            style="background-image: url('{{ asset('images/auth-bg-2.jpg') }}'); background-size: cover; background-position: center;">
            <!-- Layer aksen gambar background kedua agar berpadu halus -->
            <div class="absolute inset-0 pointer-events-none opacity-65 mix-blend-multiply"
                style="background-image: url('{{ asset('images/auth-bg-1.jpg') }}'); background-size: cover; background-position: right center;">
            </div>

            <!-- Pola dot tipis di atas background -->
            <div class="absolute inset-0 pointer-events-none opacity-35"
                style="background-image: radial-gradient(#93C5FD 1px, transparent 1px); background-size: 22px 22px;">
            </div>

            <div x-data="{
                    email: @js(old('email', '')),
                    showPass: false
                }" class="relative z-10 w-full max-w-6xl mx-auto">
                <!-- Bar navigasi paling atas dengan Logo Digi-Safe resmi -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <a href="{{ route('beranda') }}"
                        class="flow-node rounded-full px-4 py-2 inline-flex items-center gap-3 group">
                        <span
                            class="w-9 h-8 rounded-xl bg-white border border-gray-100 shadow-2xs flex items-center justify-center p-2">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta"
                                class="w-full h-full object-contain">
                        </span>
                        <div class="text-left pr-1">
                            <span
                                class="font-heading font-extrabold text-xs sm:text-sm text-ink block leading-none">Digi-Safe
                                Purwakarta</span>
                            <span class="text-[10px] font-semibold text-sky tracking-wider uppercase">Portal Keamanan
                                Siber</span>
                        </div>
                    </a>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <a href="{{ route('beranda') }}"
                            class="flow-node rounded-full px-4 py-2.5 text-xs font-heading font-bold text-ink hover:text-sky inline-flex items-center gap-1.5 transition-colors">
                            <span>&larr; Kembali ke Beranda</span>
                        </a>

                        <!-- Tombol Daftar terpisah & jelas di bagian atas -->
                        <a href="{{ route('register') }}"
                            class="rounded-full bg-lime hover:bg-sky text-ink hover:text-white font-heading font-extrabold text-xs px-5 py-2.5 shadow-sm inline-flex items-center gap-2 transition-all hover:-translate-y-0.5">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <line x1="20" y1="8" x2="20" y2="14" />
                                <line x1="23" y1="11" x2="17" y2="11" />
                            </svg>
                            <span>Daftar Akun Baru</span>
                        </a>
                    </div>
                </div>

                <!-- Layout 2 Kolom Desktop: Kiri Robot Besar + Judul Variasi Login, Kanan Flowchart Form Login (di mobile khusus form saja) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

                    <!-- Kolom Kiri: Judul Login Variatif di Atas + Gambar Robot Ukuran Besar (hanya desktop agar mobile fokus form) -->
                    <div class="hidden lg:flex lg:col-span-5 lg:order-1 flex-col items-center justify-center relative">
                        <div
                            class="relative w-full max-w-[460px] rounded-[30px] bg-white/95 backdrop-blur-md border border-sky/25 p-6 sm:p-8 shadow-neutral flex flex-col items-center text-center">
                            <!-- Badge & Logo di atas judul robot -->
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-[#DBEAFE]/70 border border-sky/30 px-3.5 py-1 text-[11px] font-heading font-extrabold text-sky mb-3">
                                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-4 h-4 object-contain">

                            </div>

                            <!-- Judul besar di atas gambar robot (kalimat bervariasi) -->
                            <h2
                                class="font-heading font-extrabold text-2xl sm:text-3xl text-ink leading-tight tracking-tight">
                                Login &amp; Aktifkan <span class="text-sky">Perisai Digitalmu</span>
                            </h2>
                            <p class="mt-1.5 text-xs text-muted max-w-sm leading-relaxed">
                                Masuk bersama Robot Penjaga Digi-Safe untuk mengakses arena Minigame, menyimpan skor
                                kuis, dan berdiskusi.
                            </p>

                            <!-- Gambar Robot Ukuran Besar -->
                            <div class="my-4 w-full flex items-center justify-center">
                                <img src="{{ asset('images/mascot-login.png') }}" alt="Maskot Robot Digi-Safe Login"
                                    class="w-full max-w-[360px] sm:max-w-[400px] max-h-[370px] object-contain  select-none pointer-events-none">
                            </div>

                            <!-- Bar info bawah maskot -->
                            <div
                                class="pt-3.5 border-t border-gray-100 w-full flex items-center justify-between text-xs text-left">
                                <div>
                                    <span class="font-heading font-bold text-ink block">Siap Menjelajah Ruang
                                        Siber?</span>
                                    <span class="text-[11px] text-muted">Progres modul, level minigame &amp; lencana
                                        tersimpan otomatis</span>
                                </div>
                            </div>

                            <!-- Konektor horizontal dari maskot ke form kanan -->
                            <div class="hidden xl:flex items-center absolute -right-10 top-1/2 -translate-y-1/2">
                                <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                                <div class="w-8 h-[1.5px] bg-[#93C5FD] flow-line-dashed"></div>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6]"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Form Login Node Flowchart -->
                    <div class="lg:col-span-7 lg:order-2 w-full max-w-xl mx-auto lg:max-w-none">

                        <!-- Node 1 (Top Header): Selamat Datang Kembali + Logo Digi-Safe -->
                        <div class="relative flex justify-center">
                            <div class="flow-node w-full max-w-md p-5 sm:p-6 relative z-10">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-11 h-10 rounded-xl bg-[#F8FAFC] border border-slate-200 flex items-center justify-center p-2.5 shrink-0">
                                            <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe"
                                                class="w-full h-full object-contain">
                                        </span>
                                        <div>
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-wider text-sky block">Node:
                                                Autentikasi Akun</span>
                                            <h1 class="font-heading font-extrabold text-lg sm:text-xl text-ink mt-0.5">
                                                Selamat Datang Kembali!
                                            </h1>
                                        </div>
                                    </div>
                                    <span
                                        class="w-2.5 h-2.5 rounded-full bg-lime ring-4 ring-lime/30 mt-1 shrink-0"></span>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <span
                                        class="rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold px-2.5 py-0.5">Security-Flow
                                        v2.0</span>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="w-6 h-6 rounded-md bg-ink text-white flex items-center justify-center text-[10px] font-bold">SSL</span>
                                        <span
                                            class="w-6 h-6 rounded-md bg-sky text-white flex items-center justify-center text-[10px] font-bold">2FA</span>
                                        <span
                                            class="w-6 h-6 rounded-md bg-lime text-ink flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.2">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (session('error') || session('warning'))
                            <div
                                class="mt-4 max-w-md mx-auto flow-node border-amber-300 bg-amber-50/95 px-4 py-3 text-xs font-semibold text-amber-800 text-center">
                                {{ session('error') ?: session('warning') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                class="mt-4 max-w-md mx-auto flow-node border-red-300 bg-red-50/90 px-4 py-3 text-xs font-semibold text-red-600 text-center">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <!-- Garis penghubung bercabang dari Node 1 ke Node 2 & Node 3 -->
                            <div class="hidden md:block relative h-12 max-w-xl mx-auto">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 600 48" fill="none">
                                    <circle cx="300" cy="0" r="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <path d="M 300 4 C 300 28, 150 16, 150 44" stroke="#93C5FD" stroke-width="1.8"
                                        class="flow-line-dashed" />
                                    <circle cx="215" cy="22" r="3.5" fill="#93C5FD" />
                                    <circle cx="150" cy="44" r="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />

                                    <path d="M 300 4 C 300 28, 450 16, 450 48" stroke="#93C5FD" stroke-width="1.8" />
                                    <circle cx="385" cy="22" r="3.5" fill="#3B82F6" />
                                </svg>
                            </div>

                            <!-- Grid Node 2 (Email Kiri) & Node 3 (Password Kanan) -->
                            <div class="mt-4 md:mt-0 grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 items-start">

                                <!-- Node 2 (Kiri): Input Email -->
                                <div class="flow-node p-5 relative">
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="email"
                                            class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-sky"></span>
                                            <span>Input: Alamat Email</span>
                                        </label>
                                        <span class="text-[10px] font-mono text-muted">Node 01</span>
                                    </div>

                                    <p class="text-[11px] text-muted mb-3">Masukkan email akun Digi-Safe Anda</p>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                                <polyline points="22,6 12,13 2,6" />
                                            </svg>
                                        </span>
                                        <input id="email" type="email" name="email" x-model="email" required autofocus
                                            placeholder="nama@email.com"
                                            class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-10 pr-9 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                        <button type="button" x-show="email.length > 0" @click="email = ''"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                            aria-label="Bersihkan">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M18 6L6 18M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Node 3 (Kanan): Input Password -->
                                <div class="md:mt-6 relative">
                                    <div class="hidden md:flex flex-col items-center -mt-6 mb-2">
                                        <span
                                            class="rounded-lg bg-white border border-slate-200 px-3 py-0.5 text-[10px] font-semibold text-muted shadow-2xs">
                                            Verifikasi Sandi
                                        </span>
                                        <div class="h-2.5 w-[1.5px] bg-[#93C5FD]"></div>
                                        <span
                                            class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6] -mb-1.5 z-10"></span>
                                    </div>

                                    <div class="flow-node p-5">
                                        <div class="flex items-center justify-between mb-2">
                                            <label for="password"
                                                class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-lime"></span>
                                                <span>Input: Password</span>
                                            </label>
                                            <span class="text-[10px] font-mono text-muted">Node 02</span>
                                        </div>

                                        <p class="text-[11px] text-muted mb-3">Kata sandi rahasia akun Anda</p>

                                        <div class="relative">
                                            <span
                                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                                </svg>
                                            </span>
                                            <input id="password" :type="showPass ? 'text' : 'password'" name="password"
                                                required placeholder="Masukkan password"
                                                class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-10 pr-10 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                            <button type="button" @click="showPass = !showPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                                aria-label="Tampilkan Password">
                                                <svg x-show="!showPass" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg x-show="showPass" style="display: none;" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.8">
                                                    <path
                                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                                    <line x1="1" y1="1" x2="23" y2="23" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Garis konvergensi ke tombol Masuk -->
                            <div class="hidden md:block relative h-10 max-w-xl mx-auto">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 600 40" fill="none">
                                    <circle cx="150" cy="0" r="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <circle cx="450" cy="0" r="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <path d="M 150 4 C 150 28, 300 14, 300 36" stroke="#93C5FD" stroke-width="1.8" />
                                    <path d="M 450 4 C 450 28, 300 14, 300 36" stroke="#93C5FD" stroke-width="1.8" />
                                    <circle cx="300" cy="36" r="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                </svg>
                            </div>

                            <!-- Tombol Masuk & Login Google berdampingan -->
                            <div class="mt-5 md:mt-0 flex flex-col items-center">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <div class="flow-node p-1.5 rounded-full">
                                        <button type="submit"
                                            class="min-w-[210px] rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs uppercase tracking-wider px-7 py-3.5 flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                            <span>Masuk Sekarang</span>
                                            <svg class="w-4 h-4 text-lime" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                                <polyline points="12 5 19 12 12 19" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flow-node p-1.5 rounded-full">
                                        <a href="{{ route('auth.google.redirect') }}"
                                            class="min-w-[210px] rounded-full bg-white hover:bg-slate-50 border border-slate-200 text-ink font-heading font-bold text-xs px-6 py-3.5 flex items-center justify-center gap-2.5 transition-colors">
                                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="#4285F4"
                                                    d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z" />
                                                <path fill="#34A853"
                                                    d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.72-4.96H1.29v3.14C3.26 21.3 7.31 24 12 24z" />
                                                <path fill="#FBBC05"
                                                    d="M5.28 14.24c-.24-.72-.38-1.49-.38-2.24s.14-1.52.38-2.24V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.99-3.14z" />
                                                <path fill="#EA4335"
                                                    d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.99 3.14c.95-2.85 3.6-4.96 6.72-4.96z" />
                                            </svg>
                                            <span>Masuk dengan Google</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Box Terpisah Khusus Tombol Daftar Akun Baru (Besar & Jelas) -->
                                <div
                                    class="mt-5 w-full max-w-md flow-node p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/95">
                                    <div class="flex items-center gap-3 text-left">
                                        <span
                                            class="w-10 h-10 rounded-xl bg-lime/40 border border-lime flex items-center justify-center text-ink shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="8.5" cy="7" r="4" />
                                                <line x1="20" y1="8" x2="20" y2="14" />
                                                <line x1="23" y1="11" x2="17" y2="11" />
                                            </svg>
                                        </span>
                                        <div>
                                            <span
                                                class="font-heading font-extrabold text-xs sm:text-sm text-ink block">Belum
                                                Punya Akun Digi-Safe?</span>
                                            <span class="text-[11px] text-muted">Buat akun gratis untuk bermain Minigame
                                                &amp; klaim lencana</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('register') }}"
                                        class="w-full sm:w-auto shrink-0 rounded-full bg-lime hover:bg-sky text-ink hover:text-white font-heading font-extrabold text-xs px-6 py-3 flex items-center justify-center gap-2 shadow-xs transition-all hover:-translate-y-0.5">
                                        <span>Daftar Sekarang</span>
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                            <polyline points="12 5 19 12 12 19" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>

            </div>
        </div>
    </section>

</body>

</html>