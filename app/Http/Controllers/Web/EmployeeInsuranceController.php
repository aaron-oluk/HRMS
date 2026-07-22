<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceRequest;
use App\Models\Employee;
use App\Models\EmployeeInsurance;
use Illuminate\Http\RedirectResponse;

class EmployeeInsuranceController extends Controller
{
    public function store(InsuranceRequest $request, Employee $employee): RedirectResponse
    {
        $employee->insurances()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Insurance policy added.');
    }

    public function destroy(Employee $employee, EmployeeInsurance $insurance): RedirectResponse
    {
        $insurance->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Insurance policy removed.');
    }
}
