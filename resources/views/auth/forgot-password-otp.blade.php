<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'en' ? 'Reset Password via OTP' : 'Reset Password via OTP' }} - Digi-Safe Purwakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $isEn = app()->getLocale() === 'en';
@endphp

<body class="bg-white min-h-screen py-3 sm:py-5 px-2.5 sm:px-5 flex flex-col justify-center antialiased selection:bg-lime selection:text-ink">
    <section class="w-full max-w-[1420px] mx-auto">
        <div
            class="relative rounded-[24px] sm:rounded-[32px] border border-sky/25 shadow-neutral overflow-hidden py-8 sm:py-11 px-4 sm:px-8 lg:px-12"
            style="background-image: url('{{ asset('images/auth-bg-2.jpg') }}'); background-size: cover; background-position: center;"
        >
            <div
                class="absolute inset-0 pointer-events-none opacity-65 mix-blend-multiply"
                style="background-image: url('{{ asset('images/auth-bg-1.jpg') }}'); background-size: cover; background-position: right center;"
            ></div>
            <div
                class="absolute inset-0 pointer-events-none opacity-35"
                style="background-image: radial-gradient(#93C5FD 1px, transparent 1px); background-size: 22px 22px;"
            ></div>

            <div
                x-data="{
                    email: @js($prefillEmail),
                    otp: @js(old('otp', '')),
                    password: '',
                    passwordConfirmation: '',
                    showPass: false,
                    showConfirm: false,
                    otpSent: {{ $otpSentInitial || $errors->has('otp') || $errors->has('password') ? 'true' : 'false' }},
                    sendingOtp: false,
                    submittingReset: false,
                    cooldown: 0,
                    cooldownTimer: null,
                    statusMessage: @js(session('status', '')),
                    errorMessage: @js($errors->first() ?: ''),
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
                            this.errorMessage = @js($isEn ? 'Please enter your registered email address.' : 'Masukkan alamat email akun Anda terlebih dahulu.');
                            return;
                        }
                        this.sendingOtp = true;
                        try {
                            const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                            const res = await fetch('{{ route('password.otp.send') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({ email: targetEmail, locale: @js(app()->getLocale()) })
                            });
                            const data = await res.json().catch(() => ({}));
                            if (res.ok && data.ok) {
                                this.otp = '';
                                this.otpSent = true;
                                this.statusMessage = data.message || @js($isEn ? 'OTP code has been sent to your Gmail.' : 'Kode OTP telah dikirim ke Gmail Anda.');
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
                }"
                class="relative z-10 w-full max-w-4xl mx-auto"
            >
                <!-- Bar navigasi atas -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <a href="{{ route('beranda') }}" class="flow-node rounded-full px-4 py-2 inline-flex items-center gap-3 group">
                        <span class="w-9 h-8 rounded-xl bg-white border border-gray-100 shadow-2xs flex items-center justify-center p-2">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta" class="w-full h-full object-contain">
                        </span>
                        <div class="text-left pr-1">
                            <span class="font-heading font-extrabold text-xs sm:text-sm text-ink block leading-none">Digi-Safe Purwakarta</span>
                            <span class="text-[10px] font-semibold text-sky tracking-wider uppercase">{{ $isEn ? 'Account Recovery' : 'Pemulihan Akun' }}</span>
                        </div>
                    </a>

                    <div class="flex flex-wrap items-center gap-2.5">
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

                        @auth
                            <a href="{{ route('profile.edit') }}" class="flow-node rounded-full px-4 py-2.5 text-xs font-heading font-bold text-ink hover:text-sky inline-flex items-center gap-1.5 transition-colors">
                                <span>&larr; {{ $isEn ? 'Back to Profile' : 'Kembali ke Profil' }}</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="flow-node rounded-full px-4 py-2.5 text-xs font-heading font-bold text-ink hover:text-sky inline-flex items-center gap-1.5 transition-colors">
                                <span>&larr; {{ $isEn ? 'Back to Login' : 'Kembali ke Login' }}</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Header Node -->
                <div class="flex justify-center">
                    <div class="flow-node w-full max-w-xl p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-11 h-11 rounded-xl bg-[#EFF6FF] border border-sky/25 text-sky flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 9.9-1" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky block">
                                        {{ $isEn ? 'Gmail OTP Verification' : 'Verifikasi OTP Gmail' }}
                                    </span>
                                    <h1 class="font-heading font-extrabold text-lg sm:text-xl text-ink mt-0.5">
                                        {{ $isEn ? 'Reset Your Password' : 'Reset Password Akun' }}
                                    </h1>
                                </div>
                            </div>
                            <span class="rounded-lg bg-lime/40 border border-lime px-2.5 py-1 text-[10px] font-heading font-extrabold text-ink shrink-0">
                                6-Digit OTP
                            </span>
                        </div>
                        <p class="mt-3 text-xs text-muted leading-relaxed">
                            {{ $isEn
                                ? 'Enter your registered email address to receive a 6-digit OTP code in your Gmail inbox, then set a new password.'
                                : 'Masukkan alamat email yang terdaftar untuk menerima kode OTP 6 digit di kotak masuk Gmail Anda, lalu buat password baru.' }}
                        </p>
                    </div>
                </div>

                <!-- Status / Error Alert -->
                <div
                    x-show="statusMessage !== ''"
                    x-transition
                    style="display: none;"
                    role="status"
                    class="mt-4 max-w-xl mx-auto flow-node border-emerald-300 bg-emerald-50/95 px-4 py-3 text-xs font-semibold text-emerald-800 text-center"
                    x-text="statusMessage"
                ></div>

                <div
                    x-show="errorMessage !== ''"
                    x-transition
                    style="display: none;"
                    role="alert"
                    class="mt-4 max-w-xl mx-auto flow-node border-red-300 bg-red-50/95 px-4 py-3 text-xs font-semibold text-red-700 text-center"
                    x-text="errorMessage"
                ></div>

                <!-- Langkah 1: Kirim Kode OTP ke Gmail -->
                <div class="mt-5 max-w-xl mx-auto flow-node p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <label for="reset-email" class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-sky text-white text-[10px] font-extrabold inline-flex items-center justify-center">1</span>
                            <span>{{ $isEn ? 'Registered Email (Gmail)' : 'Email Terdaftar (Gmail)' }}</span>
                        </label>
                        <button
                            type="button"
                            x-show="!otpSent"
                            @click="otpSent = true"
                            class="text-[11px] font-semibold text-sky hover:underline cursor-pointer"
                        >
                            {{ $isEn ? 'Already have OTP?' : 'Sudah punya kode OTP?' }}
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <div class="relative flex-1">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6" />
                                </svg>
                            </span>
                            <input
                                id="reset-email"
                                type="email"
                                x-model="email"
                                required
                                autocomplete="email"
                                placeholder="nama@gmail.com"
                                @auth readonly @endauth
                                class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all {{ auth()->check() ? 'bg-slate-100 cursor-not-allowed' : 'bg-slate-50 focus:bg-white' }}"
                            >
                        </div>

                        <button
                            type="button"
                            @click="sendOtp()"
                            :disabled="sendingOtp || cooldown > 0"
                            class="shrink-0 rounded-xl bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-5 py-2.5 min-h-[44px] inline-flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span x-show="sendingOtp" class="w-3.5 h-3.5 border-2 border-lime border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                            <span x-text="sendingOtp
                                ? @js($isEn ? 'Sending...' : 'Mengirim...')
                                : (cooldown > 0
                                    ? (@js($isEn ? 'Resend in ' : 'Kirim ulang (') + cooldown + @js($isEn ? 's' : 'd)'))
                                    : (otpSent ? @js($isEn ? 'Resend OTP' : 'Kirim Ulang OTP') : @js($isEn ? 'Send OTP to Gmail' : 'Kirim OTP ke Gmail')))"></span>
                        </button>
                    </div>
                </div>

                <!-- Langkah 2: Verifikasi OTP & Password Baru -->
                <form
                    x-show="otpSent"
                    x-transition
                    method="POST"
                    action="{{ route('password.otp.reset') }}"
                    @submit="submittingReset = true"
                    class="mt-4 max-w-xl mx-auto flow-node p-5 sm:p-6 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="email" :value="email">

                    <div class="flex items-center justify-between gap-2 pb-2 border-b border-gray-100">
                        <span class="font-heading font-bold text-xs text-ink flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-lime text-ink text-[10px] font-extrabold inline-flex items-center justify-center">2</span>
                            <span>{{ $isEn ? 'Enter OTP Code & New Password' : 'Masukkan Kode OTP & Password Baru' }}</span>
                        </span>
                        <span class="text-[10px] font-semibold text-muted">{{ $isEn ? 'Valid 10 mins' : 'Berlaku 10 menit' }}</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="otp-code" class="block text-xs font-heading font-bold text-ink">
                                {{ $isEn ? '6-Digit OTP Code' : 'Kode OTP 6 Digit' }}
                            </label>
                            <button
                                type="button"
                                @click="sendOtp()"
                                :disabled="sendingOtp || cooldown > 0"
                                class="text-[11px] font-semibold text-sky hover:underline cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1"
                            >
                                <span x-show="sendingOtp" class="w-3 h-3 border-2 border-sky border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                <span x-text="cooldown > 0
                                    ? (@js($isEn ? 'Resend (' : 'Kirim ulang (') + cooldown + @js($isEn ? 's)' : 'd)'))
                                    : @js($isEn ? 'Resend OTP' : 'Kirim Ulang OTP')"></span>
                            </button>
                        </div>
                        <input
                            id="otp-code"
                            type="text"
                            name="otp"
                            x-model="otp"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required
                            autocomplete="one-time-code"
                            placeholder="000000"
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-center font-mono font-extrabold text-lg tracking-[0.45em] text-ink placeholder:text-gray-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all"
                        >
                        <p class="mt-1.5 text-[11px] text-muted">
                            {{ $isEn
                                ? 'Enter the latest 6-digit code sent to your Gmail inbox or spam.'
                                : 'Masukkan 6 digit kode OTP terbaru yang masuk di kotak masuk atau spam Gmail Anda.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="new-password" class="block text-xs font-heading font-bold text-ink">
                                    {{ $isEn ? 'New Password' : 'Password Baru' }}
                                </label>
                                <span
                                    x-show="password.length > 0"
                                    style="display: none;"
                                    class="text-[10px] font-bold"
                                    :class="password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)
                                        ? 'text-emerald-600'
                                        : (password.length >= 8 ? 'text-amber-600' : 'text-red-600')"
                                    x-text="password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)
                                        ? @js($isEn ? 'Strong' : 'Kuat')
                                        : (password.length >= 8 ? @js($isEn ? 'Medium' : 'Sedang') : @js($isEn ? 'Weak' : 'Lemah'))"
                                ></span>
                            </div>
                            <div class="relative">
                                <input
                                    id="new-password"
                                    :type="showPass ? 'text' : 'password'"
                                    name="password"
                                    x-model="password"
                                    required
                                    minlength="8"
                                    maxlength="128"
                                    autocomplete="new-password"
                                    placeholder="{{ $isEn ? 'Minimum 8 characters' : 'Minimal 8 karakter' }}"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-3.5 pr-10 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all"
                                >
                                <button
                                    type="button"
                                    @click="showPass = !showPass"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                    aria-label="{{ $isEn ? 'Toggle password visibility' : 'Tampilkan atau sembunyikan password' }}"
                                >
                                    <svg x-show="!showPass" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg x-show="showPass" style="display: none;" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="new-password-confirm" class="block text-xs font-heading font-bold text-ink">
                                    {{ $isEn ? 'Confirm New Password' : 'Konfirmasi Password Baru' }}
                                </label>
                                <span
                                    x-show="passwordConfirmation.length > 0"
                                    style="display: none;"
                                    class="text-[10px] font-bold"
                                    :class="password === passwordConfirmation ? 'text-emerald-600' : 'text-red-600'"
                                    x-text="password === passwordConfirmation ? @js($isEn ? 'Matched' : 'Cocok') : @js($isEn ? 'Not matching' : 'Belum cocok')"
                                ></span>
                            </div>
                            <div class="relative">
                                <input
                                    id="new-password-confirm"
                                    :type="showConfirm ? 'text' : 'password'"
                                    name="password_confirmation"
                                    x-model="passwordConfirmation"
                                    required
                                    minlength="8"
                                    maxlength="128"
                                    autocomplete="new-password"
                                    placeholder="{{ $isEn ? 'Repeat new password' : 'Ulangi password baru' }}"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-3.5 pr-10 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 focus:outline-none transition-all"
                                >
                                <button
                                    type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ink p-1 cursor-pointer"
                                    aria-label="{{ $isEn ? 'Toggle confirmation visibility' : 'Tampilkan atau sembunyikan konfirmasi password' }}"
                                >
                                    <svg x-show="!showConfirm" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg x-show="showConfirm" style="display: none;" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-[11px] text-muted">
                            {{ $isEn ? 'Check Spam or Promotions folder if the email does not arrive.' : 'Cek folder Spam atau Promosi jika email OTP belum muncul di kotak masuk.' }}
                        </p>
                        <button
                            type="submit"
                            :disabled="submittingReset"
                            class="w-full sm:w-auto shrink-0 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-7 py-3 min-h-[44px] inline-flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-60"
                        >
                            <span x-show="submittingReset" class="w-3.5 h-3.5 border-2 border-lime border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                            <span>{{ $isEn ? 'Reset Password Now' : 'Reset Password Sekarang' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
