<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Date</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Hours</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Reason</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($myOvertimeRequests as $request)
                <tr>
                    <td class="px-4 py-3 text-slate-900">{{ $request->date->toFormattedDateString() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->hours }}</td>
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
                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">No overtime requests yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
