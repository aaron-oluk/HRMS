@props(['used' => 0, 'total' => 0, 'label' => '', 'unit' => '', 'size' => 96, 'stroke' => 8])

@php
    $percentage = $total > 0 ? min(100, ($used / $total) * 100) : 0;
    $radius = ($size - $stroke) / 2;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference * (1 - $percentage / 100);
    $format = fn (float $n) => rtrim(rtrim(number_format($n, 1), '0'), '.');
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-y-2']) }}>
    <div class="relative shrink-0" style="width: {{ $size }}px; height: {{ $size }}px;">
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
            <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke-width="{{ $stroke }}" class="stroke-emerald-50" />
            <circle
                cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke-width="{{ $stroke }}"
                stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                class="stroke-emerald-600"
            />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-sm font-semibold text-slate-900">{{ $format($used) }}<span class="text-xs font-normal text-slate-400">/{{ $format($total) }}</span></span>
            @if ($unit)
                <span class="text-[10px] text-slate-400">{{ $unit }}</span>
            @endif
        </div>
    </div>
    <span class="text-xs font-medium text-slate-500">{{ $label }}</span>
</div>
