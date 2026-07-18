<?php

namespace App\Http\Controllers\Web;

use App\Actions\Employments\RecordEmploymentChange;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Grade;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EmploymentController extends Controller
{
    public function create(Employee $employee): View
    {
        return view('employments.create', [
            'employee' => $employee,
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('title')->get(),
            'grades' => Grade::orderBy('name')->get(),
        ]);
    }

    public function store(EmploymentRequest $request, Employee $employee, RecordEmploymentChange $recordEmploymentChange): RedirectResponse
    {
        $recordEmploymentChange->handle($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employment record updated.');
    }
}
