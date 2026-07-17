<x-layouts.app title="Grades" header="Grades">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('grades.create') }}"><x-button>Add grade</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Level</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Salary range</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($grades as $grade)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $grade->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $grade->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $grade->level }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ number_format($grade->min_salary) }} – {{ number_format($grade->max_salary) }} {{ $grade->currency }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($grade->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('grades.edit', $grade) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No grades yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $grades->links() }}</div>
</x-layouts.app>
