@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}" {{ $attributes->merge(['class' => ($active ? 'bg-indigo-700 text-white' : 'text-indigo-100 hover:bg-indigo-700 hover:text-white').' group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium']) }}>
    @if ($icon)
        <i class="bx {{ $icon }} text-lg {{ $active ? 'text-white' : 'text-indigo-300 group-hover:text-white' }}"></i>
    @endif
    {{ $slot }}
</a>
