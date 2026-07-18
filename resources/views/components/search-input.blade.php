@props(['disabled' => false])

<div class="relative">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <i class="bx bx-search text-base leading-none text-slate-400"></i>
    </span>
    <input
        type="search"
        autocomplete="off"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30']) }}
    >
</div>
