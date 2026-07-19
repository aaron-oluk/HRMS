<x-layouts.app title="Generate payroll run" header="Generate payroll run">
    <x-card class="max-w-xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Generate payroll run</h2>
            <p class="mt-1 text-sm text-slate-500">Pulls every active employment for the entity and computes PAYE/NSSF for the period.</p>
        </div>

        <form method="POST" action="{{ route('payroll.runs.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-label for="entity_id" value="Entity" />
                    <x-select id="entity_id" name="entity_id" class="mt-1">
                        @foreach ($entities as $entity)
                            <option value="{{ $entity->id }}" @selected(old('entity_id') == $entity->id)>{{ $entity->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="period_month" value="Period" />
                    <x-input id="period_month" type="month" name="period_month" :value="old('period_month', now()->format('Y-m'))" required class="mt-1" />
                    <x-input-error :messages="$errors->get('period_month')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('payroll.runs.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Generate</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
