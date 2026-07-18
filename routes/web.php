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
use App\Http\Controllers\Web\SecuritySettingsController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Access control lives here, on the routes, rather than inside controllers/requests:
// every gated action is declared with a 'role:RoleA|RoleB' middleware, listing every
// role (see App\Actions\Tenancy\ProvisionDefaultRoles::ROLE_PERMISSIONS) that should
// reach it. Record-level scoping (e.g. "only your direct reports/department") still
// happens inside the relevant Action class (see App\Support\Approvals\TeamScope),
// since that depends on which record is being acted on, not just which role the actor
// holds. Blade's @can()/@cannot() checks (nav visibility, field-level display) still
// use the underlying permission catalog — only route-level enforcement is role-based.
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');

    Route::get('/settings/security', [SecuritySettingsController::class, 'edit'])->name('security.edit');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('role:HR Admin|Auditor|Accountant');

    Route::resource('entities', EntityController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::resource('branches', BranchController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::resource('departments', DepartmentController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::resource('positions', PositionController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::resource('grades', GradeController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager');

    Route::resource('users', UserController::class)->except(['show', 'destroy'])
        ->middleware('role:HR Admin');

    Route::resource('employees', EmployeeController::class)->except('destroy')
        ->middlewareFor(['index', 'show'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead|Auditor|Accountant|Executive')
        ->middlewareFor(['create', 'store'], 'role:HR Admin|HR Manager|HR Specialist')
        ->middlewareFor(['edit', 'update'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

    Route::resource('leave-types', LeaveTypeController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist');

    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('leave/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave.approve')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('leave/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave.reject')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');

    Route::resource('shifts', ShiftController::class)->except('show')
        ->middlewareFor(['index'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:HR Admin|HR Manager|HR Specialist|Department Manager');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('attendance/overtime', [AttendanceController::class, 'storeOvertime'])->name('attendance.overtime.store');
    Route::post('attendance/overtime/{overtimeRequest}/approve', [AttendanceController::class, 'approveOvertime'])->name('attendance.overtime.approve')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');
    Route::post('attendance/overtime/{overtimeRequest}/reject', [AttendanceController::class, 'rejectOvertime'])->name('attendance.overtime.reject')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Team Lead');

    Route::scopeBindings()->group(function (): void {
        Route::get('employees/{employee}/employments/create', [EmploymentController::class, 'create'])
            ->name('employees.employments.create')->middleware('role:HR Admin|HR Manager');
        Route::post('employees/{employee}/employments', [EmploymentController::class, 'store'])
            ->name('employees.employments.store')->middleware('role:HR Admin|HR Manager');

        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])
            ->name('employees.documents.store')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])
            ->name('employees.documents.destroy')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');

        Route::post('employees/{employee}/bank-accounts', [EmployeeBankAccountController::class, 'store'])
            ->name('employees.bank-accounts.store')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');
        Route::delete('employees/{employee}/bank-accounts/{bankAccount}', [EmployeeBankAccountController::class, 'destroy'])
            ->name('employees.bank-accounts.destroy')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');

        Route::post('employees/{employee}/mobile-money', [EmployeeMobileMoneyController::class, 'store'])
            ->name('employees.mobile-money.store')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');
        Route::delete('employees/{employee}/mobile-money/{mobileMoney}', [EmployeeMobileMoneyController::class, 'destroy'])
            ->name('employees.mobile-money.destroy')->middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager');
    });
});
