<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeNoteRequest;
use App\Models\Employee;
use App\Models\EmployeeNote;
use Illuminate\Http\RedirectResponse;

class EmployeeNoteController extends Controller
{
    public function store(EmployeeNoteRequest $request, Employee $employee): RedirectResponse
    {
        $employee->notes()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Note added.');
    }

    public function destroy(Employee $employee, EmployeeNote $note): RedirectResponse
    {
        $note->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Note removed.');
    }
}
