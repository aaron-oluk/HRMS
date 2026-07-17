<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeBankAccountRequest;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeBankAccountController extends Controller
{
    public function store(EmployeeBankAccountRequest $request, Employee $employee): RedirectResponse
    {
        $employee->bankAccounts()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Bank account added.');
    }

    public function destroy(Employee $employee, EmployeeBankAccount $bankAccount): RedirectResponse
    {
        Gate::authorize('employees.manage');

        $bankAccount->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Bank account removed.');
    }
}
