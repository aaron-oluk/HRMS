<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeAdvanceRequest;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Http\RedirectResponse;

class EmployeeAdvanceController extends Controller
{
    public function store(EmployeeAdvanceRequest $request, Employee $employee): RedirectResponse
    {
        $employee->advances()->create([
            ...$request->validated(),
            'balance_remaining' => $request->validated('amount'),
        ]);

        return redirect()->route('employees.show', $employee)->with('status', 'Advance recorded.');
    }

    public function destroy(Employee $employee, EmployeeAdvance $advance): RedirectResponse
    {
        $advance->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Advance removed.');
    }
}
