@props(['data' => [], 'max' => null])

@php
    $ceiling = $max ?? max(1, collect($data)->max('value'));
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @foreach ($data as $row)
        @php($width = $ceiling > 0 ? max(2, ((float) $row['value'] / $ceiling) * 100) : 2)
        @php($href = $row['href'] ?? null)
        @php($tag = $href ? 'a' : 'div')
        <{{ $tag }} @if ($href) href="{{ $href }}" @endif class="block {{ $href ? 'group -mx-2 rounded-md px-2 py-1 transition hover:bg-slate-50' : '' }}">
            <div class="flex items-center justify-between gap-x-3 text-sm">
                <span class="text-slate-600 {{ $href ? 'group-hover:text-emerald-700' : '' }}">{{ $row['label'] }}</span>
                <span class="shrink-0 font-semibold text-slate-900">{{ $row['value'] }}</span>
            </div>
            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-600" style="width: {{ $width }}%"></div>
            </div>
        </{{ $tag }}>
    @endforeach
</div>
