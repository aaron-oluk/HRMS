<x-layouts.app title="Shifts" header="Shifts">
    <div class="mb-4 flex justify-end">
        @can('attendance.manage-shifts')
            <a href="{{ route('shifts.create') }}"><x-button icon="bx-plus">Add shift</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Start</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">End</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Break (min)</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($shifts as $shift)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $shift->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $shift->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $shift->formattedStartTime() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $shift->formattedEndTime() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $shift->break_minutes }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($shift->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('attendance.manage-shifts')
                                <a href="{{ route('shifts.edit', $shift) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">No shifts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $shifts->links() }}</div>
</x-layouts.app>
