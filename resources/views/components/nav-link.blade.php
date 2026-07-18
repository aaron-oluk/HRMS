@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}" {{ $attributes->merge(['class' => ($active ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900').' group flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition']) }}>
    @if ($icon)
        <i class="bx {{ $icon }} text-lg {{ $active ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    @endif
    {{ $slot }}
</a>
