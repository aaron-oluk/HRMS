@can('employees.view-identity-numbers')
    <x-card x-data="{ addingInsurance: false }">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Insurance</h3>
            @can('employees.update')
                <button type="button" @click="addingInsurance = ! addingInsurance" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                    <span x-show="! addingInsurance">+ Add policy</span>
                    <span x-show="addingInsurance" x-cloak>Cancel</span>
                </button>
            @endcan
        </div>

        @can('employees.update')
            <form x-show="addingInsurance" x-cloak method="POST" action="{{ route('employees.insurances.store', $employee) }}" class="mb-4 space-y-3 rounded-md border border-slate-100 p-3">
                @csrf
                <x-input name="provider" placeholder="Provider" required />
                <x-input name="policy_number" placeholder="Policy number" required />
                <x-select name="type" required>
                    @foreach (\App\Models\EmployeeInsurance::TYPES as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </x-select>
                <x-input name="coverage_amount" type="number" step="0.01" min="0" placeholder="Coverage amount (optional)" />
                <x-input name="dependents_covered" placeholder="Dependents covered (optional)" />
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Start date</label>
                    <x-input type="date" name="start_date" required />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">End date (optional)</label>
                    <x-input type="date" name="end_date" />
                </div>
                <x-button type="submit" class="w-full">Save policy</x-button>
            </form>
        @endcan

        @if ($employee->insurances->isEmpty())
            <p class="text-sm text-slate-500">No insurance policies on record.</p>
        @else
            <div class="space-y-3">
                @foreach ($employee->insurances as $insurance)
                    <div class="rounded-md border border-slate-100 p-3">
                        <div class="flex items-start justify-between gap-x-2">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $insurance->provider }}</p>
                                <p class="text-xs text-slate-500">{{ ucfirst($insurance->type) }} &middot; {{ $insurance->policy_number }}</p>
                            </div>
                            @can('employees.update')
                                <form method="POST" action="{{ route('employees.insurances.destroy', [$employee, $insurance]) }}" onsubmit="return confirm('Remove this policy?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                                </form>
                            @endcan
                        </div>
                        @if ($insurance->coverage_amount)
                            <p class="mt-1 text-xs text-slate-400">Coverage: {{ number_format((float) $insurance->coverage_amount) }}</p>
                        @endif
                        @if ($insurance->dependents_covered)
                            <p class="text-xs text-slate-400">Dependents: {{ $insurance->dependents_covered }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
@endcan
