<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeMobileMoneyRequest;
use App\Http\Resources\EmployeeMobileMoneyResource;
use App\Models\Employee;
use App\Models\EmployeeMobileMoney;
use Illuminate\Support\Facades\Gate;

class EmployeeMobileMoneyController extends Controller
{
    public function index(Employee $employee)
    {
        Gate::authorize('employees.view-bank-details');

        return EmployeeMobileMoneyResource::collection($employee->mobileMoneyAccounts()->get());
    }

    public function store(EmployeeMobileMoneyRequest $request, Employee $employee)
    {
        $account = $employee->mobileMoneyAccounts()->create($request->validated());

        return EmployeeMobileMoneyResource::make($account)->response()->setStatusCode(201);
    }

    public function update(EmployeeMobileMoneyRequest $request, Employee $employee, EmployeeMobileMoney $mobileMoney)
    {
        $mobileMoney->update($request->validated());

        return EmployeeMobileMoneyResource::make($mobileMoney);
    }

    public function destroy(Employee $employee, EmployeeMobileMoney $mobileMoney)
    {
        Gate::authorize('employees.update');

        $mobileMoney->delete();

        return response()->noContent();
    }
}
