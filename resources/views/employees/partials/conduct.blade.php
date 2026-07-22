@php
    $severityColor = fn (string $severity) => match ($severity) {
        'verbal' => 'warning',
        'written' => 'danger',
        'final' => 'danger',
        default => 'neutral',
    };
@endphp

<x-card x-data="{ addingWarning: false }">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Warnings</h3>
        @can('employees.manage-warnings')
            <button type="button" @click="addingWarning = ! addingWarning" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                <span x-show="! addingWarning">+ Issue warning</span>
                <span x-show="addingWarning" x-cloak>Cancel</span>
            </button>
        @endcan
    </div>

    @can('employees.manage-warnings')
        <form x-show="addingWarning" x-cloak method="POST" action="{{ route('employees.warnings.store', $employee) }}" class="mb-4 grid grid-cols-1 gap-3 rounded-md border border-slate-100 p-3 sm:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Severity</label>
                <x-select name="severity" required>
                    @foreach (\App\Models\EmployeeWarning::SEVERITIES as $severity)
                        <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Issued on</label>
                <x-input type="date" name="issued_at" value="{{ now()->toDateString() }}" required />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">Reason</label>
                <textarea name="reason" rows="3" required placeholder="Describe the incident..." class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Expires on (optional)</label>
                <x-input type="date" name="expires_at" />
            </div>
            <div class="sm:col-span-2">
                <x-button type="submit">Save warning</x-button>
            </div>
        </form>
    @endcan

    @if ($employee->warnings->isEmpty())
        <p class="text-sm text-slate-500">No warnings on record.</p>
    @else
        <div class="space-y-3">
            @foreach ($employee->warnings as $warning)
                <div class="rounded-md border border-slate-100 p-3">
                    <div class="flex items-start justify-between gap-x-2">
                        <div class="flex items-center gap-x-2">
                            <x-badge :color="$severityColor($warning->severity)">{{ ucfirst($warning->severity) }}</x-badge>
                            @if ($warning->acknowledged_at)
                                <span class="text-xs text-emerald-600">Acknowledged {{ $warning->acknowledged_at->toFormattedDateString() }}</span>
                            @else
                                <span class="text-xs text-amber-600">Not yet acknowledged</span>
                            @endif
                        </div>
                        @can('employees.manage-warnings')
                            <form method="POST" action="{{ route('employees.warnings.destroy', [$employee, $warning]) }}" onsubmit="return confirm('Remove this warning?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                            </form>
                        @endcan
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $warning->reason }}</p>
                    <p class="mt-2 text-xs text-slate-400">
                        Issued {{ $warning->issued_at->toFormattedDateString() }}
                        @if ($warning->issuer) by {{ $warning->issuer->name }} @endif
                        @if ($warning->expires_at) &middot; Expires {{ $warning->expires_at->toFormattedDateString() }} @endif
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</x-card>
