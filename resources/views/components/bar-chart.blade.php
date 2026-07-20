@props(['data' => [], 'max' => null])

@php
    $ceiling = $max ?? max(1, collect($data)->max('value'));
    $peakValue = collect($data)->max('value');
@endphp

<div {{ $attributes->merge(['class' => 'flex items-end gap-x-3']) }}>
    @foreach ($data as $point)
        @php($height = $ceiling > 0 ? max(4, ($point['value'] / $ceiling) * 100) : 4)
        @php($isPeak = $point['value'] > 0 && $point['value'] === $peakValue)
        <div class="flex flex-1 flex-col items-center gap-y-1.5">
            <div class="flex h-24 w-full items-end justify-center">
                <div
                    class="w-full max-w-6 rounded-t-sm transition-all {{ $isPeak ? 'bg-emerald-600' : 'bg-emerald-100' }}"
                    style="height: {{ $height }}%"
                    title="{{ $point['label'] }}: {{ $point['value'] }}"
                ></div>
            </div>
            <span class="text-[11px] text-slate-400">{{ $point['label'] }}</span>
        </div>
    @endforeach
</div>
