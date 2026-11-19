@props([
    'user' => null,
    'src' => null,
    'name' => null,
    'border' => null,
    'size' => 'md',
    'class' => '',
])

@php
    $resolvedName = $name
        ?: ($user->nama_lengkap ?? $user->username ?? 'Warga Purwakarta');

    $resolvedInitial = mb_strtoupper(mb_substr(trim((string) $resolvedName), 0, 1)) ?: 'U';

    $resolvedSrc = $src;
    if (! $resolvedSrc && $user) {
        if ($user instanceof \App\Models\User) {
            $resolvedSrc = $user->avatar_url;
        } elseif (! empty($user->avatar)) {
            $av = ltrim((string) $user->avatar, '/');
            if (str_starts_with($av, 'http://') || str_starts_with($av, 'https://')) {
                $resolvedSrc = $av;
            } elseif (file_exists(public_path($av))) {
                $resolvedSrc = asset($av);
            } elseif (file_exists(public_path('storage/' . $av))) {
                $resolvedSrc = asset('storage/' . $av);
            }
        }
    }

    $resolvedBorderUrl = null;
    if (is_string($border) && $border !== '') {
        $resolvedBorderUrl = str_starts_with($border, 'http://') || str_starts_with($border, 'https://')
            ? $border
            : \App\Services\AvatarBorderService::imageUrl($border);
    } elseif ($user) {
        if ($user instanceof \App\Models\User) {
            $resolvedBorderUrl = $user->avatar_border_url;
        } elseif (! empty($user->avatar_border_url)) {
            $resolvedBorderUrl = $user->avatar_border_url;
        } elseif (! empty($user->avatar_border)) {
            $resolvedBorderUrl = \App\Services\AvatarBorderService::imageUrl((string) $user->avatar_border);
        }
    }

    $palettes = [
        'bg-[#DBEAFE] text-[#1E40AF]',
        'bg-[#D1FAE5] text-[#065F46]',
        'bg-[#FEF3C7] text-[#92400E]',
        'bg-[#FCE7F3] text-[#9D174D]',
        'bg-[#E0F2FE] text-[#0369A1]',
        'bg-[#ECFCCB] text-[#365314]',
    ];
    $seed = abs(crc32((string) $resolvedName));
    $pastelClass = ($user instanceof \App\Models\User)
        ? $user->pastel_class
        : $palettes[$seed % count($palettes)];

    $sizeClasses = match ($size) {
        'xs' => 'w-7 h-7 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-11 h-11 text-sm',
        'xl' => 'w-20 h-20 text-2xl',
        'profile' => 'w-24 h-24 sm:w-28 sm:h-28 text-3xl',
        default => 'w-9 h-9 text-xs',
    };
@endphp

@if ($resolvedBorderUrl)
    <span class="relative inline-flex items-center justify-center shrink-0 {{ $sizeClasses }}">
        @if ($resolvedSrc)
            <img
                src="{{ $resolvedSrc }}"
                alt="{{ $resolvedName }}"
                {{ $attributes->merge(['class' => "w-full h-full rounded-full object-cover {$class}"]) }}
            >
        @else
            <span
                {{ $attributes->merge(['class' => "w-full h-full {$pastelClass} rounded-full font-heading font-extrabold flex items-center justify-center select-none {$class}"]) }}
            >
                {{ $resolvedInitial }}
            </span>
        @endif
        <img
            src="{{ $resolvedBorderUrl }}"
            alt=""
            aria-hidden="true"
            class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10"
        >
    </span>
@else
    @if ($resolvedSrc)
        <img
            src="{{ $resolvedSrc }}"
            alt="{{ $resolvedName }}"
            {{ $attributes->merge(['class' => "{$sizeClasses} rounded-full object-cover shrink-0 {$class}"]) }}
        >
    @else
        <span
            {{ $attributes->merge(['class' => "{$sizeClasses} {$pastelClass} rounded-full font-heading font-extrabold flex items-center justify-center shrink-0 select-none {$class}"]) }}
        >
            {{ $resolvedInitial }}
        </span>
    @endif
@endif
