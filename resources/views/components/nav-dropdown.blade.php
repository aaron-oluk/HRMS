@props(['icon' => null, 'label', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button
        type="button"
        @click="open = !open"
        {{ $attributes->merge(['class' => ($active ? 'text-slate-900' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900').' group flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition']) }}
    >
        @if ($icon)
            <i class="bx {{ $icon }} text-lg {{ $active ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
        @endif
        <span class="flex-1 text-left">{{ $label }}</span>
        <i class="bx bx-chevron-down shrink-0 text-sm transition-transform duration-150" :class="{ 'rotate-180': open }"></i>
    </button>
    <div x-show="open" x-cloak class="mt-1 flex flex-col gap-y-1 pl-8">
        {{ $slot }}
    </div>
</div>
