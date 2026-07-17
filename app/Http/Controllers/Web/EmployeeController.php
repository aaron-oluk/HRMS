<?php

namespace App\Http\Controllers\Web;

use App\Actions\Employees\CreateEmployee;
use App\Actions\Employees\UpdateEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('employees.view');

        $employees = Employee::with('currentEmployment.position', 'entity')->latest()->paginate(15);

        return view('employees.index', ['employees' => $employees]);
    }

    public function create(): View
    {
        Gate::authorize('employees.manage');

        return view('employees.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(EmployeeRequest $request, CreateEmployee $createEmployee): RedirectResponse
    {
        $employee = $createEmployee->handle($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        Gate::authorize('employees.view');

        $employee->load([
            'entity',
            'employments' => fn ($query) => $query->with('department', 'position', 'grade', 'branch'),
            'documents',
            'bankAccounts',
            'mobileMoneyAccounts',
        ]);

        return view('employees.show', ['employee' => $employee]);
    }

    public function edit(Employee $employee): View
    {
        Gate::authorize('employees.manage');

        return view('employees.edit', ['employee' => $employee, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee): RedirectResponse
    {
        $updateEmployee->handle($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee updated.');
    }
}
