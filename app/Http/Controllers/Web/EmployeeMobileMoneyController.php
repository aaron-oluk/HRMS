<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeMobileMoneyRequest;
use App\Models\Employee;
use App\Models\EmployeeMobileMoney;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeMobileMoneyController extends Controller
{
    public function store(EmployeeMobileMoneyRequest $request, Employee $employee): RedirectResponse
    {
        $employee->mobileMoneyAccounts()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Mobile money account added.');
    }

    public function destroy(Employee $employee, EmployeeMobileMoney $mobileMoney): RedirectResponse
    {
        Gate::authorize('employees.update');

        $mobileMoney->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Mobile money account removed.');
    }
}
