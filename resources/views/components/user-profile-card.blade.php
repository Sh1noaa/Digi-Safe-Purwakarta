@props([
    'user',
    'linkToDirectory' => false,
    'followingIds' => [],
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

    $followersCount = (int) ($user->followers_count ?? 0);
    $followingCount = (int) ($user->following_count ?? 0);
    $contributionsCount = $forumCount + $jawabanCount + $testiCount;

    $isMe = Auth::check() && (int) Auth::id() === (int) $user->id;
    $initiallyFollowing = Auth::check() && ! $isMe && in_array((int) $user->id, $followingIds ?? [], true);

    $expColors = [
        '#6366F1', '#7C3AED', '#8B5CF6', '#A855F7',
        '#D946EF', '#EC4899', '#F43F5E', '#EF4444',
        '#F97316', '#F59E0B', '#EAB308', '#84CC16',
        '#22C55E', '#10B981', '#14B8A6', '#06B6D4',
        '#0EA5E9', '#3B82F6', '#6366F1', '#8B5CF6',
    ];
    $activeTicks = min(20, max(4, 4 + (int) floor(($totalScore + ($contributionsCount * 25) + ($followersCount * 15)) / 40)));

    $targetUrl = $linkToDirectory
        ? route('profile.show', $user->id)
        : route('profile.show', $user->id);

    $isEnCard = app()->getLocale() === 'en';
@endphp

<div
    x-data="{
        userId: {{ (int) $user->id }},
        isAuth: {{ Auth::check() ? 'true' : 'false' }},
        isMe: {{ $isMe ? 'true' : 'false' }},
        followed: {{ $initiallyFollowing ? 'true' : 'false' }},
        followersCount: {{ $followersCount }},
        busy: false,
        init() {
            window.addEventListener('digisafe-follow-changed', (e) => {
                if (e.detail && Number(e.detail.userId) === this.userId) {
                    this.followed = Boolean(e.detail.following);
                    if (typeof e.detail.followersCount === 'number') {
                        this.followersCount = e.detail.followersCount;
                    }
                }
            });
        },
        async toggleFollow() {
            if (this.isMe || this.busy) return;
            if (!this.isAuth) {
                window.location.href = @js(route('login'));
                return;
            }
            this.busy = true;
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                const res = await fetch(`/users/${this.userId}/follow`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.followed = Boolean(data.following);
                    this.followersCount = Number(data.followers_count ?? this.followersCount);
                    window.dispatchEvent(new CustomEvent('digisafe-follow-changed', {
                        detail: {
                            userId: this.userId,
                            following: this.followed,
                            followersCount: this.followersCount
                        }
                    }));
                }
            } finally {
                this.busy = false;
            }
        }
    }"
    {{ $attributes->merge(['class' => "group relative h-full rounded-[32px] bg-white p-2.5 border border-gray-100 shadow-[0_18px_45px_rgba(15,23,42,0.07)] hover:shadow-[0_24px_55px_rgba(30,136,229,0.14)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between overflow-hidden select-none {$class}"]) }}
