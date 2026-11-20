<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'en' ? 'Customize Your Profile - Digi-Safe Purwakarta' : 'Kustomisasi Profil - Digi-Safe Purwakarta' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@1&family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .onboard-grid-bg {
            background-color: #FFFFFF;
            background-image:
                linear-gradient(to right, rgba(148, 163, 184, 0.13) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148, 163, 184, 0.13) 1px, transparent 1px);
            background-size: 56px 56px;
        }
        .onboard-script {
            font-family: 'Instrument Serif', Georgia, serif;
            font-style: italic;
        }
    </style>
</head>

@php
    $isEn = app()->getLocale() === 'en';
    $initialAvatar = $user->avatar_url ?: '';
    $initialBanner = $user->banner_url ?: '';
@endphp

<body class="onboard-grid-bg min-h-screen text-[#0A0A0A] antialiased selection:bg-lime selection:text-ink overflow-x-hidden relative">

    <div
        x-data="{
            nama_lengkap: @js(old('nama_lengkap', $user->nama_lengkap ?? '')),
            username: @js(old('username', $user->username ?? '')),
            avatarPreview: @js($initialAvatar),
            bannerPreview: @js($initialBanner),
            avatarName: '',
            bannerName: '',
            submitting: false,
            errorMsg: @js($errors->first() ?? ''),
            loadingStage: false,
            progress: 0,
            statusIndex: 0,
            statuses: @js($isEn
                ? ['Saving your identity...', 'Configuring your cyber shield...', 'Almost ready! Opening Digi-Safe...']
                : ['Menyimpan identitas profilmu...', 'Menyiapkan ruang aman Digi-Safe...', 'Siap! Mengarahkan ke Beranda...']
            ),
            onAvatarChange(e) {
                const f = e.target.files && e.target.files[0];
                if (!f) return;
                this.avatarName = f.name;
                const r = new FileReader();
                r.onload = (ev) => { this.avatarPreview = ev.target.result; };
                r.readAsDataURL(f);
            },
            onBannerChange(e) {
                const f = e.target.files && e.target.files[0];
                if (!f) return;
                this.bannerName = f.name;
                const r = new FileReader();
                r.onload = (ev) => { this.bannerPreview = ev.target.result; };
                r.readAsDataURL(f);
            },
            async submitCustomize() {
                this.errorMsg = '';
                const cleanNick = (this.nama_lengkap || '').trim();
                const cleanUser = (this.username || '').trim();

                if (cleanNick.length < 2) {
                    this.errorMsg = @js($isEn ? 'Nickname is required (min. 2 characters).' : 'Nickname wajib diisi (minimal 2 karakter).');
                    return;
                }
                if (!/^[a-zA-Z0-9_-]{3,50}$/.test(cleanUser)) {
                    this.errorMsg = @js($isEn ? 'Username must be 3-50 characters (letters, numbers, - or _).' : 'Username minimal 3 karakter (hanya huruf, angka, strip, atau garis bawah).');
                    return;
                }

                this.submitting = true;
                try {
                    const formEl = this.$refs.customizeForm;
                    const fd = new FormData(formEl);
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

                    const res = await fetch(formEl.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: fd
                    });

                    const payload = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const firstErr = payload.errors ? Object.values(payload.errors)[0]?.[0] : null;
                        this.errorMsg = firstErr || payload.message || @js($isEn ? 'Please check your inputs and try again.' : 'Periksa kembali isian Anda dan coba lagi.');
                        this.submitting = false;
                        return;
                    }

                    const targetUrl = payload.redirect || @js($redirectTarget);
                    this.startMascotLoading(targetUrl);
                } catch (_) {
                    this.errorMsg = @js($isEn ? 'Network error. Please try again.' : 'Terjadi gangguan jaringan. Silakan coba lagi.');
                    this.submitting = false;
                }
            },
            startMascotLoading(targetUrl) {
                this.loadingStage = true;
                this.progress = 8;
                this.statusIndex = 0;

                const start = performance.now();
                const duration = 2200;

                const tick = (now) => {
                    const t = Math.min(1, (now - start) / duration);
                    // kurva easing halus 8% -> 100%
                    const eased = 1 - Math.pow(1 - t, 2.4);
                    this.progress = Math.round(8 + eased * 92);

                    if (this.progress >= 74) {
                        this.statusIndex = 2;
                    } else if (this.progress >= 38) {
                        this.statusIndex = 1;
                    } else {
                        this.statusIndex = 0;
                    }

                    if (t < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        setTimeout(() => {
                            window.location.replace(targetUrl);
                        }, 260);
                    }
                };

                requestAnimationFrame(tick);
            }
        }"
        class="relative min-h-screen flex flex-col justify-between px-4 sm:px-6 pt-7 sm:pt-9 pb-10 overflow-hidden"
    >
        <!-- Ilustrasi doodle warga ceria warna-warni di bagian bawah kiri & kanan (persis nuansa referensi) -->
        <div class="pointer-events-none select-none fixed inset-x-0 bottom-0 z-0 overflow-hidden flex items-end justify-between opacity-95" aria-hidden="true">
            <!-- Doodle Kiri -->
            <svg class="w-[340px] sm:w-[460px] lg:w-[540px] h-auto -ml-6 sm:-ml-2 translate-y-2" viewBox="0 0 540 240" fill="none">
                <!-- karakter 1: biru langit -->
                <g stroke="#38BDF8" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M24 238 C28 190, 56 172, 82 178 C108 184, 118 212, 114 238" fill="#FFFFFF" />
                    <circle cx="72" cy="144" r="24" fill="#FFFFFF" />
                    <circle cx="64" cy="141" r="2.2" fill="#38BDF8" />
                    <circle cx="80" cy="141" r="2.2" fill="#38BDF8" />
                    <path d="M64 152 Q72 159 80 152" />
                    <path d="M26 168 L14 142 M18 148 L8 148 M22 140 L18 130" />
                </g>
                <!-- karakter 2: kuning cerah bertelinga -->
                <g stroke="#F59E0B" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M110 238 C112 184, 146 160, 178 168 C206 176, 214 210, 210 238" fill="#FFFFFF" />
                    <path d="M144 126 C136 96, 152 94, 156 120" fill="#FFFFFF" />
                    <path d="M174 124 C182 94, 196 98, 186 124" fill="#FFFFFF" />
                    <circle cx="164" cy="144" r="26" fill="#FFFFFF" />
                    <circle cx="155" cy="141" r="2.4" fill="#F59E0B" />
                    <circle cx="173" cy="141" r="2.4" fill="#F59E0B" />
                    <path d="M155 152 Q164 161 173 152" />
                </g>
                <!-- karakter 3: merah coral tersenyum lebar -->
                <g stroke="#FB7185" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M210 238 C212 172, 248 148, 286 154 C324 160, 336 196, 332 238" fill="#FFFFFF" />
                    <circle cx="272" cy="122" r="30" fill="#FFFFFF" />
                    <path d="M250 100 L246 84 M262 94 L262 78 M276 94 L280 78 M290 100 L298 86" />
                    <circle cx="261" cy="118" r="2.6" fill="#FB7185" />
                    <circle cx="283" cy="118" r="2.6" fill="#FB7185" />
                    <path d="M258 131 Q272 144 286 131" />
                </g>
                <!-- karakter 4: hijau emerald melambai -->
                <g stroke="#10B981" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M326 238 C328 192, 356 174, 386 180 C414 186, 422 214, 420 238" fill="#FFFFFF" />
                    <circle cx="374" cy="148" r="24" fill="#FFFFFF" />
                    <circle cx="366" cy="145" r="2.2" fill="#10B981" />
                    <circle cx="382" cy="145" r="2.2" fill="#10B981" />
                    <path d="M366 156 Q374 162 382 156" />
                    <path d="M408 172 C424 156, 434 140, 428 124" />
                    <path d="M424 128 L434 120 M420 124 L424 114" />
                </g>
                <!-- aksen bintang & kilau kecil -->
                <path d="M126 112 L130 120 L138 124 L130 128 L126 136 L122 128 L114 124 L122 120 Z" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                <circle cx="328" cy="108" r="6" stroke="#38BDF8" stroke-width="3.5" fill="#FFFFFF" />
                <path d="M214 106 L220 96 M226 112 L236 106" stroke="#818CF8" stroke-width="3.5" stroke-linecap="round" />
            </svg>

            <!-- Doodle Kanan -->
            <svg class="hidden md:block w-[360px] lg:w-[520px] h-auto -mr-6 sm:-mr-2 translate-y-2" viewBox="0 0 540 240" fill="none">
                <!-- karakter 1 kanan: ungu lembut -->
                <g stroke="#818CF8" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M108 238 C110 192, 138 176, 168 182 C196 188, 204 214, 200 238" fill="#FFFFFF" />
                    <circle cx="156" cy="150" r="24" fill="#FFFFFF" />
                    <circle cx="148" cy="147" r="2.2" fill="#818CF8" />
                    <circle cx="164" cy="147" r="2.2" fill="#818CF8" />
                    <path d="M148 158 Q156 164 164 158" />
                </g>
                <!-- karakter 2 kanan: biru utama Digi-Safe berkacamata -->
                <g stroke="#1E88E5" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M204 238 C208 176, 246 150, 286 156 C322 162, 336 196, 332 238" fill="#FFFFFF" />
                    <circle cx="270" cy="124" r="29" fill="#FFFFFF" />
                    <circle cx="258" cy="122" r="8" fill="#EFF6FF" />
                    <circle cx="282" cy="122" r="8" fill="#EFF6FF" />
                    <line x1="266" y1="122" x2="274" y2="122" />
                    <path d="M259 138 Q270 146 281 138" />
                    <path d="M244 98 Q270 80 296 98" />
                </g>
                <!-- karakter 3 kanan: oranye ceria angkat tangan -->
                <g stroke="#FB923C" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M332 238 C334 186, 366 164, 398 170 C428 176, 438 206, 434 238" fill="#FFFFFF" />
                    <circle cx="384" cy="138" r="25" fill="#FFFFFF" />
                    <circle cx="376" cy="135" r="2.4" fill="#FB923C" />
                    <circle cx="392" cy="135" r="2.4" fill="#FB923C" />
                    <path d="M375 147 Q384 156 393 147" />
                    <path d="M342 172 C328 154, 322 138, 326 122" />
                </g>
                <!-- karakter 4 kanan: toska ceria -->
                <g stroke="#14B8A6" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M432 238 C436 190, 464 174, 492 180 C516 186, 524 212, 522 238" fill="#FFFFFF" />
                    <circle cx="478" cy="148" r="23" fill="#FFFFFF" />
                    <circle cx="471" cy="145" r="2.2" fill="#14B8A6" />
                    <circle cx="485" cy="145" r="2.2" fill="#14B8A6" />
                    <path d="M471 155 Q478 161 485 155" />
                </g>
                <!-- aksen kilau & perisai kecil -->
                <path d="M424 102 L428 110 L436 114 L428 118 L424 126 L420 118 L412 114 L420 110 Z" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                <path d="M210 114 L224 108 L238 114 L238 126 C238 135 224 142 224 142 C224 142 210 135 210 126 Z" fill="#FFFFFF" stroke="#1E88E5" stroke-width="3.5" stroke-linejoin="round" />
            </svg>
        </div>

        <!-- Bar atas TANPA NAVBAR: hanya menampilkan Logo Digi-Safe di tengah (persis referensi gambar 2) -->
        <header class="relative z-10 flex items-center justify-center">
            <div class="inline-flex items-center gap-2.5 rounded-2xl bg-white/95 border border-slate-200/80 px-3.5 py-2 shadow-xs">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta" class="w-7 h-7 object-contain select-none">
                <div class="text-left leading-none">
                    <span class="font-heading font-extrabold text-xs text-[#0A0A0A] block">Digi-Safe</span>
                    <span class="text-[9px] font-semibold uppercase tracking-[0.14em] text-sky block mt-0.5">Purwakarta</span>
                </div>
            </div>
        </header>

        <!-- TAHAP 1: Form Kustomisasi Profil (mirip kartu bertumpuk di tengah pada gambar referensi 2) -->
        <main x-show="!loadingStage" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative z-10 w-full max-w-2xl mx-auto my-auto py-6 sm:py-8">
            <div class="text-center max-w-xl mx-auto">
                <p class="onboard-script text-xl sm:text-2xl text-slate-500">
                    {{ $isEn ? 'Welcome!' : 'Selamat Datang!' }}
                </p>

                <h1 class="mt-1.5 font-heading font-extrabold text-3xl sm:text-[42px] text-[#0A0A0A] leading-[1.12] tracking-[-0.03em]">
                    {{ $isEn ? 'Google Linked.' : 'Akun Google Terhubung.' }}<br>
                    {{ $isEn ? 'Make Your Profile Yours.' : 'Atur Identitas Profilmu.' }}
                </h1>

                <p class="mt-3 text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    {{ $isEn
                        ? 'Choose your required nickname and username. Profile photo and banner are optional and can always be changed later in Profile.'
                        : 'Tentukan nickname dan username wajibmu dulu. Foto profil dan banner bersifat opsional serta bisa diubah kapan saja di halaman Profil.' }}
                </p>
            </div>

            <!-- Kartu Bertumpuk di Tengah (Persis Referensi Gambar 2) -->
            <div class="relative mt-7 sm:mt-8 max-w-[460px] mx-auto">
                <!-- lapisan kartu bayangan miring halus di belakang -->
                <div class="pointer-events-none absolute inset-0 rounded-[24px] bg-white border border-slate-200/80 shadow-sm -rotate-2 translate-y-1.5" aria-hidden="true"></div>

                <!-- kartu form utama -->
                <form
                    x-ref="customizeForm"
                    method="POST"
                    action="{{ route('auth.google.customize.store') }}"
                    enctype="multipart/form-data"
                    @submit.prevent="submitCustomize()"
                    class="relative rounded-[24px] bg-white border border-slate-200/90 shadow-[0_22px_55px_rgba(15,23,42,0.09)] overflow-hidden"
                >
                    @csrf

                    <!-- error alert jika validasi gagal -->
                    <div x-show="errorMsg !== ''" style="display: none;" role="alert" class="mx-5 mt-5 rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-xs font-semibold text-rose-700">
                        <span x-text="errorMsg"></span>
                    </div>

                    <div class="divide-y divide-slate-100 px-5 sm:px-6 pt-3 pb-2">
                        <!-- Baris 1: Nickname (Wajib) -->
                        <div class="py-4 flex items-start gap-3.5">
                            <span class="mt-1 w-9 h-9 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="nama_lengkap" class="font-heading font-bold text-sm text-[#0A0A0A]">
                                        {{ $isEn ? 'Nickname / Display Name' : 'Nickname / Nama Tampilan' }}
                                    </label>
                                    <span class="rounded-full bg-[#0A0A0A] text-lime px-2 py-0.5 text-[9.5px] font-extrabold uppercase tracking-wide">
                                        {{ $isEn ? 'Required' : 'Wajib' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $isEn ? 'Shown on your profile, leaderboard, and community forum' : 'Ditampilkan di profil, leaderboard, dan forum komunitas' }}
                                </p>
                                <input
                                    id="nama_lengkap"
                                    name="nama_lengkap"
                                    type="text"
                                    x-model="nama_lengkap"
                                    required
                                    maxlength="100"
                                    placeholder="{{ $isEn ? 'Your preferred nickname' : 'Nama panggilan atau nama lengkapmu' }}"
                                    class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-[#0A0A0A] placeholder:text-slate-400 focus:bg-white focus:border-sky focus:ring-2 focus:ring-sky/20 focus:outline-none transition-all"
                                >
                            </div>
                        </div>

                        <!-- Baris 2: Username (Wajib) -->
                        <div class="py-4 flex items-start gap-3.5">
                            <span class="mt-1 w-9 h-9 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 flex items-center justify-center shrink-0 font-mono font-bold text-sm">
                                @
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="username" class="font-heading font-bold text-sm text-[#0A0A0A]">
                                        {{ $isEn ? 'Unique Username' : 'Username Unik' }}
                                    </label>
                                    <span class="rounded-full bg-[#0A0A0A] text-lime px-2 py-0.5 text-[9.5px] font-extrabold uppercase tracking-wide">
                                        {{ $isEn ? 'Required' : 'Wajib' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $isEn ? 'Letters, numbers, dashes (-), or underscores (_)' : 'Huruf, angka, strip (-), atau garis bawah (_)' }}
                                </p>
                                <div class="relative mt-2">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold text-xs pointer-events-none">@</span>
                                    <input
                                        id="username"
                                        name="username"
                                        type="text"
                                        x-model="username"
                                        required
                                        minlength="3"
                                        maxlength="50"
                                        placeholder="username_kamu"
                                        class="w-full rounded-xl bg-slate-50 border border-slate-200 pl-8 pr-3.5 py-2.5 text-xs font-medium text-[#0A0A0A] placeholder:text-slate-400 focus:bg-white focus:border-sky focus:ring-2 focus:ring-sky/20 focus:outline-none transition-all"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Foto Profil (Opsional) -->
                        <div class="py-4 flex items-start gap-3.5">
                            <div class="mt-0.5 w-10 h-10 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" alt="Preview Avatar" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!avatarPreview">
                                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="4" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="M21 15l-5-5L5 21" />
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="avatar_input" class="font-heading font-bold text-sm text-[#0A0A0A]">
                                        {{ $isEn ? 'Profile Photo' : 'Foto Profil' }}
                                    </label>
                                    <span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wide">
                                        {{ $isEn ? 'Optional' : 'Opsional' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $isEn ? 'Skip to keep your Google photo, or change later in Profile' : 'Bisa dilewati atau diubah nanti di halaman Profil' }}
                                </p>
                                <div class="mt-2 flex items-center gap-2.5">
                                    <label for="avatar_input" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-[#0A0A0A] text-[11px] font-heading font-bold px-3.5 py-1.5 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        <span>{{ $isEn ? 'Upload Photo' : 'Pilih Foto' }}</span>
                                    </label>
                                    <input id="avatar_input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" @change="onAvatarChange($event)" class="sr-only">
                                    <span class="text-[11px] text-slate-400 truncate max-w-[170px]" x-text="avatarName || @js($isEn ? 'Using default photo' : 'Pakai foto bawaan')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 4: Banner Profil (Opsional) -->
                        <div class="py-4 flex items-start gap-3.5">
                            <div class="mt-0.5 w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                <template x-if="bannerPreview">
                                    <img :src="bannerPreview" alt="Preview Banner" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!bannerPreview">
                                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="3" />
                                        <path d="M2 15l5-5 4 4 4-4 7 6" />
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="banner_input" class="font-heading font-bold text-sm text-[#0A0A0A]">
                                        {{ $isEn ? 'Profile Banner' : 'Banner Profil' }}
                                    </label>
                                    <span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wide">
                                        {{ $isEn ? 'Optional' : 'Opsional' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $isEn ? 'Optional header image (JPG/PNG/GIF), editable anytime in Profile' : 'Gambar sampul profil (JPG/PNG/GIF), bisa diubah nanti di Profil' }}
                                </p>
                                <div class="mt-2 flex items-center gap-2.5">
                                    <label for="banner_input" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-[#0A0A0A] text-[11px] font-heading font-bold px-3.5 py-1.5 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        <span>{{ $isEn ? 'Upload Banner' : 'Pilih Banner' }}</span>
                                    </label>
                                    <input id="banner_input" type="file" name="banner" accept="image/jpeg,image/png,image/webp,image/gif" @change="onBannerChange($event)" class="sr-only">
                                    <span class="text-[11px] text-slate-400 truncate max-w-[170px]" x-text="bannerName || @js($isEn ? 'Using default sky banner' : 'Pakai banner bawaan')"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan di bawah kartu (kapsul hitam elegan persis tombol referensi gambar 2) -->
                    <div class="px-5 sm:px-6 pb-5 pt-2">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="w-full rounded-full bg-[#0A0A0A] hover:bg-sky text-white font-heading font-extrabold text-xs sm:text-sm py-3.5 px-6 shadow-md hover:-translate-y-0.5 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
                        >
                            <span x-text="submitting ? @js($isEn ? 'Saving profile...' : 'Menyimpan profil...') : @js($isEn ? 'Save & Continue to Digi-Safe' : 'Simpan & Masuk ke Digi-Safe')"></span>
                            <svg x-show="!submitting" class="w-4 h-4 text-lime" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- TAHAP 2: Layar Loading Serupa Desain Gambar 2 + Logo Digi-Safe + Maskot Gambar 3 Melihat ke Arah Bar Loading -->
        <section
            x-show="loadingStage"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            style="display: none;"
            aria-live="polite"
            class="relative z-10 w-full max-w-lg mx-auto my-auto py-8 text-center"
        >
            <p class="onboard-script text-xl sm:text-2xl text-slate-500">
                {{ $isEn ? 'Setting things up!' : 'Sedang Menyiapkan!' }}
            </p>
            <h2 class="mt-1 font-heading font-extrabold text-2xl sm:text-4xl text-[#0A0A0A] tracking-[-0.03em]">
                {{ $isEn ? 'Welcome Aboard,' : 'Selamat Bergabung,' }}
                <span class="text-sky" x-text="nama_lengkap || username"></span>
            </h2>

            <!-- Kartu loading bertumpuk dengan Maskot Gambar 3 tepat di atas bar loading seakan menatap bar loading -->
            <div class="relative mt-8 max-w-[420px] mx-auto">
                <div class="pointer-events-none absolute inset-0 rounded-[26px] bg-white border border-slate-200/80 shadow-sm rotate-2 translate-y-1.5" aria-hidden="true"></div>

                <div class="relative rounded-[26px] bg-white border border-slate-200/90 shadow-[0_24px_60px_rgba(15,23,42,0.09)] px-6 sm:px-8 pt-6 pb-7 flex flex-col items-center">
                    <!-- Maskot Robot Gambar 3 tanpa background & tanpa animasi floating, tepat di atas bar loading -->
                    <div class="relative w-44 sm:w-52 -mb-2 flex items-center justify-center">
                        <img
                            src="{{ asset('images/mascot-loading.png') }}"
                            alt="Maskot Digi-Safe memantau proses loading"
                            width="220"
                            height="220"
                            class="w-full h-auto object-contain select-none pointer-events-none"
                        >
                    </div>

                    <!-- Bar Loading Tepat di Bawah Maskot -->
                    <div class="w-full mt-2">
                        <div class="flex items-center justify-between text-xs font-heading font-bold mb-2">
                            <span class="text-slate-600" x-text="statuses[statusIndex]"></span>
                            <span class="text-[#0A0A0A] tabular-nums" x-text="progress + '%'"></span>
                        </div>

                        <div class="w-full h-3.5 rounded-full bg-slate-100 border border-slate-200/90 p-0.5 overflow-hidden">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-[#1E88E5] via-[#38BDF8] to-[#C6F432] transition-[width] duration-150 ease-out"
                                :style="'width: ' + progress + '%'"
                            ></div>
                        </div>

                        <!-- Mini badge logo Digi-Safe di bawah bar loading -->
                        <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] font-semibold text-slate-500">
                            <img src="{{ asset('images/logo.png') }}" alt="" aria-hidden="true" class="w-4 h-4 object-contain">
                            <span>Digi-Safe Purwakarta &bull; <span class="text-sky font-bold" x-text="'@' + username"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Spacer bawah ringan agar kartu tetap seimbang di atas ilustrasi doodle -->
        <div class="relative z-10 h-6" aria-hidden="true"></div>
    </div>

</body>

</html>
