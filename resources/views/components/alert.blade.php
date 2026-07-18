@props(['type' => 'success'])

@php
    $styles = [
        'success' => ['classes' => 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-600/20', 'icon' => 'bxs-check-circle text-emerald-500'],
        'error' => ['classes' => 'bg-red-50 text-red-800 ring-1 ring-inset ring-red-600/20', 'icon' => 'bxs-x-circle text-red-500'],
        'info' => ['classes' => 'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-600/20', 'icon' => 'bxs-info-circle text-blue-500'],
    ];
    $style = $styles[$type];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-x-2.5 rounded-lg px-4 py-3 text-sm '.$style['classes']]) }}>
    <i class="bx {{ $style['icon'] }} mt-0.5 shrink-0 text-base"></i>
    <div>{{ $slot }}</div>
</div>
