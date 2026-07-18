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

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');

    Route::get('/settings/security', [SecuritySettingsController::class, 'edit'])->name('security.edit');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::resource('entities', EntityController::class)->except('show');
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('positions', PositionController::class)->except('show');
    Route::resource('grades', GradeController::class)->except('show');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::resource('employees', EmployeeController::class)->except('destroy');

    Route::resource('leave-types', LeaveTypeController::class)->except('show');

    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('leave/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
    Route::post('leave/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave.reject');

    Route::resource('shifts', ShiftController::class)->except('show');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('attendance/overtime', [AttendanceController::class, 'storeOvertime'])->name('attendance.overtime.store');
    Route::post('attendance/overtime/{overtimeRequest}/approve', [AttendanceController::class, 'approveOvertime'])->name('attendance.overtime.approve');
    Route::post('attendance/overtime/{overtimeRequest}/reject', [AttendanceController::class, 'rejectOvertime'])->name('attendance.overtime.reject');

    Route::scopeBindings()->group(function (): void {
        Route::get('employees/{employee}/employments/create', [EmploymentController::class, 'create'])
            ->name('employees.employments.create');
        Route::post('employees/{employee}/employments', [EmploymentController::class, 'store'])
            ->name('employees.employments.store');

        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])
            ->name('employees.documents.store');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])
            ->name('employees.documents.destroy');

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
