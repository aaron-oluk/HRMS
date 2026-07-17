<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Employees\CreateEmployee;
use App\Actions\Employees\UpdateEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('employees.view');

        $employees = Employee::with('currentEmployment')->latest()->paginate(25);

        return EmployeeResource::collection($employees);
    }

    public function store(EmployeeRequest $request, CreateEmployee $createEmployee)
    {
        $employee = $createEmployee->handle($request->validated());

        return EmployeeResource::make($employee)->response()->setStatusCode(201);
    }

    public function show(Employee $employee)
    {
        Gate::authorize('employees.view');

        $employee->load('currentEmployment');

        return EmployeeResource::make($employee);
    }

    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee)
    {
        $employee = $updateEmployee->handle($employee, $request->validated());

        return EmployeeResource::make($employee);
    }
}
