@props(['variant' => 'primary', 'icon' => null])

@php
    $variants = [
        'primary' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-500 focus-visible:outline-emerald-600',
        'secondary' => 'bg-white text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 hover:text-slate-900',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-500 focus-visible:outline-red-600',
    ];
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-x-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 '.$variants[$variant]]) }}>
    @if ($icon)
        <i class="bx {{ $icon }} text-base"></i>
    @endif
    {{ $slot }}
</button>
