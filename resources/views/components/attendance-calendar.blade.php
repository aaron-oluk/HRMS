@props(['month', 'data' => []])

@php
    $statusColors = [
        'present' => 'bg-emerald-500 text-white',
        'late' => 'bg-amber-400 text-white',
        'on_leave' => 'bg-blue-400 text-white',
        'absent' => 'bg-red-400 text-white',
        'holiday' => 'bg-slate-200 text-slate-500',
    ];

    $daysInMonth = $month->daysInMonth;
    $leadingBlanks = $month->copy()->startOfMonth()->dayOfWeek;
    $today = now();
@endphp

<div {{ $attributes }}>
    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-slate-400">
        @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $label)
            <span>{{ $label }}</span>
        @endforeach
    </div>

    <div class="mt-1.5 grid grid-cols-7 gap-1">
        @for ($i = 0; $i < $leadingBlanks; $i++)
            <span></span>
        @endfor

        @for ($day = 1; $day <= $daysInMonth; $day++)
            @php($isToday = $today->day === $day && $today->month === $month->month && $today->year === $month->year)
            <span
                class="flex h-7 w-7 items-center justify-center rounded-md text-xs {{ $statusColors[$data[$day] ?? ''] ?? 'text-slate-600' }} {{ $isToday && ! isset($data[$day]) ? 'ring-2 ring-emerald-600 ring-offset-1' : '' }}"
            >
                {{ $day }}
            </span>
        @endfor
    </div>

    <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
        <span class="flex items-center gap-x-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Present</span>
        <span class="flex items-center gap-x-1"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Late</span>
        <span class="flex items-center gap-x-1"><span class="h-2 w-2 rounded-full bg-blue-400"></span> On Leave</span>
        <span class="flex items-center gap-x-1"><span class="h-2 w-2 rounded-full bg-red-400"></span> Absent</span>
    </div>
</div>
