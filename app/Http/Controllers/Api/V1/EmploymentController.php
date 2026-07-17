<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Employments\RecordEmploymentChange;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentRequest;
use App\Http\Resources\EmploymentResource;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;

class EmploymentController extends Controller
{
    public function index(Employee $employee)
    {
        Gate::authorize('employees.view');

        return EmploymentResource::collection($employee->employments()->paginate(25));
    }

    public function store(EmploymentRequest $request, Employee $employee, RecordEmploymentChange $recordEmploymentChange)
    {
        $employment = $recordEmploymentChange->handle($employee, $request->validated());

        return EmploymentResource::make($employment)->response()->setStatusCode(201);
    }
}
