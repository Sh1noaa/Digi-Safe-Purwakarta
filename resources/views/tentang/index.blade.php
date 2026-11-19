@extends('layouts.app')

@section('title', 'Tentang Kami - Digi-Safe Purwakarta')

@section('content')

    <!-- hero section bergaya dokumen editor & floating command bar -->
    <section
        class="about-hero-surface relative pt-28 sm:pt-36 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden"
    >
        <!-- aksen organik lembut di sudut seperti referensi -->
        <div class="about-hero-orb-1 absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#A5D6A7]/35 blur-3xl pointer-events-none"></div>
        <div class="about-hero-orb-2 absolute -bottom-28 -right-20 w-[420px] h-[420px] rounded-full bg-[#81C784]/40 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto">

            <!-- tab header nama file di kiri atas dokumen -->
            <div class="reveal inline-flex items-center gap-2.5 rounded-t-[20px] bg-white px-6 pt-4 pb-2.5 border-t border-x border-gray-200/80">
                <span class="w-6 h-6 rounded-md bg-sky/15 text-sky flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                </span>
                <span class="font-heading font-bold text-xs sm:text-sm text-ink tracking-tight">
                    {{ __('ui.about.doc_title') }}
                </span>
            </div>

            <!-- container putih dokumen editor -->
            <div
                class="reveal rounded-b-[24px] rounded-tr-[24px] bg-white p-6 sm:p-9 pb-14 border border-gray-200/80 shadow-neutral"
            >
                <!-- toolbar dekoratif editor -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-6 border-b border-gray-100 text-xs text-ink/75 select-none">
                    <div class="flex items-center gap-3">
                        <!-- undo & redo -->
                        <span class="p-1 text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7v6h6" />
                                <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13" />
                            </svg>
                        </span>
                        <span class="p-1 text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 7v6h-6" />
                                <path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7" />
                            </svg>
                        </span>

                        <span class="h-4 w-px bg-gray-200 mx-0.5"></span>

                        <!-- dropdown font & ukuran -->
                        <span class="inline-flex items-center gap-1 font-medium text-ink">
                            <span>Arial</span>
                            <svg class="w-3 h-3 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>

                        <span class="inline-flex items-center gap-1 font-medium text-ink ml-1">
                            <span>14 pt</span>
                            <svg class="w-3 h-3 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <!-- align, bold, italic, underline -->
                        <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="15" y2="12" />
                            <line x1="3" y1="18" x2="18" y2="18" />
                        </svg>
                        <span class="font-bold text-ink">B</span>
                        <span class="italic font-serif text-ink">I</span>
                        <span class="underline text-ink">U</span>

                        <span class="h-4 w-px bg-gray-200 mx-0.5"></span>

                        <span class="inline-flex items-center gap-1.5 font-medium text-ink">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                            </svg>
                            <span>Edit</span>
                        </span>
                    </div>
                </div>

                <!-- isi dokumen -->
                <div>
                    <h1 class="font-heading font-bold text-lg sm:text-xl text-ink mb-4">
                        {{ __('ui.about.heading') }}
                    </h1>

                    <p class="text-sm sm:text-[15px] text-ink/75 leading-[1.9] tracking-[-0.01em]">
                        {!! __('ui.about.body_html') !!}
                    </p>
                </div>
            </div>

            <!-- floating command bar glassmorphism di bawah dokumen -->
            <div
                class="about-command-bar reveal reveal-delay-1 -mt-8 relative z-20 max-w-md mx-auto rounded-[28px] p-5 border border-white"
            >
                <div class="flex items-center text-sm sm:text-base font-medium text-ink px-1">
                    <span>{{ __('ui.about.prompt') }}</span>
                    <span class="inline-block w-[2px] h-5 bg-sky ml-1 animate-pulse"></span>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-9 h-9 rounded-full bg-white text-ink flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                        </span>
                        <span class="w-9 h-9 rounded-full bg-white text-ink flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                        </span>
                    </div>

                    <!-- tombol bulat hitam panah ke bawah -->
                    <a
                        href="#section-pengembang"
                        class="w-10 h-10 rounded-full bg-ink hover:bg-sky text-white flex items-center justify-center shadow-sm hover:scale-105 transition-all"
                        aria-label="Scroll ke Tentang Pengembang"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <polyline points="19 12 12 19 5 12" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- section pengembang -->
    <section id="section-pengembang" class="py-20 sm:py-28 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- kolom kiri: foto bulat pengembang & identitas visual -->
                <div class="reveal lg:col-span-5">
                    <div class="relative rounded-[26px] bg-pastel-green border border-[#DCEEDC] p-8 flex flex-col items-center text-center shadow-neutral">
                        <div class="w-28 h-28 rounded-full overflow-hidden ring-8 ring-white shadow-md bg-white shrink-0">
                            <img
                                src="{{ asset('images/developer-razief.jpg') }}"
                                alt="{{ $developer['nama'] }}"
                                class="w-full h-full object-cover object-top"
                            >
                        </div>

                        <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-1 text-[11px] font-bold text-ink shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-sky"></span>
                            <span>Creator & Lead Developer</span>
                        </span>

                        <p class="mt-3 font-heading font-bold text-base text-ink">
                            {{ $developer['nama'] }}
                        </p>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $developer['asal'] }}
                        </p>

                        <div class="mt-6 pt-5 border-t border-ink/10 w-full flex flex-wrap items-center justify-center gap-2 text-[10px] font-bold text-ink/75">
                            <span class="rounded-full bg-white px-3 py-1">Laravel 11</span>
                            <span class="rounded-full bg-white px-3 py-1">Tailwind CSS</span>
                            <span class="rounded-full bg-white px-3 py-1">Alpine.js</span>
                            <span class="rounded-full bg-white px-3 py-1">Cyber Security</span>
                        </div>
                    </div>
                </div>

                <!-- kolom kanan: informasi pengembang & social media links -->
                <div class="reveal reveal-delay-1 lg:col-span-7 space-y-5">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                        <span class="text-sky">&bull;</span> {{ __('ui.about.creator_badge') }}
                    </span>

                    <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-ink tracking-tight">
                        {{ __('ui.about.dev_heading') }}
                    </h2>

                    <div class="space-y-1">
                        <h3 class="font-heading font-bold text-lg sm:text-xl text-sky">
                            {{ $developer['nama'] }}
                        </h3>
                        <p class="text-xs sm:text-sm font-semibold text-ink/80">
                            {{ $developer['asal'] }}
                        </p>
                    </div>

                    <p class="text-xs sm:text-sm text-muted leading-relaxed">
                        {{ $developer['deskripsi'] }}
                    </p>

                    <!-- tautan sosial media (dibuka di tab baru) -->
                    <div class="pt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ($developer['socials'] as $soc)
                            <a
                                href="{{ $soc['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-2xl border border-gray-200 hover:border-ink bg-white p-3.5 flex items-center gap-3 hover:-translate-y-0.5 transition-all group"
                            >
                                <span class="w-9 h-9 rounded-xl bg-pastel-green text-ink group-hover:bg-ink group-hover:text-lime flex items-center justify-center shrink-0 transition-colors">
                                    @if ($soc['type'] === 'instagram')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                                        </svg>
                                    @elseif ($soc['type'] === 'tiktok')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <line x1="3" y1="12" x2="21" y2="12" />
                                            <path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18" />
                                        </svg>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-medium text-muted block">{{ $soc['label'] }}</span>
                                    <span class="font-heading font-bold text-xs text-ink truncate block">{{ $soc['handle'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- section lokasi (embed maps) -->
    <section class="py-16 sm:py-24 bg-[#F9FBF9] border-y border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                        <span class="text-sky">&bull;</span> {{ __('ui.about.location_kicker') }}
                    </span>
                    <h2 class="mt-2 font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                        {{ __('ui.about.location_title') }}
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-muted">
                        {{ __('ui.about.location_desc') }}
                    </p>
                </div>

                <a
                    href="https://maps.app.goo.gl/yXAFWWacptxSEoU36"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 hover:border-ink px-4 py-2.5 text-xs font-bold text-ink shadow-2xs transition-colors self-start sm:self-auto"
                >
                    <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span>{{ __('ui.about.open_gmaps') }}</span>
                </a>
            </div>

            <!-- embed google maps -->
            <div class="reveal rounded-[20px] overflow-hidden border border-gray-200 bg-white shadow-neutral">
                <iframe
                    src="https://maps.google.com/maps?q=SMKN%202%20Purwakarta&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Peta Lokasi SMK Negeri 2 Purwakarta"
                    class="w-full h-[340px] sm:h-[400px] block"
                ></iframe>
            </div>

        </div>
    </section>

    <!-- section visi & misi / pilar perjalanan -->
    <section class="py-20 sm:py-28 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal text-center max-w-xl mx-auto mb-12">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                    <span class="text-sky">&bull;</span> {{ __('ui.about.vision_badge') }}
                </span>
                <h2 class="mt-2 font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                    {{ __('ui.about.vision_heading') }}
                </h2>
                <p class="mt-2 text-xs sm:text-sm text-muted">
                    {{ __('ui.about.vision_sub') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($visiMisi as $idx => $pilar)
                    <div class="reveal rounded-[22px] bg-white border border-gray-100 p-6 sm:p-7 flex flex-col justify-between shadow-2xs hover:-translate-y-1 hover:shadow-neutral transition-all duration-300">
                        <div>
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-pastel-green text-[#1B5E20] font-heading font-extrabold text-xs">
                                {{ $pilar['tahap'] }}
                            </span>
                            <h3 class="mt-5 font-heading font-bold text-base sm:text-lg text-ink">
                                {{ $pilar['judul'] }}
                            </h3>
                            <p class="mt-2 text-xs text-muted leading-relaxed">
                                {{ $pilar['deskripsi'] }}
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-[11px] font-semibold text-ink/70">
                            <span>{{ __('ui.about.vision_badge_footer') }}</span>
                            <span class="w-2 h-2 rounded-full bg-lime"></span>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

@endsection
