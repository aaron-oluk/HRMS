<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Date</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Hours</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Reason</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($overtimeApprovals as $request)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $request->employee->fullName() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->date->toFormattedDateString() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->hours }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->reason ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-x-2">
                            <form method="POST" action="{{ route('attendance.overtime.approve', $request) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-x-1 rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                    <i class="bx bx-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('attendance.overtime.reject', $request) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-x-1 rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                    <i class="bx bx-x"></i> Deny
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">No pending overtime requests.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
