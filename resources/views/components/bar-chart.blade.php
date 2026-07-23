@props(['data' => [], 'max' => null, 'compact' => false])

@php
    $ceiling = $max ?? max(1, collect($data)->max('value'));
    $peakValue = collect($data)->max('value');
    $barAreaClass = $compact ? 'h-12' : 'h-24';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-end gap-x-3']) }}>
    @foreach ($data as $point)
        @php($height = $ceiling > 0 ? max(4, ($point['value'] / $ceiling) * 100) : 4)
        @php($isPeak = $point['value'] > 0 && $point['value'] === $peakValue)
        @php($href = $point['href'] ?? null)
        @php($tag = $href ? 'a' : 'div')
        <{{ $tag }} @if ($href) href="{{ $href }}" @endif class="flex flex-1 flex-col items-center gap-y-1.5 {{ $href ? 'group' : '' }}">
            <div class="flex {{ $barAreaClass }} w-full items-end justify-center">
                <div
                    class="w-full max-w-6 rounded-t-sm transition-all {{ $isPeak ? 'bg-emerald-600' : ($href ? 'bg-emerald-100 group-hover:bg-emerald-300' : 'bg-emerald-100') }}"
                    style="height: {{ $height }}%"
                    title="{{ $point['label'] }}: {{ $point['value'] }}"
                ></div>
            </div>
            @unless ($compact)
                <span class="text-[11px] text-slate-400">{{ $point['label'] }}</span>
            @endunless
        </{{ $tag }}>
    @endforeach
</div>