>
    <div>
        <!-- Banner Awan Atas (atau custom banner user jika ada) -->
        <div
            class="relative h-36 sm:h-40 w-full rounded-[24px] overflow-hidden shrink-0"
            style="background-image: url('{{ $user->banner_url ?: asset('images/bluesky.jpeg') }}'); background-size: cover; background-position: center;"
        >
            @if (! $user->banner_url)
                <div
                    class="absolute inset-0 pointer-events-none"
                    style="background: linear-gradient(180deg, rgba(186, 230, 253, 0.35) 0%, rgba(255, 255, 255, 0.25) 100%);"
                ></div>
            @endif

            @if (! $isMe)
                <button
                    type="button"
                    @click.stop="toggleFollow()"
                    :disabled="busy"
                    :class="followed ? 'bg-ink text-lime' : 'bg-white/95 text-[#0A0A0A] hover:bg-white'"
                    class="absolute top-3.5 right-3.5 rounded-full px-4 py-1.5 text-xs font-heading font-bold shadow-sm inline-flex items-center gap-1.5 transition-all cursor-pointer z-20 disabled:opacity-60"
                >
                    <span x-text="followed ? @js($isEnCard ? 'Following' : 'Mengikuti') : @js($isEnCard ? 'Follow' : 'Ikuti')"></span>
                    <svg x-show="followed" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <svg x-show="!followed" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </button>
            @else
                <span class="absolute top-3.5 right-3.5 rounded-full px-3.5 py-1.5 text-[11px] font-heading font-bold bg-ink/90 text-lime z-20">
                    {{ $isEnCard ? 'My Profile' : 'Profil Saya' }}
                </span>
            @endif
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
                <span class="pb-2 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span>{{ __('ui.profile.private_account_badge') }}</span>
                </span>
            @endif
        </div>

        <!-- Nama Lengkap, Role Slot Tetap (h-6), & Bio 2 Baris Tetap (h-[38px]) agar Tinggi Seluruh Kartu Seragam -->
        <a href="{{ $targetUrl }}" class="block px-4 pt-3.5 pb-4 text-left">
            <div class="flex items-center gap-2 h-7">
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

            <div class="mt-1 h-6 flex items-center">
                @if (! $isPrivate && filled($user->specialty_role))
                    <span class="inline-block rounded-full bg-[#EFF6FF] border border-sky/20 px-2.5 py-0.5 text-[10px] font-heading font-bold text-sky truncate max-w-full">
                        {{ $user->specialty_role }}
                    </span>
                @elseif (! $isPrivate)
                    <span class="inline-block rounded-full bg-gray-100 border border-gray-200/80 px-2.5 py-0.5 text-[10px] font-heading font-semibold text-gray-500 truncate max-w-full">
                        {{ $isEnCard ? 'Community Member' : 'Warga Siber' }}
                    </span>
                @else
                    <span class="inline-block rounded-full bg-amber-50 border border-amber-200/80 px-2.5 py-0.5 text-[10px] font-heading font-semibold text-amber-700 truncate max-w-full">
                        {{ __('ui.profile.private_account_badge') }}
                    </span>
                @endif
            </div>

            <p class="mt-1.5 text-xs text-gray-500 leading-[19px] line-clamp-2 h-[38px] overflow-hidden">
                {{ $bioText }}
            </p>
        </a>
    </div>

    @if (! $isPrivate)
        <!-- Bagian Bawah: 3 Kolom Statistik Nyata (Followers, Following, Kontribusi) + 3 Ikon Profil -->
        <div class="shrink-0">
            <a
                href="{{ $targetUrl }}"
                class="grid grid-cols-3 divide-x divide-gray-100 border-t border-b border-gray-100 py-3.5 text-center hover:bg-gray-50/50 transition-colors"
            >
                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight" x-text="followersCount">
                        {{ $followersCount }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ $isEnCard ? 'Followers' : 'Pengikut' }}</span>
                </div>

                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight">
                        {{ $followingCount }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ $isEnCard ? 'Following' : 'Mengikuti' }}</span>
                </div>

                <div class="px-2">
                    <span class="font-heading font-extrabold text-sm sm:text-base text-[#0A0A0A] block leading-tight">
                        {{ $contributionsCount }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium mt-0.5 block">{{ $isEnCard ? 'Posts' : 'Kontribusi' }}</span>
                </div>
            </a>

            <div class="grid grid-cols-3 divide-x divide-gray-100 py-3 text-center bg-[#FAFAFA]/70 rounded-b-[22px]">
                <a href="{{ $targetUrl }}" aria-label="Profil {{ $displayName }}" class="flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </a>

                <a href="{{ route('forum.index') }}" aria-label="Forum {{ $displayName }}" class="flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
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
        <!-- Footer mode privat dengan tinggi identik agar ukuran kartu tetap 100% seragam -->
        <div class="shrink-0">
            <div class="border-t border-b border-gray-100 py-3.5 px-4 text-center flex flex-col items-center justify-center h-[57px]">
                <div class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-muted">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span>{{ __('ui.profile.private_account_badge') }}</span>
                </div>
            </div>
            <div class="grid grid-cols-3 divide-x divide-gray-100 py-3 text-center bg-[#FAFAFA]/70 rounded-b-[22px]">
                <a href="{{ $targetUrl }}" aria-label="Profil {{ $displayName }}" class="col-span-3 flex items-center justify-center text-[#0A0A0A]/80 hover:text-sky transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </a>
            </div>
        </div>
    @endif
</div>
