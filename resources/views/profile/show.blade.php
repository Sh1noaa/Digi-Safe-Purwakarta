@extends('layouts.app')

@section('title', ($profileUser->nama_lengkap ?: $profileUser->username) . ' | Profil Digi-Safe Purwakarta')

@section('hide_footer', '1')

@section('content')

    @php
        $isDarkProfile = ($currentTheme ?? 'light') === 'dark';
        $isEnProfile = app()->getLocale() === 'en';
    @endphp

    <style>
        .profile-cursor-stage {
            --cx: 50%;
            --cy: 26%;
            --c-opacity: 0;
        }
        .profile-cursor-glow {
            opacity: var(--c-opacity);
            transition: opacity 380ms ease;
            background: radial-gradient(
                580px circle at var(--cx) var(--cy),
                {{ $isDarkProfile ? 'rgba(56, 189, 248, 0.22)' : 'rgba(30, 99, 233, 0.16)' }} 0%,
                {{ $isDarkProfile ? 'rgba(30, 99, 233, 0.11)' : 'rgba(14, 165, 233, 0.08)' }} 35%,
                {{ $isDarkProfile ? 'rgba(200, 243, 74, 0.05)' : 'rgba(200, 243, 74, 0.055)' }} 55%,
                transparent 72%
            );
        }
        .profile-cursor-weave {
            opacity: var(--c-opacity);
            transition: opacity 420ms ease;
            background-image:
                linear-gradient(to right, {{ $isDarkProfile ? 'rgba(56, 189, 248, 0.22)' : 'rgba(30, 99, 233, 0.18)' }} 1px, transparent 1px),
                linear-gradient(to bottom, {{ $isDarkProfile ? 'rgba(56, 189, 248, 0.22)' : 'rgba(30, 99, 233, 0.18)' }} 1px, transparent 1px),
                radial-gradient({{ $isDarkProfile ? 'rgba(200, 243, 74, 0.55)' : 'rgba(30, 99, 233, 0.36)' }} 1.25px, transparent 1.25px);
            background-size: 36px 36px, 36px 36px, 36px 36px;
            background-position: 0 0, 0 0, -0.5px -0.5px;
            -webkit-mask-image: radial-gradient(360px circle at var(--cx) var(--cy), rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.38) 46%, transparent 74%);
            mask-image: radial-gradient(360px circle at var(--cx) var(--cy), rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.38) 46%, transparent 74%);
        }
        @media (prefers-reduced-motion: reduce) {
            .profile-cursor-glow,
            .profile-cursor-weave {
                display: none !important;
            }
        }
    </style>

    <section
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            settingsOpen: false,
            friendsModalOpen: false,
            followModalOpen: false,
            followModalTab: 'followers',
            followListLoading: false,
            followList: { followers: [], following: [] },
            isFollowing: {{ ($isFollowingProfile ?? false) ? 'true' : 'false' }},
            followersCount: {{ (int) ($followersCount ?? 0) }},
            followingCount: {{ (int) ($followingCount ?? 0) }},
            followBusy: false,
            previewUrl: null,
            bannerPreviewUrl: null,
            specialtyRole: @js(old('specialty_role', $profileUser->specialty_role ?? '')),
            friendStatus: @js($friendRelation ?? 'none'),
            friendBusy: false,
            borders: @js($avatarBorders ?? []),
            equippedBorder: @js($profileUser->avatar_border ?: null),
            equippedBorderUrl: @js($profileUser->avatar_border_url),
            borderModalOpen: false,
            draftBorderKey: @js($profileUser->avatar_border ?: null),
            draftBorderUrl: @js($profileUser->avatar_border_url),
            borderBusy: false,
            borderNotice: '',
            borderError: '',
            init() {
                if ({{ $isOwnProfile ? 'true' : 'false' }} && window.location.hash === '#avatar-borders') {
                    this.openBorderModal();
                }
            },
            openBorderModal() {
                this.draftBorderKey = this.equippedBorder || null;
                this.draftBorderUrl = this.equippedBorderUrl || null;
                this.borderError = '';
                this.borderModalOpen = true;
            },
            pickDraftBorder(borderKey) {
                if (!borderKey) {
                    this.draftBorderKey = null;
                    this.draftBorderUrl = null;
                    this.borderError = '';
                    return;
                }
                const target = this.borders.find(b => b.key === borderKey);
                if (!target || !target.unlocked) return;
                this.draftBorderKey = target.key;
                this.draftBorderUrl = target.image_url;
                this.borderError = '';
            },
            cursorRaf: null,
            targetX: 0,
            targetY: 0,
            currentX: 0,
            currentY: 0,
            hasCursorPos: false,
            onCursorMove(e) {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || window.matchMedia('(pointer: coarse)').matches) return;
                const rect = this.$el.getBoundingClientRect();
                this.targetX = e.clientX - rect.left;
                this.targetY = e.clientY - rect.top;
                if (!this.hasCursorPos) {
                    this.currentX = this.targetX;
                    this.currentY = this.targetY;
                    this.hasCursorPos = true;
                }
                this.$el.style.setProperty('--c-opacity', '1');
                if (!this.cursorRaf) {
                    const step = () => {
                        this.currentX += (this.targetX - this.currentX) * 0.14;
                        this.currentY += (this.targetY - this.currentY) * 0.14;
                        this.$el.style.setProperty('--cx', this.currentX.toFixed(1) + 'px');
                        this.$el.style.setProperty('--cy', this.currentY.toFixed(1) + 'px');
                        if (Math.abs(this.targetX - this.currentX) > 0.4 || Math.abs(this.targetY - this.currentY) > 0.4) {
                            this.cursorRaf = requestAnimationFrame(step);
                        } else {
                            this.cursorRaf = null;
                        }
                    };
                    this.cursorRaf = requestAnimationFrame(step);
                }
            },
            onCursorLeave() {
                this.$el.style.setProperty('--c-opacity', '0');
            },
            async saveBorderChanges() {
                if (this.borderBusy) return;
                const borderKey = this.draftBorderKey || '';
                if (borderKey) {
                    const target = this.borders.find(b => b.key === borderKey);
                    if (!target || !target.unlocked) return;
                }
                this.borderBusy = true;
                this.borderError = '';
                this.borderNotice = '';
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const res = await fetch('{{ route('profile.border') }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ avatar_border: borderKey })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.equippedBorder = data.avatar_border || null;
                        this.equippedBorderUrl = data.avatar_border_url || null;
                        this.draftBorderKey = this.equippedBorder;
                        this.draftBorderUrl = this.equippedBorderUrl;
                        this.borders = this.borders.map(b => ({ ...b, is_equipped: b.key === this.equippedBorder }));
                        this.borderModalOpen = false;
                        if (window.digisafeToast && data.message) {
                            window.digisafeToast(data.message, 'success');
                        }
                    } else {
                        this.borderError = data.message || 'Gagal memperbarui border.';
                    }
                } finally {
                    this.borderBusy = false;
                }
            },
            async toggleProfileFollow() {
                if (this.followBusy) return;
                this.followBusy = true;
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const uid = {{ (int) $profileUser->id }};
                    const res = await fetch(`/users/${uid}/follow`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.isFollowing = Boolean(data.following);
                        this.followersCount = Number(data.followers_count ?? this.followersCount);
                    }
                } finally {
                    this.followBusy = false;
                }
            },
            async openFollowModal(tab = 'followers') {
                this.followModalTab = tab;
                this.followModalOpen = true;
                this.followListLoading = true;
                try {
                    const uid = {{ (int) $profileUser->id }};
                    const res = await fetch(`/users/${uid}/follows`, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        this.followList = {
                            followers: data.followers || [],
                            following: data.following || []
                        };
                        this.followersCount = Number(data.followers_count ?? this.followersCount);
                        this.followingCount = Number(data.following_count ?? this.followingCount);
                    }
                } finally {
                    this.followListLoading = false;
                }
            },
            async toggleFollowItem(item) {
                if (!item || item.is_me || item._busy) return;
                item._busy = true;
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const res = await fetch(`/users/${item.id}/follow`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        item.is_following = Boolean(data.following);
                        if ({{ $isOwnProfile ? 'true' : 'false' }}) {
                            this.followingCount = Number(data.my_following_count ?? this.followingCount);
                        }
                    }
                } finally {
                    item._busy = false;
                }
            },
            async handleFriendAction(action) {
                if (this.friendBusy) return;
                this.friendBusy = true;
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const uid = {{ (int) $profileUser->id }};
                    let url = `/friends/request/${uid}`;
                    let method = 'POST';
                    if (action === 'accept') url = `/friends/accept/${uid}`;
                    if (action === 'decline') url = `/friends/decline/${uid}`;
                    if (action === 'remove') { url = `/friends/${uid}`; method = 'DELETE'; }

                    const res = await fetch(url, {
                        method,
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status) this.friendStatus = data.status;
                    }
                } finally {
                    this.friendBusy = false;
                }
            }
        }"
        @mousemove.window="onCursorMove($event)"
        @mouseleave="onCursorLeave()"
        class="profile-cursor-stage relative min-h-screen {{ $isDarkProfile ? 'bg-[#0B1120]' : 'bg-[#F7F9FC]' }} pt-28 sm:pt-32 pb-20 px-4 sm:px-6 lg:px-8 flex items-center overflow-x-clip transition-colors duration-300"
    >
        <!-- latar belakang minimalist responsif kursor (cahaya halus + kisi arsitektural di sekitar kursor) -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden z-0 hidden md:block">
            <div class="profile-cursor-glow absolute inset-0"></div>
            <div class="profile-cursor-weave absolute inset-0"></div>
        </div>

        <div class="relative z-10 max-w-4xl w-full mx-auto">

            <!-- notifikasi sukses ubah border avatar -->
            <div
                x-show="borderNotice !== ''"
                x-transition
                style="display: none;"
                class="mb-6 rounded-2xl bg-lime/35 border border-lime px-5 py-3.5 flex items-center gap-3 text-xs font-semibold text-ink shadow-2xs"
            >
                <span class="w-6 h-6 rounded-full bg-ink text-lime flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <span x-text="borderNotice"></span>
            </div>

            <!-- notifikasi sukses / error -->
            @if (session('success'))
                <div class="mb-7 rounded-2xl bg-lime/35 border border-lime px-5 py-3.5 flex items-center gap-3 text-xs font-semibold text-ink shadow-2xs">
                    <span class="w-6 h-6 rounded-full bg-ink text-lime flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-7 rounded-2xl bg-red-50 border border-red-200 px-5 py-3.5 text-xs text-red-700 shadow-2xs">
                    <p class="font-bold mb-1">{{ __('ui.profile.check_data_error') }}</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isOwnProfile && $privacy['is_private_profile'])
                <div class="mb-5 rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs font-semibold text-amber-800 flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                    <span>{{ __('ui.profile.private_self_notice') }}</span>
                </div>
            @endif

            <!-- wrapper relatif untuk persegi panjang biru di belakang sisi kiri kartu sesuai referensi -->
            <div class="relative my-6 sm:my-8">

                <!-- bentuk persegi panjang biru solid (#1E88E5) di sisi kiri belakang kartu -->
                <div
                    aria-hidden="true"
                    class="hidden sm:block absolute -left-7 md:-left-10 -top-7 -bottom-7 w-60 md:w-72 rounded-[32px] bg-[#1E88E5] shadow-lg z-0"
                ></div>

                <!-- kartu profil utama -->
                <div class="relative z-10 rounded-[28px] bg-white border border-gray-100 shadow-neutral overflow-hidden">

                    <!-- banner atas (SELALU TAMPIL meski mode privat: foto/gif custom atau warna solid/gradient pilihan user) -->
                    <div
                        class="relative h-40 sm:h-48 w-full overflow-hidden transition-all duration-300"
                        style="background: {{ $profileUser->banner_style_css }};"
                    >
                        @if ($profileUser->banner_url)
                            <img
                                src="{{ $profileUser->banner_url }}"
                                alt="Banner {{ $profileUser->nama_lengkap ?: $profileUser->username }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div
                                class="absolute inset-0 opacity-45"
                                style="background: radial-gradient(circle at 72% 25%, rgba(255, 255, 255, 0.55) 0%, transparent 55%);"
                            ></div>
                        @endif

                        @if ($isOwnProfile)
                            <button
                                type="button"
                                @click="open = true"
                                title="Ubah Banner & Profil"
                                class="absolute top-4 right-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 hover:bg-white text-ink px-3.5 py-2 text-[11px] font-heading font-bold shadow-sm transition-transform hover:scale-105 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                <span>{{ __('ui.profile.edit_btn') }}</span>
                            </button>
                        @elseif ($isPrivateForVisitor)
                            <span class="absolute top-4 right-4 inline-flex items-center gap-1.5 rounded-full bg-ink/85 text-lime px-3.5 py-1.5 text-[11px] font-heading font-bold backdrop-blur-xs">
                                <svg class="w-3.5 h-3.5 text-lime shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                <span>{{ __('ui.profile.private_account_badge') }}</span>
                            </span>
                        @endif
                    </div>

                    <!-- konten kartu di bawah banner -->
                    <div class="px-6 sm:px-10 pb-8 sm:pb-10">

                        <!-- avatar bulat overlap dengan border putih 4px + border avatar kustom yang reaktif -->
                        <div class="-mt-14 sm:-mt-16 mb-4 flex items-end justify-between">
                            <div class="relative inline-flex items-center justify-center w-24 h-24 sm:w-28 sm:h-28">
                                <x-user-avatar
                                    :user="$profileUser"
                                    :border="''"
                                    size="profile"
                                    class="border-4 border-white shadow-md bg-white"
                                />
                                <template x-if="equippedBorderUrl">
                                    <img
                                        :src="equippedBorderUrl"
                                        alt=""
                                        aria-hidden="true"
                                        class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-20 transition-all duration-300"
                                    >
                                </template>
                            </div>
                        </div>

                        @if ($isPrivateForVisitor)
                            <!-- MODE PRIVAT UNTUK PENGUNJUNG: Hanya Nama, Foto Profil (PP), dan Banner -->
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                                            {{ $profileUser->nama_lengkap ?: $profileUser->username }}
                                        </h1>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-200 px-3 py-1 text-[10px] font-extrabold text-muted uppercase tracking-wider">
                                            <svg class="w-3 h-3 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                            <span>{{ __('ui.profile.private_account_badge') }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2.5">
                                    @auth
                                        <button
                                            type="button"
                                            @click="toggleProfileFollow()"
                                            :disabled="followBusy"
                                            :class="isFollowing ? 'bg-ink text-lime border-ink' : 'bg-white text-ink border-gray-300 hover:border-sky hover:text-sky'"
                                            class="inline-flex items-center gap-1.5 rounded-full border font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer disabled:opacity-60"
                                        >
                                            <svg x-show="isFollowing" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><polyline points="20 6 9 17 4 12" /></svg>
                                            <svg x-show="!isFollowing" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                            <span x-text="isFollowing ? @js(app()->getLocale() === 'en' ? 'Following' : 'Mengikuti') : @js(app()->getLocale() === 'en' ? 'Follow' : 'Ikuti')"></span>
                                        </button>
                                        <template x-if="friendStatus === 'accepted'">
                                            <button
                                                type="button"
                                                @click="handleFriendAction('remove')"
                                                :disabled="friendBusy"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-lime text-ink border border-lime font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                                <span>Berteman</span>
                                            </button>
                                        </template>
                                        <template x-if="friendStatus === 'pending_outgoing'">
                                            <button
                                                type="button"
                                                @click="handleFriendAction('remove')"
                                                :disabled="friendBusy"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <span>Menunggu Konfirmasi</span>
                                            </button>
                                        </template>
                                        <template x-if="friendStatus === 'pending_incoming'">
                                            <button
                                                type="button"
                                                @click="handleFriendAction('accept')"
                                                :disabled="friendBusy"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-sky text-white border border-sky font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <span>Konfirmasi Pertemanan</span>
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                            </button>
                                        </template>
                                        <template x-if="friendStatus === 'none' || friendStatus === 'declined'">
                                            <button
                                                type="button"
                                                @click="handleFriendAction('request')"
                                                :disabled="friendBusy"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-ink hover:bg-sky text-white border border-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <span>+ Tambah Teman</span>
                                            </button>
                                        </template>

                                        <button
                                            type="button"
                                            @click="window.dispatchEvent(new CustomEvent('open-chat-with', { detail: { userId: {{ (int) $profileUser->id }} } }))"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                        >
                                            <span>Chat</span>
                                        </button>
                                    @endauth

                                    <a
                                        href="{{ route('forum.index') }}"
                                        class="inline-flex items-center justify-center rounded-full bg-white border border-gray-300 hover:border-ink text-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors"
                                    >
                                        {{ __('ui.profile.back_forum') }}
                                    </a>
                                </div>
                            </div>

                            <div class="mt-7 rounded-2xl bg-[#F8FAFC] border border-dashed border-gray-200 p-8 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                </div>
                                <h2 class="font-heading font-extrabold text-base text-ink">{{ __('ui.profile.private_title') }}</h2>
                                <p class="mt-1.5 text-xs text-muted max-w-md mx-auto leading-relaxed">
                                    {{ __('ui.profile.private_desc') }}
                                </p>
                            </div>
                        @else
                            <!-- PROFIL LENGKAP (Untuk Pemilik Sendiri atau Pengunjung Non-Privat dengan Kontrol Privasi Granular) -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                <!-- kolom kiri -->
                                <div class="md:col-span-6">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                                            {{ $profileUser->nama_lengkap ?: $profileUser->username }}
                                        </h1>

                                        @if ($profileUser->isOwner())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                                <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                                </svg>
                                                <span>Owner</span>
                                            </span>
                                        @elseif ($profileUser->isAdmin())
                                            <span class="rounded-full bg-lime text-ink px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                                Admin
                                            </span>
                                        @endif

                                        @if (filled($profileUser->specialty_role))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-[#EFF6FF] border border-sky/25 text-sky px-3 py-1 text-[11px] font-heading font-bold">
                                                <span>{{ $profileUser->specialty_role }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs sm:text-sm font-medium text-muted">
                                        {{ $profileUser->role_label }} • <span class="text-ink/80 font-semibold">&#64;{{ $profileUser->username }}</span>
                                    </p>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            @click="openFollowModal('followers')"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-gray-200 hover:border-sky/40 px-3.5 py-1.5 text-xs font-heading font-bold text-ink transition-colors cursor-pointer"
                                        >
                                            <span class="font-extrabold text-sky" x-text="followersCount">{{ $followersCount ?? 0 }}</span>
                                            <span>{{ app()->getLocale() === 'en' ? 'Followers' : 'Pengikut' }}</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="openFollowModal('following')"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-gray-200 hover:border-sky/40 px-3.5 py-1.5 text-xs font-heading font-bold text-ink transition-colors cursor-pointer"
                                        >
                                            <span class="font-extrabold text-ink" x-text="followingCount">{{ $followingCount ?? 0 }}</span>
                                            <span>{{ app()->getLocale() === 'en' ? 'Following' : 'Mengikuti' }}</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="friendsModalOpen = true"
                                            class="inline-flex items-center gap-2 rounded-full bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-gray-200 hover:border-sky/40 px-3.5 py-1.5 text-xs font-heading font-bold text-ink transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            <span>{{ $friendsCount }} {{ app()->getLocale() === 'en' ? 'Friends' : 'Teman' }}</span>
                                        </button>
                                    </div>

                                    @if ($isOwnProfile || ! $privacy['hide_location'])
                                        <p class="mt-1.5 text-xs sm:text-sm text-muted leading-relaxed">
                                            {{ $profileUser->bio ?: 'Purwakarta, Jawa Barat, Indonesia' }}
                                            @if ($isOwnProfile && $privacy['hide_location'])
                                                <span class="ml-1 inline-block rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[10px] font-bold">Hidden</span>
                                            @endif
                                        </p>
                                    @else
                                        <p class="mt-1.5 text-xs italic text-muted inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                            <span>{{ __('ui.profile.hidden_location') }}</span>
                                        </p>
                                    @endif

                                    @if ($isOwnProfile && ! $privacy['hide_email'])
                                        <p class="mt-1 text-[11px] text-muted">{{ $profileUser->email }}</p>
                                    @endif

                                    <!-- tombol aksi terpisah: Edit Profil vs Pengaturan -->
                                    <div class="mt-5 flex flex-wrap items-center gap-3">
                                        @if ($isOwnProfile)
                                            <button
                                                type="button"
                                                @click="open = true"
                                                class="inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 shadow-2xs transition-colors cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                <span>{{ __('ui.profile.edit_btn') }}</span>
                                            </button>

                                            <button
                                                type="button"
                                                @click="settingsOpen = true"
                                                class="inline-flex items-center gap-2 rounded-full bg-white border border-ink/75 hover:border-sky hover:text-sky text-ink font-heading font-bold text-xs px-6 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3" />
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                                </svg>
                                                <span>{{ __('ui.profile.settings_btn') }}</span>
                                            </button>

                                            <button
                                                type="button"
                                                @click="openBorderModal()"
                                                class="inline-flex items-center gap-2 rounded-full bg-lime/35 border border-lime hover:bg-lime text-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="4" /></svg>
                                                <span>{{ $isEnProfile ? 'Avatar Borders' : 'Border Avatar' }}</span>
                                            </button>
                                        @else
                                            @auth
                                                <button
                                                    type="button"
                                                    @click="toggleProfileFollow()"
                                                    :disabled="followBusy"
                                                    :class="isFollowing ? 'bg-ink text-lime border-ink' : 'bg-white text-ink border-gray-300 hover:border-sky hover:text-sky'"
                                                    class="inline-flex items-center gap-1.5 rounded-full border font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer disabled:opacity-60"
                                                    title="{{ app()->getLocale() === 'en' ? 'Follow publicly without confirmation' : 'Ikuti profil publik tanpa perlu konfirmasi' }}"
                                                >
                                                    <svg x-show="isFollowing" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><polyline points="20 6 9 17 4 12" /></svg>
                                                    <svg x-show="!isFollowing" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                                    <span x-text="isFollowing ? @js(app()->getLocale() === 'en' ? 'Following' : 'Mengikuti') : @js(app()->getLocale() === 'en' ? 'Follow' : 'Ikuti')"></span>
                                                </button>
                                                <template x-if="friendStatus === 'accepted'">
                                                    <button
                                                        type="button"
                                                        @click="handleFriendAction('remove')"
                                                        :disabled="friendBusy"
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-lime text-ink border border-lime font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                                        title="{{ app()->getLocale() === 'en' ? 'Friends — private chat unlocked (click to unfriend)' : 'Sudah berteman — chat pribadi aktif (klik untuk batal berteman)' }}"
                                                    >
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                                        <span>{{ app()->getLocale() === 'en' ? 'Friends' : 'Berteman' }}</span>
                                                    </button>
                                                </template>
                                                <template x-if="friendStatus === 'pending_outgoing'">
                                                    <button
                                                        type="button"
                                                        @click="handleFriendAction('remove')"
                                                        :disabled="friendBusy"
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                                    >
                                                        <span>{{ app()->getLocale() === 'en' ? 'Pending Request' : 'Menunggu Konfirmasi' }}</span>
                                                    </button>
                                                </template>
                                                <template x-if="friendStatus === 'pending_incoming'">
                                                    <button
                                                        type="button"
                                                        @click="handleFriendAction('accept')"
                                                        :disabled="friendBusy"
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-sky text-white border border-sky font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                                    >
                                                        <span>{{ app()->getLocale() === 'en' ? 'Accept Friend' : 'Konfirmasi Pertemanan' }}</span>
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                                    </button>
                                                </template>
                                                <template x-if="friendStatus === 'none' || friendStatus === 'declined'">
                                                    <button
                                                        type="button"
                                                        @click="handleFriendAction('request')"
                                                        :disabled="friendBusy"
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-ink hover:bg-sky text-white border border-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                                        title="{{ app()->getLocale() === 'en' ? 'Send friend request to unlock direct private chat' : 'Kirim permintaan teman untuk membuka akses chat pribadi' }}"
                                                    >
                                                        <span>{{ app()->getLocale() === 'en' ? '+ Add Friend' : '+ Tambah Teman' }}</span>
                                                    </button>
                                                </template>

                                                <button
                                                    type="button"
                                                    @click="window.dispatchEvent(new CustomEvent('open-chat-with', { detail: { userId: {{ (int) $profileUser->id }} } }))"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                                                >
                                                    <span>Chat</span>
                                                </button>
                                            @else
                                                <a
                                                    href="{{ route('login') }}"
                                                    class="inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 transition-colors"
                                                >
                                                    <span>+ Tambah Teman / Chat</span>
                                                </a>
                                            @endauth

                                            <a
                                                href="{{ route('forum.index') }}"
                                                class="inline-flex items-center justify-center rounded-full bg-white border border-gray-300 hover:border-ink text-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors"
                                            >
                                                {{ __('ui.profile.back_forum') }}
                                            </a>

                                            @auth
                                                <p class="w-full text-[11px] text-muted mt-1">
                                                    {{ app()->getLocale() === 'en'
                                                        ? 'Follow: public profile updates • Add Friend: unlock direct private chat.'
                                                        : 'Ikuti: pantau profil publik tanpa konfirmasi • Tambah Teman: buka akses chat pribadi.' }}
                                                </p>
                                            @endauth
                                        @endif
                                    </div>
                                </div>

                                <!-- kolom kanan (current role, select your role & lencana achievement dari minigame) -->
                                <div class="md:col-span-6 md:text-right flex flex-col md:items-end justify-between space-y-5">
                                    <div>
                                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted mb-2">
                                            <span>{{ __('ui.profile.current_role') }}</span>
                                            <svg class="w-4 h-4 text-ink/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="inline-block rounded-full bg-[#F8FAFC] border border-gray-200/80 px-4 py-1.5 text-xs font-heading font-bold text-ink">
                                                {{ $profileUser->display_role }}
                                            </span>
                                        </div>

                                        @if ($isOwnProfile)
                                            @php
                                                $quickRoles = \App\Models\User::specialtyRoleOptions(app()->getLocale() === 'en');
                                            @endphp
                                            <div
                                                x-data="{ rolePickerOpen: false }"
                                                @click.outside="rolePickerOpen = false"
                                                class="mt-3 relative inline-block text-left"
                                            >
                                                <form x-ref="quickRoleForm" action="{{ route('profile.role') }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="specialty_role" x-ref="quickRoleInput" value="{{ $profileUser->specialty_role }}">
                                                </form>

                                                <div class="flex flex-col md:items-end gap-1.5">
                                                    <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-sky">
                                                        Select Your Role
                                                    </span>
                                                    <button
                                                        type="button"
                                                        @click="rolePickerOpen = !rolePickerOpen"
                                                        class="inline-flex items-center justify-between gap-2.5 rounded-full bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-gray-200/90 hover:border-sky px-4 py-2 text-xs font-heading font-bold text-ink shadow-2xs transition-all cursor-pointer"
                                                    >
                                                        <span class="w-2 h-2 rounded-full bg-lime ring-2 ring-ink/10 shrink-0"></span>
                                                        <span>{{ filled($profileUser->specialty_role) ? $profileUser->specialty_role : (app()->getLocale() === 'en' ? 'Choose Your Role' : 'Pilih Role Anda') }}</span>
                                                        <svg
                                                            :class="rolePickerOpen ? 'rotate-180 text-sky' : 'text-muted'"
                                                            class="w-3.5 h-3.5 transition-transform duration-200"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2.2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        >
                                                            <polyline points="6 9 12 15 18 9" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Popover Menu Select Your Role (4 Pilihan Variatif) -->
                                                <div
                                                    x-show="rolePickerOpen"
                                                    x-transition:enter="transition ease-out duration-150"
                                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                    x-transition:leave="transition ease-in duration-100"
                                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                                    style="display: none;"
                                                    class="absolute left-0 md:left-auto md:right-0 mt-2 w-72 rounded-2xl bg-white border border-gray-200/90 shadow-[0_18px_42px_rgba(15,23,42,0.16)] p-2 z-40 text-left space-y-1"
                                                >
                                                    <div class="px-2.5 py-1.5 flex items-center justify-between border-b border-gray-100 mb-1">
                                                        <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-muted">
                                                            Select Your Role
                                                        </span>
                                                        <span class="rounded-full bg-sky/10 text-sky px-2 py-0.5 text-[9px] font-extrabold">
                                                            Maks 4
                                                        </span>
                                                    </div>

                                                    @foreach ($quickRoles as $qr)
                                                        @php
                                                            $isCurrentQr = $profileUser->specialty_role === $qr['value'];
                                                            $iconColors = match ($qr['icon']) {
                                                                'shield' => 'bg-sky/15 text-sky',
                                                                'book' => 'bg-lime/40 text-ink',
                                                                'search' => 'bg-amber-100 text-amber-700',
                                                                default => 'bg-indigo-100 text-indigo-700',
                                                            };
                                                        @endphp
                                                        <button
                                                            type="button"
                                                            @click="$refs.quickRoleInput.value = @js($qr['value']); rolePickerOpen = false; $refs.quickRoleForm.submit();"
                                                            class="w-full rounded-xl p-2 flex items-center gap-2.5 transition-colors cursor-pointer {{ $isCurrentQr ? 'bg-[#EFF6FF] border border-sky/40' : 'hover:bg-[#F8FAFC] border border-transparent' }}"
                                                        >
                                                            <span class="w-8 h-8 rounded-lg {{ $iconColors }} flex items-center justify-center shrink-0">
                                                                @if ($qr['icon'] === 'shield')
                                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                                                                @elseif ($qr['icon'] === 'book')
                                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                                                @elseif ($qr['icon'] === 'search')
                                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                                @else
                                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                                                @endif
                                                            </span>
                                                            <div class="min-w-0 flex-1">
                                                                <span class="font-heading font-bold text-xs text-ink block truncate">{{ $qr['label'] }}</span>
                                                                <span class="text-[10px] text-muted block truncate">{{ $qr['desc'] }}</span>
                                                            </div>
                                                            @if ($isCurrentQr)
                                                                <span class="w-4 h-4 rounded-full bg-sky text-white flex items-center justify-center shrink-0">
                                                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endforeach

                                                    @if (filled($profileUser->specialty_role))
                                                        <button
                                                            type="button"
                                                            @click="$refs.quickRoleInput.value = ''; rolePickerOpen = false; $refs.quickRoleForm.submit();"
                                                            class="w-full rounded-xl px-2.5 py-1.5 text-[11px] font-semibold text-muted hover:text-rose-600 hover:bg-rose-50 text-center transition-colors cursor-pointer mt-1 border-t border-gray-100"
                                                        >
                                                            {{ app()->getLocale() === 'en' ? 'Reset to Default Role' : 'Reset ke Peran Bawaan' }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted mb-2">
                                            <span>{{ __('ui.profile.badges_title') }}</span>
                                            <svg class="w-4 h-4 text-ink/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        </div>

                                        @if (! $isOwnProfile && $privacy['hide_badges'])
                                            <p class="text-[11px] italic text-muted inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                                <span>{{ __('ui.profile.hidden_section') }}</span>
                                            </p>
                                        @elseif (count($badges) > 0)
                                            <div class="flex flex-wrap md:justify-end gap-2">
                                                @foreach ($badges as $badge)
                                                    @if ($badge === 'New Player')
                                                        <span
                                                            title="Otomatis diberikan untuk pemain baru dan hilang setelah 1 bulan"
                                                            class="inline-flex items-center gap-1.5 rounded-full bg-[#DBEAFE] border border-sky/30 px-3.5 py-1 text-[11px] font-heading font-bold text-sky"
                                                        >
                                                            <span class="w-1.5 h-1.5 rounded-full bg-sky"></span>
                                                            <span>New Player</span>
                                                        </span>
                                                    @else
                                                        <span
                                                            title="Diraih dari pencapaian Minigame"
                                                            class="inline-flex items-center gap-1.5 rounded-full bg-lime/35 border border-lime px-3.5 py-1 text-[11px] font-heading font-bold text-ink"
                                                        >
                                                            <svg class="w-3 h-3 text-ink" viewBox="0 0 24 24" fill="currentColor">
                                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                            </svg>
                                                            <span>{{ $badge }}</span>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-[11px] text-muted">{{ __('ui.profile.no_badges') }}</p>
                                        @endif

                                        <p class="mt-2 text-[10px] text-muted">
                                            {{ __('ui.profile.badge_hint') }}
                                        </p>
                                    </div>
                                </div>

                            </div>

                            <!-- baris bawah: 3 kotak card info tambahan (menghormati pengaturan privasi) -->
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">

                                <!-- kotak 1: total skor minigame -->
                                @if ($isOwnProfile || ! $privacy['hide_minigame_stats'])
                                    <a
                                        href="{{ route('minigame.index') }}"
                                        class="group rounded-2xl bg-[#F8FAFC] border border-gray-200/75 hover:border-sky p-4 sm:p-5 flex items-center justify-between gap-3 transition-all"
                                    >
                                        <div>
                                            <h2 class="font-heading font-bold text-sm text-ink">
                                                {{ __('ui.profile.total_score', ['score' => number_format($totalSkor)]) }}
                                            </h2>
                                            <p class="mt-1 text-[11px] text-muted leading-relaxed">
                                                {{ __('ui.profile.score_desc', ['top' => $topSkor, 'matches' => $totalMain]) }}
                                            </p>
                                        </div>
                                        <span class="w-8 h-8 rounded-full border border-sky/40 text-sky group-hover:bg-sky group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                                <polyline points="12 5 19 12 12 19" />
                                            </svg>
                                        </span>
                                    </a>
                                @else
                                    <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/75 p-4 sm:p-5 flex items-center justify-center gap-1.5 text-center text-[11px] text-muted">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                        <span>{{ __('ui.profile.hidden_section') }}</span>
                                    </div>
                                @endif

                                <!-- kotak 2: jumlah ulasan testimoni -->
                                @if ($isOwnProfile || ! $privacy['hide_activity'])
                                    <a
                                        href="{{ route('testimoni.index') }}"
                                        class="group rounded-2xl bg-[#F8FAFC] border border-gray-200/75 hover:border-sky p-4 sm:p-5 flex items-center justify-between gap-3 transition-all"
                                    >
                                        <div>
                                            <h2 class="font-heading font-bold text-sm text-ink">
                                                {{ __('ui.profile.total_reviews', ['count' => $totalUlasan]) }}
                                            </h2>
                                            <p class="mt-1 text-[11px] text-muted leading-relaxed">
                                                {{ __('ui.profile.reviews_desc') }}
                                            </p>
                                        </div>
                                        <span class="w-8 h-8 rounded-full border border-sky/40 text-sky group-hover:bg-sky group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                                <polyline points="12 5 19 12 12 19" />
                                            </svg>
                                        </span>
                                    </a>
                                @else
                                    <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/75 p-4 sm:p-5 flex items-center justify-center gap-1.5 text-center text-[11px] text-muted">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                        <span>{{ __('ui.profile.hidden_section') }}</span>
                                    </div>
                                @endif

                                <!-- kotak 3: total diskusi forum -->
                                @if ($isOwnProfile || ! $privacy['hide_activity'])
                                    <a
                                        href="{{ route('forum.index') }}"
                                        class="group rounded-2xl bg-[#F8FAFC] border border-gray-200/75 hover:border-sky p-4 sm:p-5 flex items-center justify-between gap-3 transition-all"
                                    >
                                        <div>
                                            <h2 class="font-heading font-bold text-sm text-ink">
                                                {{ __('ui.profile.total_discussions', ['count' => $totalDiskusi]) }}
                                            </h2>
                                            <p class="mt-1 text-[11px] text-muted leading-relaxed">
                                                {{ __('ui.profile.discussions_desc', ['threads' => $totalThread, 'replies' => $totalJawaban]) }}
                                            </p>
                                        </div>
                                        <span class="w-8 h-8 rounded-full border border-sky/40 text-sky group-hover:bg-sky group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                                <polyline points="12 5 19 12 12 19" />
                                            </svg>
                                        </span>
                                    </a>
                                @else
                                    <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/75 p-4 sm:p-5 flex items-center justify-center gap-1.5 text-center text-[11px] text-muted">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                        <span>{{ __('ui.profile.hidden_section') }}</span>
                                    </div>
                                @endif

                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>

        @if ($isOwnProfile)
            <!-- modal pop-up pemilihan 5 border avatar (opsional, unlock bertahap, dengan pratinjau langsung & tombol Simpan Perubahan) -->
            <div
                id="avatar-borders"
                x-show="borderModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none; background: rgba(10, 15, 30, 0.56);"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
                @keydown.escape.window="borderModalOpen = false"
            >
                <div
                    @click.outside="borderModalOpen = false"
                    class="w-full max-w-2xl max-h-[88vh] rounded-[26px] border {{ $isDarkProfile ? 'bg-[#0F172A] border-slate-800 text-slate-100' : 'bg-white border-gray-200/80 text-ink' }} shadow-neutral flex flex-col overflow-hidden"
                >
                    <!-- header modal + pratinjau langsung avatar user -->
                    <div class="px-6 py-5 border-b {{ $isDarkProfile ? 'border-slate-800' : 'border-gray-100' }} flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="relative w-14 h-14 shrink-0 flex items-center justify-center">
                                @if ($profileUser->avatar_url)
                                    <img src="{{ $profileUser->avatar_url }}" alt="{{ $profileUser->nama_lengkap ?: $profileUser->username }}" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-xs">
                                @else
                                    <span class="w-12 h-12 rounded-full {{ $profileUser->pastel_class }} font-heading font-extrabold text-sm flex items-center justify-center border-2 border-white shadow-xs">{{ $profileUser->inisial }}</span>
                                @endif
                                <template x-if="draftBorderUrl">
                                    <img :src="draftBorderUrl" alt="" aria-hidden="true" class="pointer-events-none select-none absolute inset-0 w-full h-full scale-[1.24] max-w-none object-contain z-10">
                                </template>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-heading font-extrabold text-base sm:text-lg {{ $isDarkProfile ? 'text-white' : 'text-ink' }}">
                                        {{ $isEnProfile ? 'Avatar Borders' : 'Koleksi Border Avatar' }}
                                    </h2>
                                    <span class="rounded-lg border {{ $isDarkProfile ? 'border-slate-700 bg-slate-800 text-slate-200' : 'border-gray-200 bg-[#F8FAFC] text-ink' }} px-2 py-0.5 text-[10px] font-heading font-bold">
                                        <span x-text="borders.filter(b => b.unlocked).length"></span> / 5 {{ $isEnProfile ? 'Unlocked' : 'Terbuka' }}
                                    </span>
                                </div>
                                <p class="text-[11px] {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }} mt-0.5 truncate">
                                    {{ $isEnProfile
                                        ? 'Choose an unlocked border, preview it above, then click Save Changes.'
                                        : 'Pilih border yang sudah terbuka, lihat pratinjau di atas, lalu klik Simpan Perubahan.' }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="borderModalOpen = false"
                            aria-label="{{ $isEnProfile ? 'Close modal' : 'Tutup jendela' }}"
                            class="w-9 h-9 rounded-full {{ $isDarkProfile ? 'bg-slate-800 hover:bg-slate-700 text-slate-200' : 'bg-gray-100 hover:bg-gray-200 text-ink' }} flex items-center justify-center shrink-0 transition-colors cursor-pointer"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- daftar pilihan border scrollable -->
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 {{ $isDarkProfile ? 'bg-[#0B1120]/60' : 'bg-[#F8FAFC]' }}">
                        <div
                            x-show="borderError !== ''"
                            x-transition
                            style="display: none;"
                            class="rounded-xl bg-red-50 border border-red-200 px-4 py-2.5 text-xs font-semibold text-red-700"
                            x-text="borderError"
                        ></div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <!-- Opsi 0: Tanpa Border -->
                            <button
                                type="button"
                                @click="pickDraftBorder('')"
                                :class="!draftBorderKey
                                    ? '{{ $isDarkProfile ? 'border-lime bg-lime/10 ring-1 ring-lime/40' : 'border-sky bg-[#F0F8FF] ring-1 ring-sky/25' }}'
                                    : '{{ $isDarkProfile ? 'border-slate-800 bg-[#0F172A] hover:border-slate-600' : 'border-gray-200 bg-white hover:border-gray-400' }}'"
                                class="rounded-2xl border p-4 text-left flex flex-col justify-between gap-3 transition-all cursor-pointer"
                            >
                                <div class="flex items-center gap-3.5">
                                    <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                                        @if ($profileUser->avatar_url)
                                            <img src="{{ $profileUser->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover border-2 border-gray-200">
                                        @else
                                            <span class="w-11 h-11 rounded-full {{ $profileUser->pastel_class }} font-heading font-extrabold text-xs flex items-center justify-center border-2 border-gray-200">{{ $profileUser->inisial }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }}">{{ $isEnProfile ? 'Default' : 'Bawaan' }}</span>
                                        <span class="block font-heading font-extrabold text-sm {{ $isDarkProfile ? 'text-white' : 'text-ink' }}">{{ $isEnProfile ? 'No Border' : 'Tanpa Border' }}</span>
                                        <span class="block text-[11px] {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }} mt-0.5">{{ $isEnProfile ? 'Clean circular avatar' : 'Tampilan avatar polos bersih' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t {{ $isDarkProfile ? 'border-slate-800' : 'border-gray-100' }}">
                                    <span class="text-[10px] font-semibold {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }}">{{ $isEnProfile ? 'Always available' : 'Selalu tersedia' }}</span>
                                    <span
                                        :class="!draftBorderKey ? 'bg-ink text-lime' : '{{ $isDarkProfile ? 'bg-slate-800 text-slate-300' : 'bg-gray-100 text-ink' }}'"
                                        class="rounded-lg px-2.5 py-0.5 text-[10px] font-heading font-bold"
                                        x-text="!draftBorderKey ? @js($isEnProfile ? 'Selected' : 'Dipilih') : @js($isEnProfile ? 'Select' : 'Pilih')"
                                    ></span>
                                </div>
                            </button>

                            <!-- Opsi 1..5: 5 Border Prestasi -->
                            <template x-for="b in borders" :key="b.key">
                                <button
                                    type="button"
                                    @click="b.unlocked && pickDraftBorder(b.key)"
                                    :disabled="!b.unlocked"
                                    :class="draftBorderKey === b.key
                                        ? '{{ $isDarkProfile ? 'border-lime bg-lime/10 ring-1 ring-lime/40 cursor-pointer' : 'border-sky bg-[#F0F8FF] ring-1 ring-sky/25 cursor-pointer' }}'
                                        : (b.unlocked
                                            ? '{{ $isDarkProfile ? 'border-slate-800 bg-[#0F172A] hover:border-sky/60 cursor-pointer' : 'border-gray-200 bg-white hover:border-sky/60 cursor-pointer' }}'
                                            : '{{ $isDarkProfile ? 'border-slate-800/80 bg-slate-900/40 opacity-75 cursor-not-allowed' : 'border-gray-200/80 bg-white/70 opacity-80 cursor-not-allowed' }}')"
                                    class="rounded-2xl border p-4 text-left flex flex-col justify-between gap-3 transition-all"
                                >
                                    <div class="flex items-center gap-3.5">
                                        <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                                            @if ($profileUser->avatar_url)
                                                <img src="{{ $profileUser->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover" :class="!b.unlocked ? 'grayscale opacity-70' : ''">
                                            @else
                                                <span class="w-11 h-11 rounded-full {{ $profileUser->pastel_class }} font-heading font-extrabold text-xs flex items-center justify-center" :class="!b.unlocked ? 'grayscale opacity-70' : ''">{{ $profileUser->inisial }}</span>
                                            @endif
                                            <img
                                                :src="b.image_url"
                                                :alt="b.name"
                                                class="pointer-events-none select-none absolute inset-0 w-full h-full scale-[1.24] max-w-none object-contain z-10"
                                                :class="!b.unlocked ? 'grayscale opacity-65' : ''"
                                            >
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-sky" x-text="'Tier ' + b.tier"></span>
                                                <template x-if="!b.unlocked">
                                                    <svg class="w-3 h-3 {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                                                </template>
                                            </div>
                                            <span class="block font-heading font-extrabold text-sm truncate {{ $isDarkProfile ? 'text-white' : 'text-ink' }}" x-text="b.name"></span>
                                            <span class="block text-[11px] {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }} leading-snug mt-0.5" x-text="b.requirement"></span>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 pt-2 border-t {{ $isDarkProfile ? 'border-slate-800' : 'border-gray-100' }}">
                                        <div class="flex items-center justify-between gap-2 text-[10px]">
                                            <span class="font-semibold {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }} truncate" x-text="b.progress_label"></span>
                                            <span
                                                :class="draftBorderKey === b.key
                                                    ? 'bg-ink text-lime'
                                                    : (b.unlocked ? 'bg-sky/15 text-sky' : '{{ $isDarkProfile ? 'bg-slate-800 text-slate-400' : 'bg-gray-200/80 text-muted' }}')"
                                                class="shrink-0 rounded-lg px-2.5 py-0.5 font-heading font-bold"
                                                x-text="draftBorderKey === b.key
                                                    ? @js($isEnProfile ? 'Selected' : 'Dipilih')
                                                    : (b.unlocked ? @js($isEnProfile ? 'Select' : 'Pilih') : @js($isEnProfile ? 'Locked' : 'Terkunci'))"
                                            ></span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full {{ $isDarkProfile ? 'bg-slate-800' : 'bg-gray-200/80' }} overflow-hidden">
                                            <div
                                                :class="b.unlocked ? 'bg-emerald-500' : 'bg-sky'"
                                                :style="'width: ' + b.progress_percent + '%'"
                                                class="h-full rounded-full transition-all duration-300"
                                            ></div>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- footer modal: tombol Batal & Simpan Perubahan -->
                    <div class="px-6 py-4 border-t {{ $isDarkProfile ? 'border-slate-800 bg-[#0F172A]' : 'border-gray-100 bg-white' }} flex items-center justify-between gap-3">
                        <span class="text-[11px] {{ $isDarkProfile ? 'text-slate-400' : 'text-muted' }} hidden sm:inline">
                            {{ $isEnProfile ? 'Border updates across all Digi-Safe pages.' : 'Border akan tampil di seluruh halaman Digi-Safe.' }}
                        </span>
                        <div class="flex items-center gap-2.5 ml-auto">
                            <button
                                type="button"
                                @click="borderModalOpen = false"
                                class="rounded-xl border {{ $isDarkProfile ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-gray-200 text-muted hover:text-ink hover:bg-gray-50' }} px-4 py-2.5 text-xs font-heading font-bold transition-colors cursor-pointer"
                            >
                                {{ $isEnProfile ? 'Cancel' : 'Batal' }}
                            </button>
                            <button
                                type="button"
                                @click="saveBorderChanges()"
                                :disabled="borderBusy"
                                class="rounded-xl bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-5 py-2.5 shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                                x-text="borderBusy
                                    ? @js($isEnProfile ? 'Saving...' : 'Menyimpan...')
                                    : @js($isEnProfile ? 'Save Changes' : 'Simpan Perubahan')"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- modal edit profil & banner -->
        @include('profile.edit')

        <!-- modal pengaturan terpisah (Bahasa, Dark/Light Mode, Privasi) -->
        @include('profile.settings-modal')

        <!-- modal pop-up daftar teman -->
        <div
            x-show="friendsModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.52);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="friendsModalOpen = false"
        >
            <div
                @click.outside="friendsModalOpen = false"
                class="w-full max-w-md max-h-[82vh] rounded-[24px] bg-white border border-gray-200/80 shadow-neutral flex flex-col overflow-hidden"
            >
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-ink">
                            {{ app()->getLocale() === 'en' ? 'Friends List' : 'Daftar Teman' }} ({{ $friendsCount }})
                        </h3>
                        <p class="text-[11px] text-muted">
                            {{ $profileUser->nama_lengkap ?: $profileUser->username }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="friendsModalOpen = false"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 bg-[#F8FAFC]">
                    @if ($friendsList->isEmpty())
                        <div class="py-10 text-center text-xs text-muted">
                            {{ app()->getLocale() === 'en' ? 'No friends yet.' : 'Belum memiliki teman yang terhubung.' }}
                        </div>
                    @else
                        @foreach ($friendsList as $friendUser)
                            <div class="rounded-2xl bg-white border border-gray-100 p-3 flex items-center justify-between gap-3 shadow-2xs">
                                <a href="{{ route('profile.show', $friendUser->id) }}" class="flex items-center gap-3 min-w-0 group">
                                    <x-user-avatar :user="$friendUser" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink group-hover:text-sky truncate">
                                            {{ $friendUser->nama_lengkap ?: $friendUser->username }}
                                        </p>
                                        <p class="text-[10px] text-muted truncate">
                                            {{ $friendUser->specialty_role ?: ('@' . $friendUser->username) }}
                                        </p>
                                    </div>
                                </a>

                                @auth
                                    @if (Auth::id() !== $friendUser->id)
                                        <button
                                            type="button"
                                            @click="friendsModalOpen = false; window.dispatchEvent(new CustomEvent('open-chat-with', { detail: { userId: {{ (int) $friendUser->id }} } }))"
                                            class="shrink-0 rounded-full bg-[#1E63E9] hover:bg-ink text-white text-[11px] font-bold px-3.5 py-1.5 transition-colors cursor-pointer"
                                        >
                                            Chat
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- modal pop-up daftar Followers & Following -->
        <div
            x-show="followModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.52);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="followModalOpen = false"
        >
            <div
                @click.outside="followModalOpen = false"
                class="w-full max-w-md max-h-[82vh] rounded-[24px] bg-white border border-gray-200/80 shadow-neutral flex flex-col overflow-hidden"
            >
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-ink">
                            {{ $profileUser->nama_lengkap ?: $profileUser->username }}
                        </h3>
                        <p class="text-[11px] text-muted">
                            &#64;{{ $profileUser->username }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="followModalOpen = false"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Tab Pengikut / Mengikuti -->
                <div class="px-4 py-2.5 bg-[#F8FAFC] border-b border-gray-100 grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        @click="followModalTab = 'followers'"
                        :class="followModalTab === 'followers' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                        class="rounded-xl border py-2 text-xs font-heading font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                    >
                        <span>{{ app()->getLocale() === 'en' ? 'Followers' : 'Pengikut' }}</span>
                        <span class="rounded-full bg-sky/15 text-sky px-2 py-0.5 text-[10px]" x-text="followersCount"></span>
                    </button>
                    <button
                        type="button"
                        @click="followModalTab = 'following'"
                        :class="followModalTab === 'following' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                        class="rounded-xl border py-2 text-xs font-heading font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                    >
                        <span>{{ app()->getLocale() === 'en' ? 'Following' : 'Mengikuti' }}</span>
                        <span class="rounded-full bg-gray-200/80 text-ink px-2 py-0.5 text-[10px]" x-text="followingCount"></span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 bg-[#F8FAFC]">
                    <template x-if="followListLoading">
                        <div class="py-10 text-center text-xs text-muted">Memuat daftar...</div>
                    </template>

                    <template x-if="!followListLoading && (followModalTab === 'followers' ? followList.followers : followList.following).length === 0">
                        <div class="py-10 text-center text-xs text-muted" x-text="followModalTab === 'followers' ? @js(app()->getLocale() === 'en' ? 'No followers yet.' : 'Belum ada pengikut.') : @js(app()->getLocale() === 'en' ? 'Not following anyone yet.' : 'Belum mengikuti siapa pun.')"></div>
                    </template>

                    <template x-for="u in (followModalTab === 'followers' ? followList.followers : followList.following)" :key="u.id">
                        <div class="rounded-2xl bg-white border border-gray-100 p-3 flex items-center justify-between gap-3 shadow-2xs">
                            <a :href="u.profile_url" class="flex items-center gap-3 min-w-0 group">
                                <span class="relative inline-flex w-9 h-9 shrink-0">
                                    <template x-if="u.avatar_url">
                                        <img :src="u.avatar_url" :alt="u.nama" class="w-full h-full rounded-full object-cover">
                                    </template>
                                    <template x-if="!u.avatar_url">
                                        <span :class="u.pastel_class" class="w-full h-full rounded-full font-heading font-bold text-xs flex items-center justify-center" x-text="u.inisial"></span>
                                    </template>
                                    <template x-if="u.avatar_border_url">
                                        <img :src="u.avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                    </template>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-heading font-bold text-xs text-ink group-hover:text-sky truncate" x-text="u.nama"></p>
                                    <p class="text-[10px] text-muted truncate" x-text="u.specialty_role || ('@' + u.username)"></p>
                                </div>
                            </a>

                            @auth
                                <template x-if="!u.is_me">
                                    <button
                                        type="button"
                                        @click="toggleFollowItem(u)"
                                        :disabled="u._busy"
                                        :class="u.is_following ? 'bg-ink text-lime' : 'bg-sky/10 hover:bg-sky text-sky hover:text-white'"
                                        class="shrink-0 rounded-full text-[11px] font-heading font-bold px-3.5 py-1.5 transition-colors cursor-pointer"
                                        x-text="u.is_following ? @js(app()->getLocale() === 'en' ? 'Following' : 'Mengikuti') : @js(app()->getLocale() === 'en' ? 'Follow' : 'Ikuti')"
                                    ></button>
                                </template>
                            @endauth
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </section>

@endsection
