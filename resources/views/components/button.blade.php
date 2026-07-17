@props(['variant' => 'primary', 'icon' => null])

@php
    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
        'secondary' => 'bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
    ];
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-x-2 rounded-md px-3 py-2 text-sm font-semibold shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed '.$variants[$variant]]) }}>
    @if ($icon)
        <i class="bx {{ $icon }} text-base"></i>
    @endif
    {{ $slot }}
</button>
