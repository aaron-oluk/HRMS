<x-layouts.app title="My Warnings" header="My Warnings">
    @php
        $severityColor = fn (string $severity) => match ($severity) {
            'verbal' => 'warning',
            'written', 'final' => 'danger',
            default => 'neutral',
        };
    @endphp

    @if ($warnings->isEmpty())
        <x-card>
            <p class="text-sm text-slate-500">You have no warnings on record.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($warnings as $warning)
                <x-card>
                    <div class="flex items-start justify-between gap-x-2">
                        <div class="flex items-center gap-x-2">
                            <x-badge :color="$severityColor($warning->severity)">{{ ucfirst($warning->severity) }}</x-badge>
                            @if ($warning->acknowledged_at)
                                <span class="text-xs text-emerald-600">Acknowledged {{ $warning->acknowledged_at->toFormattedDateString() }}</span>
                            @else
                                <span class="text-xs text-amber-600">Not yet acknowledged</span>
                            @endif
                        </div>
                        @if (! $warning->acknowledged_at)
                            <form method="POST" action="{{ route('employees.warnings.acknowledge', [$warning->employee_id, $warning]) }}">
                                @csrf
                                <x-button type="submit" variant="secondary">Acknowledge</x-button>
                            </form>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $warning->reason }}</p>
                    <p class="mt-2 text-xs text-slate-400">
                        Issued {{ $warning->issued_at->toFormattedDateString() }}
                        @if ($warning->issuer) by {{ $warning->issuer->name }} @endif
                        @if ($warning->expires_at) &middot; Expires {{ $warning->expires_at->toFormattedDateString() }} @endif
                    </p>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
