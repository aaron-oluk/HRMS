<x-card class="!p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                            $statusColors = [
                                'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                'cancelled' => 'bg-slate-50 text-slate-600 ring-slate-500/20',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusColors[$request->status] }}">
                            {{ ucfirst($request->status) }}
                        </span>
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
