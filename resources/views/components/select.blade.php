@props(['disabled' => false])

<div class="relative">
    <select
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'block w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-900 shadow-sm transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 disabled:hover:border-slate-300']) }}
    >
        {{ $slot }}
    </select>
    <i class="bx bx-chevron-down pointer-events-none absolute inset-y-0 right-3 flex items-center text-base text-slate-400"></i>
</div>
