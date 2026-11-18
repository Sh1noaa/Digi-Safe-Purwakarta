@props([
    'user',
    'linkToDirectory' => false,
    'class' => '',
])

@php
    $displayName = $user->nama_lengkap ?: $user->username;
    $privacy = $user->privacy_config;
    $isPrivate = $privacy['is_private_profile'];
    $hideLocation = $privacy['hide_location'];

    if ($isPrivate) {
        $bioText = __('ui.directory.private_card_bio');
    } elseif ($hideLocation) {
        $bioText = __('ui.directory.hidden_bio');
    } else {
        $bioText = $user->bio ?: ($user->display_role . ' ' . __('ui.profile.default_card_bio_suffix'));
    }

    $totalScore = (int) ($user->total_skor_game ?? ($user->skorGames ? $user->skorGames->sum('skor') : 0));
    $forumCount = (int) ($user->forums_count ?? ($user->forums ? $user->forums->count() : 0));
    $jawabanCount = (int) ($user->jawaban_forums_count ?? ($user->jawabanForums ? $user->jawabanForums->count() : 0));
    $testiCount = (int) ($user->testimonis_count ?? ($user->testimonis ? $user->testimonis->count() : 0));

    $seed = abs(crc32((string) ($user->username . '_' . $user->id)));

    $rawLikes = max(120, ($totalScore * 14) + (($seed % 450) + 80));
    $likesFormatted = $rawLikes >= 1000 ? number_format($rawLikes / 1000, 1) . 'K' : (string) $rawLikes;

    $postsCount = max(1, $forumCount + $jawabanCount + $testiCount + ($seed % 18));

    $rawViews = max(450, ($postsCount * 85) + ($totalScore * 22) + ($seed % 1900));
    $viewsFormatted = $rawViews >= 1000 ? number_format($rawViews / 1000, 1) . 'K' : (string) $rawViews;

    $expColors = [
        '#6366F1', '#7C3AED', '#8B5CF6', '#A855F7',
        '#D946EF', '#EC4899', '#F43F5E', '#EF4444',
        '#F97316', '#F59E0B', '#EAB308', '#84CC16',
        '#22C55E', '#10B981', '#14B8A6', '#06B6D4',
        '#0EA5E9', '#3B82F6', '#6366F1', '#8B5CF6',
    ];
    $activeTicks = min(18, max(11, 10 + (int) floor(($totalScore + ($postsCount * 10)) / 35) + ($seed % 5)));

    $targetUrl = $linkToDirectory
        ? route('users.directory')
        : route('profile.show', $user->id);
@endphp

<div
    x-data="{ followed: false }"
    {{ $attributes->merge(['class' => "group relative rounded-[32px] bg-white p-2.5 border border-gray-100 shadow-[0_18px_45px_rgba(15,23,42,0.07)] hover:shadow-[0_24px_55px_rgba(30,136,229,0.14)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between overflow-hidden select-none {$class}"]) }}
