<x-layouts.app title="Dashboard" header="Dashboard">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-card>
            <p class="text-sm text-slate-500">Employees</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $employeeCount }}</p>
        </x-card>

        <x-card>
            <p class="text-sm text-slate-500">Entities</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $entityCount }}</p>
        </x-card>
    </div>
</x-layouts.app>
