@extends('layouts.app')

@section('title', 'Direktori Warga Siber Terdaftar — Digi-Safe Purwakarta')

@section('content')

    <section
        x-data="{ search: '', filterRole: 'semua' }"
        class="min-h-screen bg-[#F4F6F9] pt-28 sm:pt-32 pb-24 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-7xl mx-auto">

            <!-- Header Direktori Pendaftar Digi-Safe -->
            <div class="reveal flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8 border-b border-gray-200/80">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3.5 py-1.5 shadow-2xs mb-3">
                        <span class="w-2 h-2 rounded-full bg-lime"></span>
                        <span class="text-[11px] font-heading font-extrabold uppercase tracking-wider text-ink">
                            {{ __('ui.directory.badge', ['count' => $users->count()]) }}
                        </span>
                    </div>

                    <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-[42px] text-ink leading-tight tracking-tight">
                        {{ __('ui.directory.title_1') }} <span class="text-sky">Digi-Safe Purwakarta</span>
                    </h1>
                    <p class="mt-2 text-xs sm:text-sm text-muted max-w-2xl leading-relaxed">
                        {{ __('ui.directory.sub') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('beranda') }}#komunitas-user"
                        class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 hover:border-ink px-5 py-2.5 text-xs font-heading font-bold text-ink transition-colors shadow-2xs"
                    >
                        <span>&larr; {{ __('ui.directory.back_home') }}</span>
                    </a>

                    @guest
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-lime hover:bg-sky text-ink hover:text-white px-5 py-2.5 text-xs font-heading font-extrabold transition-colors shadow-sm"
                        >
                            <span>{{ __('ui.directory.join_member') }}</span>
                        </a>
                    @endguest
                </div>
            </div>

            <!-- Bar Pencarian & Filter Peran -->
            <div class="reveal mt-7 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="{{ __('ui.directory.search_placeholder') }}"
                        class="w-full rounded-full bg-white border border-gray-200/90 pl-11 pr-5 py-3 text-xs text-ink placeholder:text-gray-400 shadow-2xs focus:outline-none focus:border-sky"
                    >
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="filterRole = 'semua'"
                        :class="filterRole === 'semua' ? 'bg-ink text-white' : 'bg-white text-muted hover:text-ink border border-gray-200'"
                        class="rounded-full px-4 py-2 text-xs font-heading font-bold transition-all cursor-pointer"
                    >
                        {{ __('ui.directory.all_users', ['count' => $users->count()]) }}
                    </button>
                    <button
                        type="button"
                        @click="filterRole = 'pengelola'"
                        :class="filterRole === 'pengelola' ? 'bg-sky text-white' : 'bg-white text-muted hover:text-ink border border-gray-200'"
                        class="rounded-full px-4 py-2 text-xs font-heading font-bold transition-all cursor-pointer"
                    >
                        {{ __('ui.directory.owners_admins') }}
                    </button>
                    <button
                        type="button"
                        @click="filterRole = 'warga'"
                        :class="filterRole === 'warga' ? 'bg-lime text-ink' : 'bg-white text-muted hover:text-ink border border-gray-200'"
                        class="rounded-full px-4 py-2 text-xs font-heading font-bold transition-all cursor-pointer"
                    >
                        {{ __('ui.directory.cyber_citizens') }}
                    </button>
                </div>
            </div>

            <!-- Grid Kartu Seluruh Pendaftar Digi-Safe (Desain Gambar ke-3 sebanyak jumlah pendaftar) -->
            <div class="mt-9 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-7">
                @foreach ($users as $u)
                    @php
                        $searchBlob = strtolower(($u->nama_lengkap ?: '') . ' ' . $u->username . ' ' . ($u->bio ?: '') . ' ' . $u->role);
                        $isPengelola = in_array($u->role, ['owner', 'admin'], true) ? 'true' : 'false';
                    @endphp
                    <div
                        x-show="(search === '' || @js($searchBlob).includes(search.toLowerCase())) && (filterRole === 'semua' || (filterRole === 'pengelola' && {{ $isPengelola }}) || (filterRole === 'warga' && !{{ $isPengelola }}))"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                    >
                        <x-user-profile-card :user="$u" :linkToDirectory="false" />
                    </div>
                @endforeach
            </div>

        </div>
    </section>

@endsection

