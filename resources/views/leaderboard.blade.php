@extends('layouts.app')

@section('title', 'Leaderboard | Digi-Safe Purwakarta')

@section('content')

    @php
        $isEn = app()->getLocale() === 'en';
        $isDark = ($currentTheme ?? 'light') === 'dark';
        $surfaceCard = $isDark
            ? 'bg-[#0F172A] border-slate-800 text-slate-100'
            : 'bg-white border-gray-200/80 text-ink';
        $mutedText = $isDark ? 'text-slate-400' : 'text-muted';
        $subtleBg = $isDark ? 'bg-slate-800/70 border-slate-700/80' : 'bg-[#F8FAFC] border-gray-200/75';
    @endphp

    <div class="min-h-screen {{ $isDark ? 'bg-[#0B1120] text-slate-100' : 'bg-[#F4F6F9] text-ink' }} pt-24 sm:pt-28 pb-24 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 sm:space-y-14">

            <!-- 1. HERO KOMUNITAS & METRIK NYATA -->
            <header class="rounded-3xl border {{ $isDark ? 'bg-[#0F172A] border-slate-800' : 'bg-white border-gray-200/80' }} p-6 sm:p-8 lg:p-10 shadow-xs">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <div class="inline-flex items-center gap-2 text-xs font-heading font-bold {{ $isDark ? 'text-lime' : 'text-sky' }}">
                            <span class="w-2 h-2 rounded-full {{ $isDark ? 'bg-lime' : 'bg-sky' }}"></span>
                            <span>{{ $isEn ? 'Purwakarta Cyber Literacy Hall of Fame' : 'Papan Kehormatan Literasi Siber Purwakarta' }}</span>
                        </div>

                        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl tracking-tight leading-tight {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'Community Leaderboard & Arena Records' : 'Peringkat Komunitas & Rekor Arena' }}
                        </h1>

                        <p class="text-sm leading-relaxed {{ $mutedText }} max-w-xl">
                            {{ $isEn
                                ? 'Live standings across minigame accuracy, forum contributions, and security badges earned by verified Digi-Safe Purwakarta members.'
                                : 'Klasemen langsung berdasarkan akurasi minigame, kontribusi diskusi forum, dan pencapaian keamanan digital warga Digi-Safe Purwakarta.' }}
                        </p>

                        <div class="pt-1 flex flex-wrap items-center gap-3">
                            <a href="{{ route('minigame.index') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-5 py-3 transition-colors">
                                {{ $isEn ? 'Play Minigame' : 'Mainkan Minigame' }}
                            </a>
                            <a href="{{ route('users.directory') }}"
                                class="inline-flex items-center justify-center rounded-xl border {{ $isDark ? 'border-slate-700 text-slate-200 hover:border-lime hover:text-lime' : 'border-gray-300 text-ink hover:border-ink' }} font-heading font-bold text-xs px-5 py-3 transition-colors">
                                {{ $isEn ? 'Browse Community' : 'Lihat Daftar Komunitas' }}
                            </a>
                        </div>
                    </div>

                    <!-- 4 Angka Nyata dari Database -->
                    <div class="lg:col-span-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border {{ $subtleBg }} p-4">
                            <p class="text-[11px] font-semibold {{ $mutedText }}">{{ $isEn ? 'Registered Citizens' : 'Warga Terdaftar' }}</p>
                            <p class="mt-1 font-heading font-extrabold text-2xl sm:text-3xl {{ $isDark ? 'text-white' : 'text-ink' }}">{{ number_format($communityStats['total_users']) }}</p>
                        </div>
                        <div class="rounded-2xl border {{ $subtleBg }} p-4">
                            <p class="text-[11px] font-semibold {{ $mutedText }}">{{ $isEn ? 'Minigame Sessions' : 'Sesi Minigame' }}</p>
                            <p class="mt-1 font-heading font-extrabold text-2xl sm:text-3xl text-sky">{{ number_format($communityStats['total_sessions']) }}</p>
                        </div>
                        <div class="rounded-2xl border {{ $subtleBg }} p-4">
                            <p class="text-[11px] font-semibold {{ $mutedText }}">{{ $isEn ? 'Forum Posts' : 'Diskusi & Balasan' }}</p>
                            <p class="mt-1 font-heading font-extrabold text-2xl sm:text-3xl {{ $isDark ? 'text-white' : 'text-ink' }}">{{ number_format($communityStats['total_discussions']) }}</p>
                        </div>
                        <div class="rounded-2xl border {{ $isDark ? 'bg-lime/10 border-lime/30' : 'bg-[#F4FDE0] border-lime/70' }} p-4">
                            <p class="text-[11px] font-semibold {{ $isDark ? 'text-lime' : 'text-ink/75' }}">{{ $isEn ? 'All-Time High Score' : 'Skor Tertinggi' }}</p>
                            <p class="mt-1 font-heading font-extrabold text-2xl sm:text-3xl {{ $isDark ? 'text-lime' : 'text-ink' }}">{{ number_format($communityStats['highest_score']) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Strip Posisi Personal (Khusus User Login) -->
                @if ($mySummary)
                    <div class="mt-6 pt-6 border-t {{ $isDark ? 'border-slate-800' : 'border-gray-100' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <x-user-avatar :user="$mySummary['user']" size="lg" />
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold {{ $mutedText }}">
                                    {{ $isEn ? 'Your current standing' : 'Posisi kamu saat ini' }}
                                </p>
                                <p class="font-heading font-extrabold text-sm sm:text-base truncate {{ $isDark ? 'text-white' : 'text-ink' }}">
                                    {{ $mySummary['user']->nama_lengkap ?: $mySummary['user']->username }}
                                    <span class="font-semibold text-xs {{ $mutedText }}">
                                        &middot;
                                        @if ($mySummary['rank'])
                                            {{ $isEn ? 'Rank #' . $mySummary['rank'] : 'Peringkat #' . $mySummary['rank'] }}
                                            @if (! empty($mySummary['rank_category']))
                                                ({{ $mySummary['rank_category'] }})
                                            @endif
                                        @else
                                            {{ $isEn ? 'Unranked yet' : 'Belum masuk klasemen' }}
                                        @endif
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs">
                            <div>
                                <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Best Score' : 'Skor Terbaik' }}</span>
                                <span class="font-heading font-extrabold text-sm text-sky">{{ $mySummary['top_score'] }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Games Played' : 'Total Main' }}</span>
                                <span class="font-heading font-extrabold text-sm">{{ $mySummary['plays'] }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Avatar Borders' : 'Border Terbuka' }}</span>
                                <span class="font-heading font-extrabold text-sm">{{ $mySummary['unlocked_borders'] }} / {{ $mySummary['total_borders'] }}</span>
                            </div>
                            <a href="{{ route('profile.edit') }}#avatar-borders"
                                class="inline-flex items-center justify-center rounded-xl bg-lime text-ink font-heading font-bold text-xs px-4 py-2 hover:brightness-95 transition-all">
                                {{ $isEn ? 'Manage Borders' : 'Atur Border' }}
                            </a>
                        </div>
                    </div>
                @endif
            </header>

            <!-- 2. PAPAN KLASEMEN UTAMA -->
            <section aria-label="{{ $isEn ? 'Main Leaderboard' : 'Papan Peringkat Utama' }}">
                <x-leaderboard-board :datasets="$leaderboardData" />
            </section>

            <!-- 3. REKOR PER ARENA MINIGAME -->
            <section aria-labelledby="arena-records-heading" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                    <div>
                        <h2 id="arena-records-heading" class="font-heading font-extrabold text-xl sm:text-2xl {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'Records by Minigame Arena' : 'Rekor per Arena Minigame' }}
                        </h2>
                        <p class="text-xs sm:text-sm {{ $mutedText }} mt-0.5">
                            {{ $isEn ? 'Performance breakdown and current record holder in each training simulation.' : 'Ringkasan permainan nyata dan pemegang skor puncak di tiap arena latihan.' }}
                        </p>
                    </div>
                    <a href="{{ route('minigame.index') }}" class="text-xs font-heading font-bold text-sky hover:underline">
                        {{ $isEn ? 'Open Minigame Hub' : 'Buka Hub Minigame' }}
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($arenaStats as $arena)
                        <article class="rounded-2xl border {{ $surfaceCard }} p-5 flex flex-col justify-between gap-5">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-bold text-sky">{{ $arena['tag'] }}</span>
                                    <span class="text-[11px] font-semibold {{ $mutedText }}">{{ $arena['total_plays'] }} {{ $isEn ? 'runs' : 'sesi' }}</span>
                                </div>
                                <h3 class="mt-1 font-heading font-extrabold text-lg {{ $isDark ? 'text-white' : 'text-ink' }}">{{ $arena['title'] }}</h3>

                                <div class="mt-4 grid grid-cols-2 gap-2.5">
                                    <div class="rounded-xl border {{ $subtleBg }} px-3 py-2.5">
                                        <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Highest' : 'Rekor Skor' }}</span>
                                        <span class="font-heading font-extrabold text-lg text-sky">{{ $arena['highest_score'] }}</span>
                                    </div>
                                    <div class="rounded-xl border {{ $subtleBg }} px-3 py-2.5">
                                        <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Average' : 'Rata-rata' }}</span>
                                        <span class="font-heading font-extrabold text-lg">{{ $arena['avg_score'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t {{ $isDark ? 'border-slate-800' : 'border-gray-100' }} flex items-center justify-between gap-3">
                                @if ($arena['champion'])
                                    <a href="{{ route('profile.show', $arena['champion']->id) }}" class="flex items-center gap-2.5 min-w-0 group">
                                        <x-user-avatar :user="$arena['champion']" size="sm" />
                                        <div class="min-w-0">
                                            <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'Top Holder' : 'Pemegang Rekor' }}</span>
                                            <span class="block font-heading font-bold text-xs truncate group-hover:text-sky transition-colors">
                                                {{ $arena['champion']->nama_lengkap ?: $arena['champion']->username }}
                                            </span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs {{ $mutedText }}">{{ $isEn ? 'No record yet' : 'Belum ada rekor' }}</span>
                                @endif

                                <a href="{{ $arena['play_url'] }}"
                                    class="shrink-0 rounded-xl bg-ink hover:bg-sky text-white font-heading font-bold text-[11px] px-3.5 py-2 transition-colors">
                                    {{ $isEn ? 'Challenge' : 'Tantang' }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <!-- 4. JALUR BORDER AVATAR & AKTIVITAS TERBARU (2 KOLOM ASIMETRIS) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Kiri (7 kolom): Koleksi 5 Border Avatar yang Bisa Di-Unlock -->
                <section aria-labelledby="border-showcase-heading" class="lg:col-span-7 rounded-3xl border {{ $surfaceCard }} p-6 sm:p-7 space-y-5">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 id="border-showcase-heading" class="font-heading font-extrabold text-lg sm:text-xl {{ $isDark ? 'text-white' : 'text-ink' }}">
                                {{ $isEn ? 'Unlockable Avatar Borders' : 'Koleksi 5 Border Avatar Prestasi' }}
                            </h2>
                            <p class="text-xs {{ $mutedText }} mt-0.5">
                                {{ $isEn
                                    ? 'New accounts start clean without a border. Reach milestones to unlock and equip them on your profile.'
                                    : 'Akun baru tampil bersih tanpa border. Selesaikan pencapaian berikut untuk membuka dan memasangnya di profilmu.' }}
                            </p>
                        </div>
                        @auth
                            <a href="{{ route('profile.edit') }}#avatar-borders"
                                class="rounded-xl bg-lime text-ink font-heading font-bold text-xs px-4 py-2 hover:brightness-95 transition-all">
                                {{ $isEn ? 'Choose on Profile' : 'Pilih di Profil' }}
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="rounded-xl bg-ink text-white font-heading font-bold text-xs px-4 py-2 hover:bg-sky transition-colors">
                                {{ $isEn ? 'Sign In to Collect' : 'Masuk untuk Mengoleksi' }}
                            </a>
                        @endauth
                    </div>

                    <div class="divide-y {{ $isDark ? 'divide-slate-800' : 'divide-gray-100' }}">
                        @foreach ($borderCatalog as $b)
                            <div class="py-3.5 first:pt-1 last:pb-0 flex items-center gap-4">
                                <!-- Preview border pada lingkaran sample -->
                                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                                    <span class="w-11 h-11 rounded-full {{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-[#EFF6FF] text-sky' }} font-heading font-extrabold text-xs flex items-center justify-center">
                                        T{{ $b['tier'] }}
                                    </span>
                                    <img
                                        src="{{ $b['image_url'] }}"
                                        alt="{{ $b['name'] }}"
                                        width="64"
                                        height="64"
                                        loading="lazy"
                                        class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10"
                                    >
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-heading font-bold text-sm {{ $isDark ? 'text-white' : 'text-ink' }}">{{ $b['name'] }}</span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md {{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-gray-100 text-muted' }}">
                                            Tier {{ $b['tier'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs {{ $mutedText }} mt-0.5 leading-snug">{{ $b['requirement'] }}</p>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="block font-heading font-bold text-xs {{ $isDark ? 'text-slate-200' : 'text-ink' }}">{{ $b['equipped_count'] }}</span>
                                    <span class="block text-[10px] {{ $mutedText }}">{{ $isEn ? 'using' : 'pemakai' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Kanan (5 kolom): Aktivitas Skor Terbaru -->
                <section aria-labelledby="recent-runs-heading" class="lg:col-span-5 rounded-3xl border {{ $surfaceCard }} p-6 sm:p-7 space-y-4">
                    <div>
                        <h2 id="recent-runs-heading" class="font-heading font-extrabold text-lg sm:text-xl {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'Latest Arena Runs' : 'Aktivitas Skor Terbaru' }}
                        </h2>
                        <p class="text-xs {{ $mutedText }} mt-0.5">
                            {{ $isEn ? 'Most recent verified minigame finishes.' : 'Sesi permainan terbaru yang tercatat di sistem.' }}
                        </p>
                    </div>

                    @if ($recentRuns->isEmpty())
                        <div class="rounded-2xl border border-dashed {{ $isDark ? 'border-slate-700' : 'border-gray-200' }} p-6 text-center space-y-2">
                            <p class="text-xs font-semibold {{ $mutedText }}">
                                {{ $isEn ? 'No minigame scores recorded yet. Be the first on the board!' : 'Belum ada skor minigame tercatat. Jadilah pemain pertama di papan peringkat!' }}
                            </p>
                            <a href="{{ route('minigame.index') }}" class="inline-block text-xs font-heading font-bold text-sky hover:underline">
                                {{ $isEn ? 'Start playing' : 'Mulai bermain sekarang' }}
                            </a>
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($recentRuns as $run)
                                <a href="{{ route('profile.show', $run->user->id) }}"
                                    class="flex items-center justify-between gap-3 rounded-2xl border {{ $subtleBg }} px-3.5 py-3 hover:border-sky/50 transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-user-avatar :user="$run->user" size="sm" />
                                        <div class="min-w-0">
                                            <p class="font-heading font-bold text-xs truncate {{ $isDark ? 'text-white' : 'text-ink' }}">
                                                {{ $run->user->nama_lengkap ?: $run->user->username }}
                                            </p>
                                            <p class="text-[11px] {{ $mutedText }} truncate">
                                                {{ $run->nama_game }} &middot; {{ $run->played_at ? $run->played_at->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="inline-block font-heading font-extrabold text-sm text-sky">{{ $run->skor }}</span>
                                        <span class="block text-[10px] {{ $mutedText }}">pts</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

        </div>
    </div>

@endsection
