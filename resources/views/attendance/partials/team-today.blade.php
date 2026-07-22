<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Clock in</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Clock out</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($teamToday as $day)
                <tr class="cursor-pointer hover:bg-slate-50" onclick="window.location='{{ route('employees.show', $day->employee) }}'">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $day->employee->fullName() }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusColor = match ($day->status) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'on_leave' => 'info',
                                default => 'neutral',
                            };
                        @endphp
                        <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $day->status)) }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $day->clock_in_at?->format('g:i A') ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $day->clock_out_at?->format('g:i A') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">No attendance recorded today.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
