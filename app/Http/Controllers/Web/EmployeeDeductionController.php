<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDeductionRequest;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use Illuminate\Http\RedirectResponse;

class EmployeeDeductionController extends Controller
{
    public function store(EmployeeDeductionRequest $request, Employee $employee): RedirectResponse
    {
        $employee->deductions()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Deduction recorded.');
    }

    public function destroy(Employee $employee, EmployeeDeduction $deduction): RedirectResponse
    {
        $deduction->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Deduction removed.');
    }
}
