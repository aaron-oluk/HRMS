<?php

namespace App\Http\Controllers\Web;

use App\Actions\Employees\CreateEmployee;
use App\Actions\Employees\UpdateEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Entity;
use App\Support\Audit\AccessAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with('currentEmployment.position', 'entity')->latest()->paginate(15);

        return view('employees.index', ['employees' => $employees]);
    }

    public function create(): View
    {
        return view('employees.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(EmployeeRequest $request, CreateEmployee $createEmployee): RedirectResponse
    {
        $employee = $createEmployee->handle($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'entity',
            'employments' => fn ($query) => $query->with('department', 'position', 'grade', 'branch'),
            'documents',
            'bankAccounts',
            'mobileMoneyAccounts',
        ]);

        $viewer = auth()->user();
        $visibleSensitiveFields = collect([
            'salary' => 'employees.view-salary',
            'identity-numbers' => 'employees.view-identity-numbers',
            'bank-details' => 'employees.view-bank-details',
        ])->filter(fn (string $permission) => $viewer->can($permission))->keys()->all();

        if ($visibleSensitiveFields !== []) {
            AccessAudit::sensitiveFieldViewed($employee, $viewer, $visibleSensitiveFields);
        }

        return view('employees.show', ['employee' => $employee]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', ['employee' => $employee, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee): RedirectResponse
    {
        $updateEmployee->handle($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee updated.');
    }
}
