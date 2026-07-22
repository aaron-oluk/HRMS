@props(['score' => null, 'label' => '', 'size' => 152, 'stroke' => 14])

@php
    $radius = ($size - $stroke) / 2;
    $circumference = 2 * M_PI * $radius;
    $offset = $score !== null ? $circumference * (1 - min(100, (float) $score) / 100) : $circumference;
@endphp

<div {{ $attributes->merge(['class' => 'relative shrink-0']) }} style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke-width="{{ $stroke }}" class="stroke-slate-100" />
        @if ($score !== null)
            <circle
                cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke-width="{{ $stroke }}"
                stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                class="stroke-emerald-600"
            />
        @endif
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-3xl font-bold text-slate-900">{{ $score !== null ? round($score) : '—' }}</span>
        @if ($label)
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</span>
        @endif
    </div>
</div>
