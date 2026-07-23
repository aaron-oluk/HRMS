@props(['self' => null, 'manager' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-y-3']) }}>
    <div>
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>Self rating</span>
            <span class="font-medium text-slate-700">{{ $self ?? '—' }}/5</span>
        </div>
        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-slate-400" style="width: {{ $self ? ($self / 5) * 100 : 0 }}%"></div>
        </div>
    </div>
    <div>
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>Manager rating</span>
            <span class="font-medium text-slate-700">{{ $manager ? "{$manager}/5" : 'Awaiting' }}</span>
        </div>
        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $manager ? ($manager / 5) * 100 : 0 }}%"></div>
        </div>
    </div>
</div>
