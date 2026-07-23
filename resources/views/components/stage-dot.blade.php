@props(['stage'])

@php
    $class = match ($stage) {
        'advertising' => 'bg-slate-400',
        'review' => 'bg-blue-500',
        'shortlisting' => 'bg-indigo-500',
        'interviews' => 'bg-violet-500',
        'negotiations_and_offers' => 'bg-amber-500',
        'contracts_and_appointments' => 'bg-emerald-500',
        'probation' => 'bg-teal-500',
        'rejected' => 'bg-red-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "h-2 w-2 shrink-0 rounded-full {$class}"]) }}></span>
