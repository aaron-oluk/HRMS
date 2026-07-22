<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarningRequest;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmployeeWarningController extends Controller
{
    public function mine(Request $request): View
    {
        $employee = $request->user()->employee;

        $warnings = $employee ? $employee->warnings()->with('issuer')->get() : collect();

        return view('warnings.my', ['warnings' => $warnings]);
    }

    public function store(WarningRequest $request, Employee $employee): RedirectResponse
    {
        $employee->warnings()->create([
            ...$request->validated(),
            'issued_by' => $request->user()->id,
        ]);

        return redirect()->route('employees.show', $employee)->with('status', 'Warning issued.');
    }

    /**
     * Only the employee the warning was issued to may acknowledge it.
     */
    public function acknowledge(Request $request, Employee $employee, EmployeeWarning $warning): RedirectResponse
    {
        abort_unless($request->user()->employee_id === $employee->id, 403);

        if ($warning->acknowledged_at === null) {
            $warning->update(['acknowledged_at' => now()]);
        }

        return redirect()->route('employees.show', $employee)->with('status', 'Warning acknowledged.');
    }

    public function destroy(Employee $employee, EmployeeWarning $warning): RedirectResponse
    {
        $warning->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Warning removed.');
    }
}
