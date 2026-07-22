@can('employees.view-bank-details')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <x-card class="!p-0 overflow-x-auto">
                <div class="border-b border-slate-100 px-4 py-3 text-sm font-semibold text-slate-900">Bank accounts</div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->bankAccounts as $account)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $account->bank_name }}</p>
                                    <p class="text-slate-500">{{ $account->account_name }} · {{ $account->account_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('employees.update')
                                        <form method="POST" action="{{ route('employees.bank-accounts.destroy', [$employee, $account]) }}" onsubmit="return confirm('Remove this account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-500">Remove</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-6 text-center text-slate-500">No bank accounts on file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            @can('employees.update')
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900">Add bank account</h3>
                    <form method="POST" action="{{ route('employees.bank-accounts.store', $employee) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-label for="bank_name" value="Bank" />
                            <x-input id="bank_name" name="bank_name" required class="mt-1" />
                        </div>
                        <div>
                            <x-label for="account_name" value="Account name" />
                            <x-input id="account_name" name="account_name" required class="mt-1" />
                        </div>
                        <div>
                            <x-label for="account_number" value="Account number" />
                            <x-input id="account_number" name="account_number" required class="mt-1" />
                        </div>
                        <x-button type="submit" icon="bx-plus" class="w-full">Add account</x-button>
                    </form>
                </x-card>
            @endcan
        </div>

        <div class="space-y-4">
            <x-card class="!p-0 overflow-x-auto">
                <div class="border-b border-slate-100 px-4 py-3 text-sm font-semibold text-slate-900">Mobile money</div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->mobileMoneyAccounts as $momo)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $momo->provider === 'mtn_momo' ? 'MTN MoMo' : 'Airtel Money' }}</p>
                                    <p class="text-slate-500">{{ $momo->account_name }} · {{ $momo->phone_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('employees.update')
                                        <form method="POST" action="{{ route('employees.mobile-money.destroy', [$employee, $momo]) }}" onsubmit="return confirm('Remove this account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-500">Remove</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-6 text-center text-slate-500">No mobile money accounts on file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            @can('employees.update')
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900">Add mobile money account</h3>
                    <form method="POST" action="{{ route('employees.mobile-money.store', $employee) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-label for="provider" value="Provider" />
                            <x-select id="provider" name="provider" class="mt-1">
                                <option value="mtn_momo">MTN MoMo</option>
                                <option value="airtel_money">Airtel Money</option>
                            </x-select>
                        </div>
                        <div>
                            <x-label for="mm_account_name" value="Account name" />
                            <x-input id="mm_account_name" name="account_name" required class="mt-1" />
                        </div>
                        <div>
                            <x-label for="phone_number" value="Phone number" />
                            <x-input id="phone_number" name="phone_number" required class="mt-1" />
                        </div>
                        <x-button type="submit" icon="bx-plus" class="w-full">Add account</x-button>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
@else
    <x-card>
        <p class="text-sm text-slate-500">You don't have permission to view bank or mobile money details.</p>
    </x-card>
@endcan

@can('employees.view-salary')
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card x-data="{ addingAdvance: false }">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Advances</h3>
                @can('payroll.run')
                    <button type="button" @click="addingAdvance = ! addingAdvance" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                        <span x-show="! addingAdvance">+ Add advance</span>
                        <span x-show="addingAdvance" x-cloak>Cancel</span>
                    </button>
                @endcan
            </div>

            @can('payroll.run')
                <form x-show="addingAdvance" x-cloak method="POST" action="{{ route('employees.advances.store', $employee) }}" class="mb-4 space-y-3 rounded-md border border-slate-100 p-3">
                    @csrf
                    <x-input name="amount" type="number" step="0.01" min="0.01" placeholder="Amount" required />
                    <x-input name="monthly_deduction" type="number" step="0.01" min="0.01" placeholder="Monthly deduction" required />
                    <x-input name="reason" placeholder="Reason (optional)" />
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Issued on</label>
                        <x-input type="date" name="issued_date" value="{{ now()->toDateString() }}" required />
                    </div>
                    <x-button type="submit" class="w-full">Save advance</x-button>
                </form>
            @endcan

            @if ($employee->advances->isEmpty())
                <p class="text-sm text-slate-500">No advances on record.</p>
            @else
                <div class="space-y-3">
                    @foreach ($employee->advances as $advance)
                        <div class="rounded-md border border-slate-100 p-3">
                            <div class="flex items-start justify-between gap-x-2">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ number_format((float) $advance->amount) }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format((float) $advance->monthly_deduction) }} / month &middot; balance {{ number_format((float) $advance->balance_remaining) }}</p>
                                </div>
                                <div class="flex items-center gap-x-2">
                                    <x-badge :color="$advance->status === 'settled' ? 'success' : 'neutral'">{{ ucfirst($advance->status) }}</x-badge>
                                    @can('payroll.run')
                                        <form method="POST" action="{{ route('employees.advances.destroy', [$employee, $advance]) }}" onsubmit="return confirm('Remove this advance?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                            @if ($advance->reason)
                                <p class="mt-2 text-sm text-slate-600">{{ $advance->reason }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card x-data="{ addingDeduction: false }">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Deductions</h3>
                @can('payroll.run')
                    <button type="button" @click="addingDeduction = ! addingDeduction" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                        <span x-show="! addingDeduction">+ Add deduction</span>
                        <span x-show="addingDeduction" x-cloak>Cancel</span>
                    </button>
                @endcan
            </div>

            @can('payroll.run')
                <form x-show="addingDeduction" x-cloak method="POST" action="{{ route('employees.deductions.store', $employee) }}" class="mb-4 space-y-3 rounded-md border border-slate-100 p-3">
                    @csrf
                    <x-input name="label" placeholder="Label" required />
                    <x-input name="amount" type="number" step="0.01" min="0.01" placeholder="Amount" required />
                    <x-select name="frequency" required>
                        @foreach (\App\Models\EmployeeDeduction::FREQUENCIES as $frequency)
                            <option value="{{ $frequency }}">{{ ucfirst(str_replace('_', ' ', $frequency)) }}</option>
                        @endforeach
                    </x-select>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Effective from</label>
                        <x-input type="date" name="effective_date" value="{{ now()->toDateString() }}" required />
                    </div>
                    <x-button type="submit" class="w-full">Save deduction</x-button>
                </form>
            @endcan

            @if ($employee->deductions->isEmpty())
                <p class="text-sm text-slate-500">No deductions on record.</p>
            @else
                <div class="space-y-3">
                    @foreach ($employee->deductions as $deduction)
                        <div class="rounded-md border border-slate-100 p-3">
                            <div class="flex items-start justify-between gap-x-2">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $deduction->label }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format((float) $deduction->amount) }} &middot; {{ ucfirst(str_replace('_', ' ', $deduction->frequency)) }}</p>
                                </div>
                                <div class="flex items-center gap-x-2">
                                    <x-badge :color="$deduction->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($deduction->status) }}</x-badge>
                                    @can('payroll.run')
                                        <form method="POST" action="{{ route('employees.deductions.destroy', [$employee, $deduction]) }}" onsubmit="return confirm('Remove this deduction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
@endcan
