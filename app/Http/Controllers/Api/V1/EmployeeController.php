<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Employees\CreateEmployee;
use App\Actions\Employees\UpdateEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Support\Audit\AccessAudit;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('currentEmployment')->latest()->paginate(25);

        return EmployeeResource::collection($employees);
    }

    public function store(EmployeeRequest $request, CreateEmployee $createEmployee)
    {
        $employee = $createEmployee->handle($request->validated());

        return EmployeeResource::make($employee)->response()->setStatusCode(201);
    }

    public function show(Request $request, Employee $employee)
    {
        $employee->load('currentEmployment');

        $viewer = $request->user();
        $visibleSensitiveFields = collect([
            'salary' => 'employees.view-salary',
            'identity-numbers' => 'employees.view-identity-numbers',
            'bank-details' => 'employees.view-bank-details',
        ])->filter(fn (string $permission) => $viewer->can($permission))->keys()->all();

        if ($visibleSensitiveFields !== []) {
            AccessAudit::sensitiveFieldViewed($employee, $viewer, $visibleSensitiveFields);
        }

        return EmployeeResource::make($employee);
    }

    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee)
    {
        $employee = $updateEmployee->handle($employee, $request->validated());

        return EmployeeResource::make($employee);
    }
}
