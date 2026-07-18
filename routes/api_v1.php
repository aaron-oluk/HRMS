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
// related routes are wrapped in a Route::middleware('role:RoleA|RoleB')->group() block
// (see App\Actions\Tenancy\ProvisionDefaultRoles::ROLE_PERMISSIONS for which roles hold
// which permissions today). Record-level scoping (e.g. "only your direct reports/
// department") still happens inside the relevant Action class (see
// App\Support\Approvals\TeamScope), since that depends on which record is being acted
// on, not just which role the actor holds. Blade's @can()/@cannot() checks (nav
// visibility, field-level display) still use the underlying permission catalog — only
// route-level enforcement is role-based.
Route::middleware(['auth:sanctum', IdentifyTenant::class, SubstituteBindings::class])->group(function (): void {
    Route::get('/user', fn () => request()->user());

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')->group(function (): void {
        Route::apiResource('entities', EntityController::class)->only(['index', 'show']);
        Route::apiResource('branches', BranchController::class)->only(['index', 'show']);
        Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
        Route::apiResource('positions', PositionController::class)->only(['index', 'show']);
        Route::apiResource('grades', GradeController::class)->only(['index', 'show']);
        Route::apiResource('leave-types', LeaveTypeController::class)->only(['index', 'show']);
        Route::apiResource('shifts', ShiftController::class)->only(['index', 'show']);
    });

    Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
        Route::apiResource('entities', EntityController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('branches', BranchController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('departments', DepartmentController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('positions', PositionController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('grades', GradeController::class)->only(['store', 'update', 'destroy']);
    });

    Route::middleware('role:HR Admin')->group(function (): void {
        Route::apiResource('users', UserController::class)->except('destroy');
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')->group(function (): void {
        Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::apiResource('employees', EmployeeController::class)->only(['store']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
        Route::apiResource('employees', EmployeeController::class)->only(['update']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::apiResource('leave-types', LeaveTypeController::class)->only(['store', 'update', 'destroy']);
    });

    Route::apiResource('leave-requests', LeaveRequestController::class)->only(['index', 'store']);

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead')->group(function (): void {
        Route::get('leave-approvals', [LeaveApprovalController::class, 'index']);
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve']);
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
        Route::apiResource('shifts', ShiftController::class)->only(['store', 'update', 'destroy']);
    });

    Route::get('attendance/my-timesheet', [AttendanceController::class, 'myTimesheet']);
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead')->group(function (): void {
        Route::get('attendance/team-today', [AttendanceController::class, 'teamToday']);
    });

    Route::apiResource('overtime-requests', OvertimeRequestController::class)->only(['index', 'store']);

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead')->group(function (): void {
        Route::get('overtime-approvals', [OvertimeApprovalController::class, 'index']);
        Route::post('overtime-requests/{overtimeRequest}/approve', [OvertimeApprovalController::class, 'approve']);
        Route::post('overtime-requests/{overtimeRequest}/reject', [OvertimeApprovalController::class, 'reject']);
    });

    Route::scopeBindings()->group(function (): void {
        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')->group(function (): void {
            Route::apiResource('employees.employments', EmploymentController::class)->only(['index']);
        });

        Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
            Route::apiResource('employees.employments', EmploymentController::class)->only(['store']);
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor')->group(function (): void {
            Route::apiResource('employees.documents', EmployeeDocumentController::class)
                ->parameters(['documents' => 'document'])
                ->only(['index']);
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
            Route::apiResource('employees.documents', EmployeeDocumentController::class)
                ->parameters(['documents' => 'document'])
                ->only(['store', 'destroy']);
        });

        Route::middleware('role:HR Admin|HR Manager|Auditor|Accountant')->group(function (): void {
            Route::apiResource('employees.bank-accounts', EmployeeBankAccountController::class)
                ->parameters(['bank-accounts' => 'bankAccount'])
                ->only(['index']);

            Route::apiResource('employees.mobile-money', EmployeeMobileMoneyController::class)
                ->parameters(['mobile-money' => 'mobileMoney'])
                ->only(['index']);
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
            Route::apiResource('employees.bank-accounts', EmployeeBankAccountController::class)
                ->parameters(['bank-accounts' => 'bankAccount'])
                ->only(['store', 'update', 'destroy']);

            Route::apiResource('employees.mobile-money', EmployeeMobileMoneyController::class)
                ->parameters(['mobile-money' => 'mobileMoney'])
                ->only(['store', 'update', 'destroy']);
        });
    });
});
