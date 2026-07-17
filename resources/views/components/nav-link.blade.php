@props(['href', 'active' => false])

<a href="{{ $href }}" {{ $attributes->merge(['class' => ($active ? 'bg-indigo-700 text-white' : 'text-indigo-100 hover:bg-indigo-700 hover:text-white').' group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium']) }}>
    {{ $slot }}
</a>
