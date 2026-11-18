@extends('layouts.app')

@section('title', 'Leaderboard — Digi-Safe Purwakarta')

@section('content')

    <section class="min-h-screen bg-[#F4F6F9] pt-24 sm:pt-28 pb-24 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- aksen diagonal sudut kanan/kiri tipis ala studio frame -->
        <div aria-hidden="true" class="hidden lg:block pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-rose-400/15 blur-3xl"></div>
        <div aria-hidden="true" class="hidden lg:block pointer-events-none absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-sky/15 blur-3xl"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            <x-leaderboard-board :datasets="$leaderboardData" />
        </div>
    </section>

@endsection

