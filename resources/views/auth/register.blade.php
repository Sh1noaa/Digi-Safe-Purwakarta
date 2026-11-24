@php
    $isEn = app()->getLocale() === 'en';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEn ? 'Register Account' : 'Daftar Akun' }} - Digi-Safe Purwakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-white min-h-screen py-2.5 sm:py-4 px-2.5 sm:px-5 flex flex-col justify-center antialiased selection:bg-lime selection:text-ink">

    <section class="w-full max-w-[1420px] mx-auto">
        <div class="relative rounded-[24px] sm:rounded-[32px] border border-sky/25 shadow-neutral overflow-hidden py-4 sm:py-5 lg:py-6 px-4 sm:px-8 lg:px-10"
            style="background-image: url('{{ asset('images/auth-bg-1.jpg') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 pointer-events-none opacity-65 mix-blend-multiply"
                style="background-image: url('{{ asset('images/auth-bg-2.jpg') }}'); background-size: cover; background-position: center;">
            </div>

            <div class="absolute inset-0 pointer-events-none opacity-35"
                style="background-image: radial-gradient(#93C5FD 1px, transparent 1px); background-size: 22px 22px;">
            </div>

            <div x-data="{
                    nama_lengkap: @js(old('nama_lengkap', '')),
                    username: @js(old('username', '')),
                    email: @js(old('email', session('otp_register_email', ''))),
                    otp: @js(old('otp', '')),
                    password: '',
                    password_confirmation: '',
                    showPass: false,
                    showConfirm: false,
                    submitting: false,
                    otpSent: {{ ($otpSentInitial ?? false) || $errors->has('otp') || old('otp') ? 'true' : 'false' }},
                    sendingOtp: false,
                    cooldown: 0,
                    cooldownTimer: null,
                    statusMessage: @js(session('status', '')),
                    errorMessage: @js($errors->first() ?: ''),
                    strengthLevel() {
                        const p = this.password || '';
                        if (p.length === 0) return 0;
                        let s = 1;
                        if (p.length >= 8) s++;
                        if (/[A-Z]/.test(p) && /[0-9]/.test(p)) s++;
                        return s;
                    },
                    strengthLabel() {
                        const lvl = this.strengthLevel();
                        if (lvl === 0) return @js($isEn ? 'Min. 8 chars' : 'Min. 8 karakter');
                        if (lvl === 1) return @js($isEn ? 'Weak' : 'Lemah');
                        if (lvl === 2) return @js($isEn ? 'Medium' : 'Sedang');
                        return @js($isEn ? 'Strong' : 'Kuat');
                    },
                    passwordsMatch() {
                        return this.password_confirmation.length > 0 && this.password === this.password_confirmation;
                    },
                    startCooldown(seconds) {
                        this.cooldown = Math.max(0, Number(seconds) || 0);
                        if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                        if (this.cooldown <= 0) return;
                        this.cooldownTimer = setInterval(() => {
                            this.cooldown--;
                            if (this.cooldown <= 0) {
                                clearInterval(this.cooldownTimer);
                                this.cooldownTimer = null;
                            }
                        }, 1000);
                    },
                    async sendOtp() {
                        if (this.sendingOtp || this.cooldown > 0) return;
                        this.errorMessage = '';
                        this.statusMessage = '';
                        const targetEmail = (this.email || '').trim();
                        if (!targetEmail) {
                            this.errorMessage = @js($isEn ? 'Please enter your email address first before requesting OTP.' : 'Masukkan alamat email Anda terlebih dahulu sebelum meminta kode OTP.');
                            return;
                        }
                        this.sendingOtp = true;
                        try {
                            const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                            const payload = {
                                email: targetEmail,
                                locale: @js(app()->getLocale()),
                            };
                            if ((this.nama_lengkap || '').trim().length >= 2) {
                                payload.nama_lengkap = this.nama_lengkap.trim();
                            }
                            if ((this.username || '').trim().length >= 3) {
                                payload.username = this.username.trim();
                            }
                            const res = await fetch('{{ route('register.otp.send') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify(payload)
                            });
                            const data = await res.json().catch(() => ({}));
                            if (res.ok && data.ok) {
                                this.otp = '';
                                this.otpSent = true;
                                this.statusMessage = data.message || @js($isEn ? '6-digit OTP code has been sent to your email.' : 'Kode OTP 6 digit telah dikirim ke email Anda.');
                                this.startCooldown(data.cooldown || 20);
                            } else {
                                const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                                this.errorMessage = firstErr || data.message || @js($isEn ? 'Unable to send OTP code.' : 'Gagal mengirim kode OTP.');
                                if (data.retry_after) {
                                    this.otpSent = true;
                                    this.startCooldown(data.retry_after);
                                }
                            }
                        } catch (_) {
                            this.errorMessage = @js($isEn ? 'Network error. Please try again.' : 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                        } finally {
                            this.sendingOtp = false;
                        }
                    }
                }" class="relative z-10 w-full max-w-6xl mx-auto">
                <!-- Bar navigasi atas dengan Logo + Pilihan Bahasa ID/EN + Tombol Login -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4 lg:mb-5">
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
                            <span class="text-[10px] font-semibold text-sky tracking-wider uppercase">{{ $isEn ? 'Cyber Citizen Onboarding' : 'Onboarding Warga Siber' }}</span>
                        </div>
                    </a>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Pilihan Bahasa ID / EN -->
                        <div class="inline-flex items-center rounded-full bg-white/95 border border-slate-200 p-1 shadow-2xs" role="group" aria-label="{{ $isEn ? 'Select language' : 'Pilih bahasa' }}">
                            <form method="POST" action="{{ route('preferences.quick') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="locale" value="id">
                                <button type="submit"
                                    class="rounded-full px-3 py-1.5 text-[11px] font-heading font-extrabold tracking-wide transition-colors cursor-pointer {{ ! $isEn ? 'bg-ink text-white' : 'text-muted hover:text-ink' }}">
                                    ID
                                </button>
                            </form>
                            <form method="POST" action="{{ route('preferences.quick') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="locale" value="en">
                                <button type="submit"
                                    class="rounded-full px-3 py-1.5 text-[11px] font-heading font-extrabold tracking-wide transition-colors cursor-pointer {{ $isEn ? 'bg-ink text-white' : 'text-muted hover:text-ink' }}">
                                    EN
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('beranda') }}"
                            class="flow-node rounded-full px-4 py-2.5 text-xs font-heading font-bold text-ink hover:text-sky inline-flex items-center gap-1.5 transition-colors">
                            <span>&larr; {{ $isEn ? 'Back to Home' : 'Kembali ke Beranda' }}</span>
                        </a>

                        <a href="{{ route('login') }}"
                            class="rounded-full bg-ink hover:bg-sky text-white font-heading font-extrabold text-xs px-5 py-2.5 shadow-sm inline-flex items-center gap-2 transition-all hover:-translate-y-0.5">
                            <svg class="w-4 h-4 text-lime" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                <polyline points="10 17 15 12 10 7" />
                                <line x1="15" y1="12" x2="3" y2="12" />
                            </svg>
                            <span>{{ $isEn ? 'Sign In' : 'Masuk (Login)' }}</span>
                        </a>
                    </div>
                </div>

                <!-- Layout 2 Kolom Desktop -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

                    <!-- Kolom Kiri: Form Register -->
                    <div class="lg:col-span-7 lg:order-1 w-full max-w-xl mx-auto lg:max-w-none">

                        <div class="relative flex justify-center">
                            <div class="flow-node w-full max-w-md p-4 relative z-10">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-11 h-10 rounded-xl bg-[#F8FAFC] border border-slate-200 flex items-center justify-center p-2.5 shrink-0">
                                            <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe"
                                                class="w-full h-full object-contain">
                                        </span>
                                        <div>
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-wider text-sky block">{{ $isEn ? 'Node: Cyber Citizen Registration' : 'Node: Registrasi Warga Siber' }}</span>
                                            <h1 class="font-heading font-extrabold text-lg sm:text-xl text-ink mt-0.5">
                                                {{ $isEn ? 'Create Your Digi-Safe Account' : 'Buat Akun Baru Digi-Safe' }}
                                            </h1>
                                        </div>
                                    </div>
                                    <span
                                        class="w-2.5 h-2.5 rounded-full bg-lime ring-4 ring-lime/30 mt-1 shrink-0"></span>
                                </div>

                                <div class="mt-2.5 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                                    <span
                                        class="rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold px-2.5 py-0.5">User-Tree
                                        Identity</span>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="w-6 h-6 rounded-md bg-sky text-white flex items-center justify-center text-[10px] font-bold">ID</span>
                                        <span
                                            class="w-6 h-6 rounded-md bg-ink text-white flex items-center justify-center text-[10px] font-bold">@</span>
                                        <span
                                            class="w-6 h-6 rounded-md bg-lime text-ink flex items-center justify-center text-[10px] font-bold">✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            x-show="statusMessage !== ''"
                            x-transition
                            style="display: none;"
                            role="status"
                            class="mt-3 max-w-lg mx-auto flow-node border-emerald-300 bg-emerald-50/95 px-4 py-2.5 text-xs font-semibold text-emerald-800 text-center"
                            x-text="statusMessage"
                        ></div>

                        <div
                            x-show="errorMessage !== ''"
                            x-transition
                            style="display: none;"
                            role="alert"
                            class="mt-3 max-w-lg mx-auto flow-node border-red-300 bg-red-50/95 px-4 py-2.5 text-xs font-semibold text-red-700 text-center"
                            x-text="errorMessage"
                        ></div>

                        <form method="POST" action="{{ route('register.store') }}" @submit="submitting = true">
                            @csrf

                            <div class="hidden md:block relative h-7 max-w-xl mx-auto">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 600 28" fill="none">
                                    <circle cx="300" cy="0" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <path d="M 300 3 C 300 16, 150 10, 150 26" stroke="#93C5FD" stroke-width="1.8"
                                        class="flow-line-dashed" />
                                    <circle cx="150" cy="26" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />

                                    <path d="M 300 3 C 300 16, 450 10, 450 26" stroke="#93C5FD" stroke-width="1.8" />
                                    <circle cx="450" cy="26" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                </svg>
                            </div>

                            <!-- Baris Cabang Atas: Nickname & Username -->
                            <div class="mt-4 md:mt-0 grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 items-start">

                                <div class="flow-node p-3.5 sm:p-4">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="nama_lengkap"
                                            class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-sky"></span>
                                            <span>{{ $isEn ? 'Display Name / Full Name' : 'Nickname / Nama Lengkap' }}</span>
                                        </label>
                                        <span class="text-[10px] font-mono text-muted">Node 01</span>
                                    </div>
                                    <p class="text-[11px] text-muted mb-3">{{ $isEn ? 'Shown on your profile, home & forum' : 'Nama tampilan di profil, beranda & forum' }}</p>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                                <circle cx="12" cy="7" r="4" />
                                            </svg>
                                        </span>
                                        <input id="nama_lengkap" type="text" name="nama_lengkap" x-model="nama_lengkap"
                                            required autofocus placeholder="{{ $isEn ? 'Your full name' : 'Nama lengkap Anda' }}"
                                            class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-10 pr-4 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                    </div>
                                </div>

                                <div class="flow-node p-3.5 sm:p-4 md:mt-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="username"
                                            class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-purple-deep"></span>
                                            <span>{{ $isEn ? 'Username (Unique)' : 'Username (Unik)' }}</span>
                                        </label>
                                        <span class="text-[10px] font-mono text-muted">Node 02</span>
                                    </div>
                                    <p class="text-[11px] text-muted mb-3">{{ $isEn ? 'Letters, numbers, or underscores' : 'Huruf, angka, atau garis bawah' }}</p>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted font-mono font-bold text-xs pointer-events-none">@</span>
                                        <input id="username" type="text" name="username" x-model="username" required
                                            placeholder="{{ $isEn ? 'unique_username' : 'username_unik' }}"
                                            class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-9 pr-4 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                    </div>
                                </div>

                            </div>

                            <div class="hidden md:grid grid-cols-2 gap-6">
                                <div class="flex flex-col items-center -mt-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                                    <div class="h-5 w-[1.5px] bg-[#93C5FD]"></div>
                                    <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                                </div>
                                <div class="flex flex-col items-center pt-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                                    <div class="h-5 w-[1.5px] bg-[#93C5FD]"></div>
                                    <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                                </div>
                            </div>

                            <!-- Baris Cabang Bawah: Email & Password -->
                            <div class="mt-5 md:mt-0 grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 items-start">

                                <div class="flow-node p-3.5 sm:p-4">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="email"
                                            class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-sky"></span>
                                            <span>{{ $isEn ? 'Email Address (OTP)' : 'Alamat Email (OTP)' }}</span>
                                        </label>
                                        <span class="text-[10px] font-mono text-muted">Node 03</span>
                                    </div>
                                    <p class="text-[11px] text-muted mb-3">{{ $isEn ? 'We will send a 6-digit OTP to verify this email' : 'Kode OTP 6 digit akan dikirim ke email ini' }}</p>

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
                                        <input id="email" type="email" name="email" x-model="email" required
                                            placeholder="{{ $isEn ? 'name@gmail.com' : 'nama@gmail.com' }}"
                                            class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-10 pr-4 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                    </div>

                                    <div class="mt-2.5 flex items-center justify-between gap-2">
                                        <button
                                            type="button"
                                            @click="sendOtp()"
                                            :disabled="sendingOtp || cooldown > 0"
                                            class="flex-1 rounded-xl bg-sky hover:bg-ink text-white font-heading font-bold text-[11px] px-3.5 py-2 min-h-[38px] inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                        >
                                            <span x-show="sendingOtp" class="w-3 h-3 border-2 border-lime border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                            <span x-text="sendingOtp
                                                ? @js($isEn ? 'Sending OTP...' : 'Mengirim OTP...')
                                                : (cooldown > 0
                                                    ? (@js($isEn ? 'Resend in ' : 'Kirim ulang (') + cooldown + @js($isEn ? 's' : 'd)'))
                                                    : (otpSent ? @js($isEn ? 'Resend 6-Digit OTP' : 'Kirim Ulang OTP') : @js($isEn ? 'Send 6-Digit OTP' : 'Kirim Kode OTP')))"></span>
                                        </button>
                                        <button
                                            type="button"
                                            x-show="!otpSent"
                                            @click="otpSent = true"
                                            class="text-[10.5px] font-semibold text-sky hover:underline shrink-0 cursor-pointer"
                                        >
                                            {{ $isEn ? 'Have OTP?' : 'Punya OTP?' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="flow-node p-3.5 sm:p-4 md:mt-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="password"
                                            class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-lime"></span>
                                            <span>Password</span>
                                        </label>
                                        <span class="text-[10px] font-bold"
                                            :class="strengthLevel() === 3 ? 'text-emerald-600' : (strengthLevel() === 2 ? 'text-amber-600' : 'text-muted')"
                                            x-text="strengthLabel()"></span>
                                    </div>
                                    <p class="text-[11px] text-muted mb-3">{{ $isEn ? 'Min. 8 characters, letters & numbers' : 'Min. 8 karakter huruf & angka' }}</p>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                            </svg>
                                        </span>
                                        <input id="password" :type="showPass ? 'text' : 'password'" name="password"
                                            x-model="password" required minlength="8" maxlength="128" autocomplete="new-password" placeholder="{{ $isEn ? 'Create a strong password' : 'Buat password aman' }}"
                                            class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-10 pr-10 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                        <button type="button" @click="showPass = !showPass"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                            aria-label="{{ $isEn ? 'Toggle password visibility' : 'Tampilkan Password' }}">
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

                                    <div class="mt-2 grid grid-cols-3 gap-1.5">
                                        <div class="h-1 rounded-full transition-colors"
                                            :class="strengthLevel() >= 1 ? 'bg-red-400' : 'bg-slate-200'"></div>
                                        <div class="h-1 rounded-full transition-colors"
                                            :class="strengthLevel() >= 2 ? 'bg-amber-400' : 'bg-slate-200'"></div>
                                        <div class="h-1 rounded-full transition-colors"
                                            :class="strengthLevel() >= 3 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                                    </div>

                                    <!-- Konfirmasi Password -->
                                    <div class="mt-2.5">
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="password_confirmation" class="text-[11px] font-heading font-bold text-ink">
                                                {{ $isEn ? 'Confirm Password' : 'Konfirmasi Password' }}
                                            </label>
                                            <span
                                                x-show="password_confirmation.length > 0"
                                                style="display: none;"
                                                class="text-[10px] font-bold"
                                                :class="passwordsMatch() ? 'text-emerald-600' : 'text-red-600'"
                                                x-text="passwordsMatch() ? @js($isEn ? 'Matched' : 'Cocok') : @js($isEn ? 'Not matching' : 'Belum cocok')"
                                            ></span>
                                        </div>
                                        <div class="relative">
                                            <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                                x-model="password_confirmation" required minlength="8" maxlength="128" autocomplete="new-password"
                                                placeholder="{{ $isEn ? 'Repeat password' : 'Ulangi password' }}"
                                                class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-3.5 pr-10 py-2 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all">
                                            <button type="button" @click="showConfirm = !showConfirm"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                                aria-label="{{ $isEn ? 'Toggle confirmation visibility' : 'Tampilkan konfirmasi password' }}">
                                                <svg x-show="!showConfirm" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg x-show="showConfirm" style="display: none;" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                                    <line x1="1" y1="1" x2="23" y2="23" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="hidden md:block relative h-6 max-w-xl mx-auto">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 600 24" fill="none">
                                    <circle cx="150" cy="0" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <circle cx="450" cy="0" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                    <path d="M 150 3 C 150 15, 300 9, 300 22" stroke="#93C5FD" stroke-width="1.8" />
                                    <path d="M 450 3 C 450 15, 300 9, 300 22" stroke="#93C5FD" stroke-width="1.8" />
                                    <circle cx="300" cy="22" r="3.5" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2" />
                                </svg>
                            </div>

                            <!-- Node 05: Verifikasi Kode OTP 6 Digit -->
                            <div class="mt-4 md:mt-0 max-w-lg mx-auto flow-node p-3.5 sm:p-4"
                                :class="otpSent ? 'border-sky/60 ring-2 ring-sky/10' : ''">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <label for="register-otp"
                                        class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-lime text-ink text-[10px] font-extrabold inline-flex items-center justify-center">OTP</span>
                                        <span>{{ $isEn ? '6-Digit Email Verification Code' : 'Kode Verifikasi OTP Email (6 Digit)' }}</span>
                                    </label>
                                    <span class="text-[10px] font-semibold text-muted">{{ $isEn ? 'Valid 10 mins' : 'Berlaku 10 menit' }}</span>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                    <input
                                        id="register-otp"
                                        type="text"
                                        name="otp"
                                        x-model="otp"
                                        inputmode="numeric"
                                        pattern="[0-9]{6}"
                                        maxlength="6"
                                        required
                                        autocomplete="one-time-code"
                                        placeholder="000000"
                                        class="flex-1 rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-center font-mono font-extrabold text-base tracking-[0.42em] text-ink placeholder:text-gray-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all"
                                    >
                                    <button
                                        type="button"
                                        @click="sendOtp()"
                                        :disabled="sendingOtp || cooldown > 0"
                                        class="shrink-0 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-ink font-heading font-bold text-[11px] px-4 py-2.5 min-h-[42px] inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                    >
                                        <span x-show="sendingOtp" class="w-3 h-3 border-2 border-sky border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                        <span x-text="cooldown > 0
                                            ? (@js($isEn ? 'Wait ' : 'Tunggu ') + cooldown + @js($isEn ? 's' : 'd'))
                                            : (otpSent ? @js($isEn ? 'Resend OTP' : 'Kirim Ulang') : @js($isEn ? 'Get OTP' : 'Kirim OTP'))"></span>
                                    </button>
                                </div>
                                <p class="mt-1.5 text-[11px] text-muted">
                                    {{ $isEn
                                        ? 'Click "Send 6-Digit OTP" above, then enter the code from your inbox or spam folder.'
                                        : 'Klik "Kirim Kode OTP", lalu masukkan 6 digit kode dari kotak masuk atau folder spam email Anda.' }}
                                </p>
                            </div>

                            <!-- Tombol Daftar & Google -->
                            <div class="mt-4 flex flex-col items-center">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <div class="flow-node p-1.5 rounded-full">
                                        <button type="submit"
                                            :disabled="submitting"
                                            class="min-w-[210px] rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs uppercase tracking-wider px-7 py-3.5 flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span x-show="submitting" style="display: none;" class="w-3.5 h-3.5 border-2 border-lime border-t-transparent rounded-full animate-spin"></span>
                                            <span>{{ $isEn ? 'Register Now' : 'Daftar Sekarang' }}</span>
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
                                            <span>{{ $isEn ? 'Sign Up with Google' : 'Daftar dengan Google' }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>

                    <!-- Kolom Kanan: Maskot Desktop -->
                    <div class="hidden lg:flex lg:col-span-5 lg:order-2 flex-col items-center justify-center relative">
                        <div
                            class="relative w-full max-w-[440px] rounded-[28px] bg-white/95 backdrop-blur-md border border-sky/25 p-5 sm:p-6 shadow-neutral flex flex-col items-center text-center">
                            <div class="hidden xl:flex items-center absolute -left-10 top-1/2 -translate-y-1/2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6]"></span>
                                <div class="w-8 h-[1.5px] bg-[#93C5FD] flow-line-dashed"></div>
                                <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-[#3B82F6]"></span>
                            </div>

                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-white/40 border border-white px-3.5 py-1 text-[11px] font-heading font-extrabold text-ink mb-3">
                                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-4 h-4 object-contain">
                            </div>

                            <h2
                                class="font-heading font-extrabold text-2xl sm:text-3xl text-ink leading-tight tracking-tight">
                                @if ($isEn)
                                    Register &amp; Become a <span class="text-sky">Smart Cyber Citizen</span>
                                @else
                                    Register &amp; Jadilah <span class="text-sky">Agen Siber Cerdas</span>
                                @endif
                            </h2>
                            <p class="mt-1.5 text-xs text-muted max-w-sm leading-relaxed">
                                {{ $isEn
                                    ? 'Join the Digi-Safe Robot to unlock every Minigame challenge level, Achievement Badges, and community profile.'
                                    : 'Bergabung bersama Robot Digi-Safe untuk membuka seluruh level tantangan Minigame, Lencana Achievement, dan profil komunitas.' }}
                            </p>

                            <div class="my-3 w-full flex items-center justify-center">
                                <img src="{{ asset('images/mascot-register.png') }}"
                                    alt="Maskot Robot Digi-Safe Register"
                                    class="w-full max-w-[280px] sm:max-w-[320px] max-h-[250px] xl:max-h-[285px] object-contain select-none pointer-events-none">
                            </div>

                            <div
                                class="pt-3.5 border-t border-gray-100 w-full flex items-center justify-between text-xs text-left">
                                <div>
                                    <span class="font-heading font-bold text-ink block">{{ $isEn ? 'Champion Digital Safety!' : 'Suarakan Keamanan Digital!' }}</span>
                                    <span class="text-[11px] text-muted">{{ $isEn ? 'Full access to 5+ minigame levels, forum & cyber citizen directory' : 'Akses penuh 5+ level minigame, forum & direktori warga siber' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

</body>

</html>
