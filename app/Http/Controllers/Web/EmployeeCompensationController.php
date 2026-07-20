<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeCompensationItemRequest;
use App\Models\Employee;
use App\Models\EmployeeCompensationItem;
use Illuminate\Http\RedirectResponse;

class EmployeeCompensationController extends Controller
{
    public function store(EmployeeCompensationItemRequest $request, Employee $employee): RedirectResponse
    {
        $employee->compensationItems()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Compensation item added.');
    }

    public function destroy(Employee $employee, EmployeeCompensationItem $compensationItem): RedirectResponse
    {
        $compensationItem->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Compensation item removed.');
    }
}
