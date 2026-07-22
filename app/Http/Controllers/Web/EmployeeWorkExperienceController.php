<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkExperienceRequest;
use App\Models\Employee;
use App\Models\EmployeeWorkExperience;
use Illuminate\Http\RedirectResponse;

class EmployeeWorkExperienceController extends Controller
{
    public function store(WorkExperienceRequest $request, Employee $employee): RedirectResponse
    {
        $employee->workExperiences()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Work experience added.');
    }

    public function destroy(Employee $employee, EmployeeWorkExperience $workExperience): RedirectResponse
    {
        $workExperience->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Work experience removed.');
    }
}
