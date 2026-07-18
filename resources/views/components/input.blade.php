@props(['disabled' => false])

@php
    $classes = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 disabled:hover:border-slate-300';
@endphp

@if ($attributes->get('type') === 'password')
    <div x-data="{ show: false }" class="relative">
        <input
            @disabled($disabled)
            :type="show ? 'text' : 'password'"
            {{ $attributes->except('type')->merge(['class' => $classes.' pr-10']) }}
        >
        <button
            type="button"
            tabindex="-1"
            @click="show = !show"
            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 hover:text-slate-600"
            :aria-label="show ? 'Hide password' : 'Show password'"
        >
            <i class="bx text-base" :class="show ? 'bx-hide' : 'bx-show'"></i>
        </button>
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
@endif
