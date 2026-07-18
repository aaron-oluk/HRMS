<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                            $statusColors = [
                                'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusColors[$request->status] }}">
                            {{ ucfirst($request->status) }}
                        </span>
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
