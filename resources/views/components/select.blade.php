@props(['disabled' => false])

<div class="relative">
    <select
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'block w-full appearance-none rounded-sm border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 disabled:hover:border-slate-300']) }}
    >
        {{ $slot }}
    </select>
    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <i class="bx bx-chevron-down text-base leading-none text-slate-400"></i>
    </span>
</div>
