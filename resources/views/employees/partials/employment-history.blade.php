<div class="mb-4 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-slate-900">At {{ $employee->entity->name }}</h3>
    @can('employments.manage')
        <a href="{{ route('employees.employments.create', $employee) }}">
            <x-button icon="bx-plus">Record employment change</x-button>
        </a>
    @endcan
</div>

<x-card class="!p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Effective from</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Effective to</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Position</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Department</th>
                @can('employees.view-salary')
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Salary</th>
                @endcan
                <th class="px-4 py-3 text-left font-medium text-slate-500">Reason</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($employee->employments as $employment)
                <tr>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->effective_from->toDateString() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->effective_to?->toDateString() ?? 'Current' }}</td>
                    <td class="px-4 py-3 text-slate-900">{{ $employment->position->title }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->department->name }}</td>
                    @can('employees.view-salary')
                        <td class="px-4 py-3 text-slate-500">{{ number_format($employment->basic_salary) }} {{ $employment->currency }}</td>
                    @endcan
                    <td class="px-4 py-3 text-slate-500">{{ ucfirst(str_replace('_', ' ', $employment->reason)) }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$employment->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($employment->status) }}</x-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">No employment records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-card>

<div class="mt-8" x-data="{ addingExperience: false }">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Prior experience</h3>
        @can('employments.manage')
            <button type="button" @click="addingExperience = ! addingExperience" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                <span x-show="! addingExperience">+ Add prior experience</span>
                <span x-show="addingExperience" x-cloak>Cancel</span>
            </button>
        @endcan
    </div>

    @can('employments.manage')
        <form x-show="addingExperience" x-cloak method="POST" action="{{ route('employees.work-experiences.store', $employee) }}" class="mb-4 grid grid-cols-1 gap-3 rounded-md border border-slate-100 p-3 sm:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Company</label>
                <x-input type="text" name="company_name" required />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Job title</label>
                <x-input type="text" name="job_title" required />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Start date</label>
                <x-input type="date" name="start_date" required />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">End date (optional)</label>
                <x-input type="date" name="end_date" />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">Description (optional)</label>
                <textarea name="description" rows="2" placeholder="Responsibilities, achievements..." class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"></textarea>
            </div>
            <div class="sm:col-span-2">
                <x-button type="submit">Save experience</x-button>
            </div>
        </form>
    @endcan

    @if ($employee->workExperiences->isEmpty())
        <p class="text-sm text-slate-500">No prior experience on record.</p>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($employee->workExperiences as $experience)
                <div class="rounded-md border border-slate-100 p-3">
                    <div class="flex items-start justify-between gap-x-2">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $experience->job_title }}</p>
                            <p class="text-xs text-slate-500">{{ $experience->company_name }}</p>
                        </div>
                        @can('employments.manage')
                            <form method="POST" action="{{ route('employees.work-experiences.destroy', [$employee, $experience]) }}" onsubmit="return confirm('Remove this experience?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                            </form>
                        @endcan
                    </div>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $experience->start_date->toFormattedDateString() }} &ndash; {{ $experience->end_date?->toFormattedDateString() ?? 'Present' }}
                    </p>
                    @if ($experience->description)
                        <p class="mt-2 text-sm text-slate-600">{{ $experience->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
