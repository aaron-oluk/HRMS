<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeBankAccountRequest;
use App\Http\Resources\EmployeeBankAccountResource;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use Illuminate\Support\Facades\Gate;

class EmployeeBankAccountController extends Controller
{
    public function index(Employee $employee)
    {
        Gate::authorize('employees.view-bank-details');

        return EmployeeBankAccountResource::collection($employee->bankAccounts()->get());
    }

    public function store(EmployeeBankAccountRequest $request, Employee $employee)
    {
        $account = $employee->bankAccounts()->create($request->validated());

        return EmployeeBankAccountResource::make($account)->response()->setStatusCode(201);
    }

    public function update(EmployeeBankAccountRequest $request, Employee $employee, EmployeeBankAccount $bankAccount)
    {
        $bankAccount->update($request->validated());

        return EmployeeBankAccountResource::make($bankAccount);
    }

    public function destroy(Employee $employee, EmployeeBankAccount $bankAccount)
    {
        Gate::authorize('employees.update');

        $bankAccount->delete();

        return response()->noContent();
    }
}
