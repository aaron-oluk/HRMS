@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-600/20',
        'error' => 'bg-red-50 text-red-800 ring-1 ring-inset ring-red-600/20',
        'info' => 'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-600/20',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md px-4 py-3 text-sm '.$styles[$type]]) }}>
    {{ $slot }}
</div>
