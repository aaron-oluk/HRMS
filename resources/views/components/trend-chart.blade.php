@props(['data' => [], 'height' => 140])

@php
    $count = count($data);
    $points = collect($data)->values()->map(fn ($point, $index) => [
        'label' => $point['label'],
        'score' => (float) $point['score'],
        'x' => $count > 1 ? ($index / ($count - 1)) * 100 : 50,
        'y' => 90 - ((float) $point['score'] / 100) * 80,
    ]);
    $polyline = $points->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
    $areaPath = $points->isNotEmpty()
        ? 'M'.$points->first()['x'].',100 L'.$polyline.' L'.$points->last()['x'].',100 Z'
        : '';
    $lastIndex = $count - 1;
    $formatScore = fn (float $score) => rtrim(rtrim(number_format($score, 1), '0'), '.');
@endphp

<div {{ $attributes }}>
    @if ($points->isEmpty())
        <div class="flex items-center justify-center text-sm text-slate-400" style="height: {{ $height }}px">
            No completed review cycles yet.
        </div>
    @elseif ($count === 1)
        {{-- A single point isn't a trend yet — a stat readout, not a one-dot line chart. --}}
        <div class="flex items-center gap-x-3 rounded-lg bg-slate-50 px-4 py-3">
            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-600"></span>
            <div>
                <p class="text-sm font-medium text-slate-900">{{ $points->first()['label'] }} &middot; {{ $formatScore($points->first()['score']) }}%</p>
                <p class="text-xs text-slate-400">Trend appears after your next completed review.</p>
            </div>
        </div>
    @else
        <div
            x-data="{
                hover: null,
                points: @js($points->all()),
                onMove(event) {
                    const rect = $refs.plot.getBoundingClientRect();
                    const pct = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width)) * 100;
                    let nearest = 0, nearestDist = Infinity;
                    this.points.forEach((p, i) => {
                        const dist = Math.abs(p.x - pct);
                        if (dist < nearestDist) { nearestDist = dist; nearest = i; }
                    });
                    this.hover = nearest;
                },
            }"
            class="flex"
            style="height: {{ $height }}px"
        >
            {{-- Y-axis --}}
            <div class="relative w-8 shrink-0 text-right text-[10px] text-slate-400">
                <span class="absolute right-2 -translate-y-1/2" style="top: 10%">100</span>
                <span class="absolute right-2 -translate-y-1/2" style="top: 50%">50</span>
                <span class="absolute right-2 -translate-y-1/2" style="top: 90%">0</span>
            </div>

            <div class="relative flex-1" x-ref="plot" @mousemove="onMove($event)" @mouseleave="hover = null">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                    <line x1="0" y1="10" x2="100" y2="10" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />
                    <line x1="0" y1="50" x2="100" y2="50" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />
                    <line x1="0" y1="90" x2="100" y2="90" stroke="currentColor" stroke-width="0.5" class="text-slate-100" vector-effect="non-scaling-stroke" />

                    <path d="{{ $areaPath }}" fill="currentColor" class="text-emerald-50" />
                    <polyline points="{{ $polyline }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" class="text-emerald-600" stroke-linejoin="round" stroke-linecap="round" />

                    {{-- Crosshair: a hairline that snaps to the nearest point on hover. --}}
                    <template x-if="hover !== null">
                        <line :x1="points[hover].x" :x2="points[hover].x" y1="4" y2="96" stroke="currentColor" stroke-width="0.5" class="text-slate-300" vector-effect="non-scaling-stroke" />
                    </template>
                </svg>

                {{-- Markers are HTML, not SVG circles — an SVG circle would stretch into an
                     ellipse under this responsive viewBox's non-uniform x/y scaling. --}}
                @foreach ($points as $index => $p)
                    <span
                        class="pointer-events-none absolute -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-sm transition-transform {{ $index === $lastIndex ? 'h-2.5 w-2.5 bg-emerald-600' : 'h-2 w-2 bg-white ring-1 ring-emerald-600' }}"
                        style="left: {{ $p['x'] }}%; top: {{ $p['y'] }}%"
                        :class="hover === {{ $index }} ? 'scale-125' : ''"
                    ></span>
                @endforeach

                {{-- Endpoint label: the one point worth always labeling — where you are now. --}}
                @php($last = $points->get($lastIndex))
                <div
                    class="pointer-events-none absolute -translate-y-full text-right"
                    style="right: 0; top: {{ $last['y'] }}%; margin-top: -8px;"
                    x-show="hover !== {{ $lastIndex }}"
                >
                    <p class="text-xs font-medium text-slate-900">{{ $formatScore($last['score']) }}%</p>
                </div>

                {{-- Hover tooltip: every other point's value lives here, not on the chart permanently. --}}
                <template x-for="(p, i) in points" :key="i">
                    <div
                        x-show="hover === i"
                        x-cloak
                        class="pointer-events-none absolute -translate-x-1/2 -translate-y-full text-nowrap rounded-md border border-slate-200 bg-white px-2 py-1 text-center text-xs shadow-sm"
                        :style="`left: ${p.x}%; top: ${p.y}%; margin-top: -10px;`"
                    >
                        <p class="font-medium text-slate-900" x-text="p.label"></p>
                        <p class="text-emerald-600" x-text="(Math.round(p.score * 10) / 10) + '%'"></p>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-2 flex justify-between pl-8 text-[11px] text-slate-400">
            @foreach ($points as $p)
                <span>{{ $p['label'] }}</span>
            @endforeach
        </div>
    @endif
</div>
