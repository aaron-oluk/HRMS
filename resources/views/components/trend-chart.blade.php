@props(['data' => [], 'height' => 140])

@php
    $count = count($data);
    $scores = collect($data)->pluck('score');
    $peakIndex = $scores->search($scores->max());

    // y is inverted (SVG origin top-left) and compressed to 10-90 within a 0-100 viewBox
    // so the peak marker/label never clips against the top or bottom edge.
    $pointFor = fn (int $index, float $score) => [
        'x' => $count > 1 ? ($index / ($count - 1)) * 100 : 50,
        'y' => 90 - ($score / 100) * 80,
    ];

    $points = collect($data)->values()->map(fn ($point, $index) => [
        ...$point,
        ...$pointFor($index, (float) $point['score']),
    ]);

    $polyline = $points->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
    $areaPath = $points->isNotEmpty()
        ? 'M'.$points->first()['x'].',100 L'.$polyline.' L'.$points->last()['x'].',100 Z'
        : '';
@endphp

<div {{ $attributes }}>
    @if ($points->isEmpty())
        <div class="flex items-center justify-center text-sm text-slate-400" style="height: {{ $height }}px">
            No completed review cycles yet.
        </div>
    @else
        <div class="relative" style="height: {{ $height }}px">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                <line x1="0" y1="25" x2="100" y2="25" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />
                <line x1="0" y1="50" x2="100" y2="50" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />
                <line x1="0" y1="75" x2="100" y2="75" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />

                <path d="{{ $areaPath }}" fill="currentColor" class="text-emerald-50" />
                <polyline points="{{ $polyline }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" class="text-emerald-600" stroke-linejoin="round" stroke-linecap="round" />

                @foreach ($points as $index => $p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $index === $peakIndex ? 2 : 1.4 }}" vector-effect="non-scaling-stroke" class="{{ $index === $peakIndex ? 'fill-emerald-600' : 'fill-white stroke-emerald-600' }}" stroke-width="1.2" />
                @endforeach
            </svg>

            @php($peak = $points->get($peakIndex))
            @if ($peak)
                <div
                    class="absolute -translate-x-1/2 -translate-y-full rounded-md border border-slate-200 bg-white px-2 py-1 text-center text-xs shadow-sm"
                    style="left: {{ $peak['x'] }}%; top: {{ $peak['y'] }}%; margin-top: -6px;"
                >
                    <p class="font-medium text-slate-900">{{ $peak['label'] }}</p>
                    <p class="text-emerald-600">{{ rtrim(rtrim(number_format($peak['score'], 1), '0'), '.') }}%</p>
                </div>
            @endif
        </div>

        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
            @foreach ($points as $p)
                <span>{{ $p['label'] }}</span>
            @endforeach
        </div>
    @endif
</div>
