<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeBankAccountController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\EmployeeDocumentController;
use App\Http\Controllers\Api\V1\EmployeeMobileMoneyController;
use App\Http\Controllers\Api\V1\EmploymentController;
use App\Http\Controllers\Api\V1\EntityController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\LeaveApprovalController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\OvertimeApprovalController;
use App\Http\Controllers\Api\V1\OvertimeRequestController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// Order matters: auth:sanctum must run first so Auth::shouldUse('sanctum') takes effect
// before IdentifyTenant reads request()->user(); SubstituteBindings (route model binding)
// must run after IdentifyTenant so tenant-scoped models are bound under the right scope.
// SubstituteBindings is removed from the global 'api' group in bootstrap/app.php so it
// can be re-added here, after IdentifyTenant, instead of before it.
//
// Access control lives here, on the routes, rather than inside controllers/requests:
// every gated action is declared with a 'role:RoleA|RoleB' middleware, listing every
// role (see App\Actions\Tenancy\ProvisionDefaultRoles::ROLE_PERMISSIONS) that should
// reach it. Record-level scoping (e.g. "only your direct reports/department") still
// happens inside the relevant Action class (see App\Support\Approvals\TeamScope),
// since that depends on which record is being acted on, not just which role the actor
// holds. Blade's @can()/@cannot() checks (nav visibility, field-level display) still
// use the underlying permission catalog — only route-level enforcement is role-based.
Route::middleware(['auth:sanctum', IdentifyTenant::class, SubstituteBindings::class])->group(function (): void {
    Route::get('/user', fn () => request()->user());

    Route::apiResource('entities', EntityController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::apiResource('branches', BranchController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::apiResource('departments', DepartmentController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::apiResource('positions', PositionController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::apiResource('grades', GradeController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::apiResource('users', UserController::class)->except('destroy')
        ->middleware('role:HR Admin');

    Route::apiResource('employees', EmployeeController::class)->except('destroy')
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')
        ->middlewareFor(['store'], 'role:HR Admin|HR Manager|HR Specialist')
        ->middlewareFor(['update'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

    Route::apiResource('leave-types', LeaveTypeController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist');

    Route::apiResource('leave-requests', LeaveRequestController::class)->only(['index', 'store']);
    Route::get('leave-approvals', [LeaveApprovalController::class, 'index'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');

    Route::apiResource('shifts', ShiftController::class)
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

    Route::get('attendance/my-timesheet', [AttendanceController::class, 'myTimesheet']);
    Route::get('attendance/team-today', [AttendanceController::class, 'teamToday'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);

    Route::apiResource('overtime-requests', OvertimeRequestController::class)->only(['index', 'store']);
    Route::get('overtime-approvals', [OvertimeApprovalController::class, 'index'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('overtime-requests/{overtimeRequest}/approve', [OvertimeApprovalController::class, 'approve'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('overtime-requests/{overtimeRequest}/reject', [OvertimeApprovalController::class, 'reject'])->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');

    Route::scopeBindings()->group(function (): void {
        Route::apiResource('employees.employments', EmploymentController::class)
            ->only(['index', 'store'])
            ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')
            ->middlewareFor(['store'], 'role:HR Admin|HR Manager');

        Route::apiResource('employees.documents', EmployeeDocumentController::class)
            ->parameters(['documents' => 'document'])
            ->only(['index', 'store', 'destroy'])
            ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor')
            ->middlewareFor(['store', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

        Route::apiResource('employees.bank-accounts', EmployeeBankAccountController::class)
            ->parameters(['bank-accounts' => 'bankAccount'])
            ->only(['index', 'store', 'update', 'destroy'])
            ->middlewareFor(['index'], 'role:HR Admin|HR Manager|Auditor|Accountant')
            ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

        Route::apiResource('employees.mobile-money', EmployeeMobileMoneyController::class)
            ->parameters(['mobile-money' => 'mobileMoney'])
            ->only(['index', 'store', 'update', 'destroy'])
            ->middlewareFor(['index'], 'role:HR Admin|HR Manager|Auditor|Accountant')
            ->middlewareFor(['store', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');
    });
});
