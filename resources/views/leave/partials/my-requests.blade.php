<x-card class="!p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Type</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Dates</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Days</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Reason</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($myRequests as $request)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $request->leaveType->name }}</td>
                    <td class="px-4 py-3 text-slate-500">
                        {{ $request->start_date->toFormattedDateString() }}
                        @if (! $request->start_date->equalTo($request->end_date))
                            &ndash; {{ $request->end_date->toFormattedDateString() }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->days }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->reason ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusColor = match ($request->status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'neutral',
                            };
                        @endphp
                        <x-badge :color="$statusColor">{{ ucfirst($request->status) }}</x-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">No time off requests yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-card>
