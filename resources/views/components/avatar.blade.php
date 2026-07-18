@props(['name', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
    ];

    $initials = str($name ?? '')->explode(' ')->filter()->map(fn ($part) => str($part)->substr(0, 1))->take(2)->implode('');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700 '.$sizes[$size]]) }}>
    {{ $initials }}
</span>
