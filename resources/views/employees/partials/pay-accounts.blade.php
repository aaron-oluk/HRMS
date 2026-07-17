@can('employees.view-sensitive')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <x-card class="!p-0 overflow-x-auto">
                <div class="border-b border-slate-100 px-4 py-3 text-sm font-semibold text-slate-900">Bank accounts</div>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->bankAccounts as $account)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $account->bank_name }}</p>
                                    <p class="text-slate-500">{{ $account->account_name }} · {{ $account->account_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('employees.manage')
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

            @can('employees.manage')
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
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->mobileMoneyAccounts as $momo)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $momo->provider === 'mtn_momo' ? 'MTN MoMo' : 'Airtel Money' }}</p>
                                    <p class="text-slate-500">{{ $momo->account_name }} · {{ $momo->phone_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('employees.manage')
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

            @can('employees.manage')
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
