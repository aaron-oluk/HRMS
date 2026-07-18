<?php

use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeBankAccountController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeDocumentController;
use App\Http\Controllers\Web\EmployeeMobileMoneyController;
use App\Http\Controllers\Web\EmploymentController;
use App\Http\Controllers\Web\EntityController;
use App\Http\Controllers\Web\GradeController;
use App\Http\Controllers\Web\InboxController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\LeaveTypeController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Access control lives here, on the routes, rather than inside controllers/requests:
// related routes are wrapped in a Route::middleware('role:RoleA|RoleB')->group() block
// (see App\Actions\Tenancy\ProvisionDefaultRoles::ROLE_PERMISSIONS for which roles hold
// which permissions today). Record-level scoping (e.g. "only your direct reports/
// department") still happens inside the relevant Action class (see
// App\Support\Approvals\TeamScope), since that depends on which record is being acted
// on, not just which role the actor holds. Blade's @can()/@cannot() checks (nav
// visibility, field-level display) still use the underlying permission catalog — only
// route-level enforcement is role-based.
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::middleware('role:HR Admin|Auditor|Accountant')->group(function (): void {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')->group(function (): void {
        Route::resource('entities', EntityController::class)->only(['index']);
        Route::resource('branches', BranchController::class)->only(['index']);
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('positions', PositionController::class)->only(['index']);
        Route::resource('grades', GradeController::class)->only(['index']);
        Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
        Route::resource('shifts', ShiftController::class)->only(['index']);
    });

    Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
        Route::resource('entities', EntityController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('branches', BranchController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('departments', DepartmentController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('positions', PositionController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('grades', GradeController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::middleware('role:HR Admin')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    // Order matters here: 'create' (GET /employees/create) must be registered before
    // 'show' (GET /employees/{employee}), otherwise Laravel matches "create" against
    // the {employee} wildcard first and tries to route-model-bind the literal string
    // "create" as an employee ID. A single Route::resource() call handles this
    // ordering automatically; splitting it across role groups means we must preserve
    // it by hand.
    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::resource('employees', EmployeeController::class)->only(['create', 'store']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
        Route::resource('employees', EmployeeController::class)->only(['edit', 'update']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')->group(function (): void {
        Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::resource('leave-types', LeaveTypeController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead')->group(function (): void {
        Route::post('leave/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('leave/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave.reject');
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
        Route::resource('shifts', ShiftController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('attendance/overtime', [AttendanceController::class, 'storeOvertime'])->name('attendance.overtime.store');

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead')->group(function (): void {
        Route::post('attendance/overtime/{overtimeRequest}/approve', [AttendanceController::class, 'approveOvertime'])->name('attendance.overtime.approve');
        Route::post('attendance/overtime/{overtimeRequest}/reject', [AttendanceController::class, 'rejectOvertime'])->name('attendance.overtime.reject');
    });

    Route::scopeBindings()->group(function (): void {
        Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
            Route::get('employees/{employee}/employments/create', [EmploymentController::class, 'create'])
                ->name('employees.employments.create');
            Route::post('employees/{employee}/employments', [EmploymentController::class, 'store'])
                ->name('employees.employments.store');
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
            Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])
                ->name('employees.documents.store');
            Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])
                ->name('employees.documents.destroy');
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
            Route::post('employees/{employee}/bank-accounts', [EmployeeBankAccountController::class, 'store'])
                ->name('employees.bank-accounts.store');
            Route::delete('employees/{employee}/bank-accounts/{bankAccount}', [EmployeeBankAccountController::class, 'destroy'])
                ->name('employees.bank-accounts.destroy');

            Route::post('employees/{employee}/mobile-money', [EmployeeMobileMoneyController::class, 'store'])
                ->name('employees.mobile-money.store');
            Route::delete('employees/{employee}/mobile-money/{mobileMoney}', [EmployeeMobileMoneyController::class, 'destroy'])
                ->name('employees.mobile-money.destroy');
        });
    });
});
