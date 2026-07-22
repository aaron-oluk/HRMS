<x-card class="max-w-2xl">
    <div class="mb-6 border-b border-slate-100 pb-5">
        <h2 class="text-base font-semibold text-slate-900">Organization structure</h2>
        <p class="mt-1 text-sm text-slate-500">Choose how your company is organized. This can be changed at any time.</p>
    </div>

    <div class="mb-6 flex items-center gap-x-3">
        <span class="text-sm text-slate-500">Current structure:</span>
        <x-badge :color="$tenant->isSegmented() ? 'info' : 'neutral'">{{ $tenant->isSegmented() ? 'Segmented' : 'Simple' }}</x-badge>
    </div>

    <form method="POST" action="{{ route('organization.update-structure') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <label class="flex cursor-pointer items-start gap-x-3 rounded-md border border-slate-200 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
            <input type="radio" name="structure" value="simple" class="mt-1" @checked(! $tenant->isSegmented())>
            <span>
                <span class="block text-sm font-medium text-slate-900">Simple</span>
                <span class="block text-sm text-slate-500">A single location. Employees, leave, and payroll are managed company-wide with no branch/area distinction.</span>
            </span>
        </label>

        <label class="flex cursor-pointer items-start gap-x-3 rounded-md border border-slate-200 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
            <input type="radio" name="structure" value="segmented" class="mt-1" @checked($tenant->isSegmented())>
            <span>
                <span class="block text-sm font-medium text-slate-900">Segmented</span>
                <span class="block text-sm text-slate-500">Multiple branches grouped into areas under Head Office. Unlocks Area management and location-scoped Branch/Area Manager roles.</span>
            </span>
        </label>

        <div class="flex justify-end border-t border-slate-100 pt-5">
            <x-button type="submit">Save structure</x-button>
        </div>
    </form>
</x-card>