>
    <div>
        <!-- Banner Awan Atas (atau custom banner user jika ada) -->
        <div
            class="relative h-36 sm:h-40 w-full rounded-[24px] overflow-hidden"
            style="background-image: url('{{ $user->banner_url ?: asset('images/bluesky.jpeg') }}'); background-size: cover; background-position: center;"
        >
            @if (! $user->banner_url)
                <div
                    class="absolute inset-0 pointer-events-none"
                    style="background: linear-gradient(180deg, rgba(186, 230, 253, 0.35) 0%, rgba(255, 255, 255, 0.25) 100%);"
                ></div>
            @endif

            <button
                type="button"
                @click.stop="followed = !followed"
                :class="followed ? 'bg-ink text-lime' : 'bg-white/95 text-[#0A0A0A] hover:bg-white'"
                class="absolute top-3.5 right-3.5 rounded-full px-4 py-1.5 text-xs font-heading font-bold shadow-sm backdrop-blur-md inline-flex items-center gap-1.5 transition-all cursor-pointer z-20"
            >
                <span x-text="followed ? @js(str_replace(' ✓', '', __('ui.profile.following'))) : @js(__('ui.profile.follow'))"></span>
                <span class="text-sm leading-none font-normal" x-text="followed ? '✓' : '+'"></span>
            </button>
        </div>

        <!-- Baris Avatar Bulat Overlap & Indikator exp. -->
        <div class="px-3.5 flex items-end justify-between gap-2">
            <a
                href="{{ $targetUrl }}"
                class="-mt-11 relative z-10 inline-block rounded-full p-1 bg-white shadow-md group-hover:scale-105 transition-transform shrink-0"
            >
                <x-user-avatar :user="$user" size="xl" class="!w-18 !h-18 sm:!w-20 sm:!h-20" />
            </a>

            @if (! $isPrivate)
                <div class="pb-2 flex items-center gap-2 min-w-0">
                    <span class="text-[11px] font-medium text-gray-400 shrink-0">exp.</span>
                    <div class="flex items-center gap-[2.5px]" aria-label="Experience Bar">
                        @for ($i = 0; $i < 20; $i++)
                            <span
                                class="w-[2.5px] h-3 rounded-full transition-opacity"
                                style="background-color: {{ $i < $activeTicks ? $expColors[$i] : '#E2E8F0' }};"
                            ></span>
                        @endfor
                    </div>
                </div>
            @else
                <span class="pb-2 text-[10px] font-bold text-amber-600">🔒 {{ __('ui.profile.private_account_badge') }}</span>
            @endif
        </div>

        <!-- Nama Lengkap & Bio Singkat -->
        <a href="{{ $targetUrl }}" class="block px-4 pt-4 pb-5 text-left">
            <div class="flex items-center gap-2">
                <h3 class="font-heading font-bold text-lg text-[#0A0A0A] group-hover:text-sky transition-colors truncate">
                    {{ $displayName }}
                </h3>
                @if (! $isPrivate)
                    @if ($user->isOwner())
                        <span class="shrink-0 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold uppercase">Owner</span>
                    @elseif ($user->isAdmin())
                        <span class="shrink-0 rounded-full bg-lime text-ink px-2 py-0.5 text-[9px] font-extrabold uppercase">Admin</span>
                    @endif
                @endif
            </div>

            @if (! $isPrivate && filled($user->specialty_role))
                <span class="mt-1 inline-block rounded-full bg-[#EFF6FF] border border-sky/20 px-2.5 py-0.5 text-[10px] font-heading font-bold text-sky">
                    {{ $user->specialty_role }}
                </span>
            @endif

            <p class="mt-1.5 text-xs text-gray-500 leading-relaxed line-clamp-2 min-h-[36px]">
                {{ $bioText }}
            </p>
        </a>
    </div>

    @if (! $isPrivate)
        <!-- Bagian Bawah: 3 Kolom Statistik (Likes, Posts, Views) + 3 Ikon Sosial -->
        <div>
            <a
                href="{{ $targetUrl }}"
                class="grid grid-cols-3 divide-x divide-gray-100 border-t border-b border-gray-100 py-3.5 text-center hover:bg-gray-50/50 transition-colors"
            >
                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight">
                        {{ $likesFormatted }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ __('ui.profile.likes') }}</span>
                </div>

                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight">
                        {{ $postsCount }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ __('ui.profile.posts') }}</span>
                </div>

                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight">
                        {{ $viewsFormatted }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ __('ui.profile.views') }}</span>
                </div>
            </a>

            <div class="grid grid-cols-3 divide-x divide-gray-100 py-3 text-center bg-[#FAFAFA]/70 rounded-b-[22px]">
                <a href="{{ $targetUrl }}" aria-label="Instagram {{ $displayName }}" class="flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                    </svg>
                </a>

                <a href="{{ $targetUrl }}" aria-label="X {{ $displayName }}" class="flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                        <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                    </svg>
                </a>

                <a href="{{ $targetUrl }}" aria-label="Profil &#64;{{ $user->username }}" class="flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94" />
                    </svg>
                </a>
            </div>
        </div>
    @else
        <!-- Footer ringkas mode privat: hanya menampilkan badge privasi -->
        <div class="border-t border-gray-100 py-3.5 px-4 text-center bg-[#FAFAFA]/70 rounded-b-[22px] text-[11px] font-semibold text-muted">
            🔒 {{ __('ui.profile.private_account_badge') }}
        </div>
    @endif
</div>
