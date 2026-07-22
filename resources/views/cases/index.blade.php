<x-layouts.app title="Cases" :header="$canManage ? 'HR cases' : 'My cases'">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('cases.create') }}"><x-button icon="bx-plus">New case</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    @if ($canManage)
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                    @endif
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Subject</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Category</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cases as $case)
                    @php($statusColor = match ($case->status) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved', 'closed' => 'success',
                        default => 'neutral',
                    })
                    <tr>
                        @if ($canManage)
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a href="{{ route('employees.show', $case->employee) }}" class="hover:text-emerald-600">{{ $case->employee->fullName() }}</a>
                            </td>
                        @endif
                        <td class="px-4 py-3 {{ $canManage ? 'text-slate-500' : 'font-medium text-slate-900' }}">{{ $case->subject }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($case->category) }}</td>
                        <td class="px-4 py-3"><x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cases.show', $case) }}" class="text-emerald-600 hover:text-emerald-500">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 5 : 4 }}" class="px-4 py-6 text-center text-slate-500">No cases yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $cases->links() }}</div>
</x-layouts.app>
