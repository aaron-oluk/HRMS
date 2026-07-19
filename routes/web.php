<?php

use App\Http\Controllers\Web\Admin\TenantController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\CandidateController;
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
use App\Http\Controllers\Web\JobRequisitionController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\LeaveTypeController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PayrollRunController;
use App\Http\Controllers\Web\PerformanceReviewController;
use App\Http\Controllers\Web\PerformanceReviewCycleController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! request()->user()) {
        return view('landing');
    }

    return redirect()->route(request()->user()->is_super_admin ? 'admin.tenants.index' : 'dashboard');
})->name('landing');

// The platform admin console: onboarding a new company (tenant) is deliberately not
// self-service (see App\Actions\Tenancy\CreateTenant) — only a super admin reaches
// this, via its own middleware rather than the role:/permission: ones used below,
// since a super admin holds no tenant-scoped role at all.
Route::middleware(['auth', 'super-admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('tenants', TenantController::class)->only(['index', 'create', 'store']);
});

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
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

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

    // 'create' must be registered before the index/show group's {payrollRun} wildcard route,
    // otherwise Laravel matches the literal "create" segment against it (same class of bug as
    // the employees resource above).
    Route::middleware('role:HR Admin|HR Manager|Accountant')->group(function (): void {
        Route::get('payroll/runs/create', [PayrollRunController::class, 'create'])->name('payroll.runs.create');
        Route::post('payroll/runs', [PayrollRunController::class, 'store'])->name('payroll.runs.store');
        Route::post('payroll/runs/{payrollRun}/submit', [PayrollRunController::class, 'submit'])->name('payroll.runs.submit');
        Route::post('payroll/runs/{payrollRun}/disburse', [PayrollRunController::class, 'disburse'])->name('payroll.runs.disburse');
    });

    Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
        Route::post('payroll/runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])->name('payroll.runs.approve');
    });

    Route::middleware('role:HR Admin|HR Manager|Accountant|Auditor|Department Manager|Team Lead|Executive')->group(function (): void {
        // Route::resource() only dot-converts a name that already contains dots — a bare
        // slash like "payroll/runs" does NOT auto-namespace the route names (it silently
        // names them "runs.index"/"runs.show"), so the base name must be set explicitly.
        Route::resource('payroll/runs', PayrollRunController::class)
            ->only(['index', 'show'])
            ->parameters(['runs' => 'payrollRun'])
            ->names('payroll.runs');
    });

    // Same create-before-wildcard ordering requirement as above.
    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::resource('recruitment/requisitions', JobRequisitionController::class)
            ->only(['create', 'store', 'edit', 'update'])
            ->parameters(['requisitions' => 'jobRequisition'])
            ->names('recruitment.requisitions');
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Auditor|Executive')->group(function (): void {
        Route::resource('recruitment/requisitions', JobRequisitionController::class)
            ->only(['index', 'show'])
            ->parameters(['requisitions' => 'jobRequisition'])
            ->names('recruitment.requisitions');
    });

    Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
        Route::scopeBindings()->group(function (): void {
            Route::post('recruitment/requisitions/{jobRequisition}/candidates', [CandidateController::class, 'store'])
                ->name('recruitment.requisitions.candidates.store');
            Route::post('recruitment/requisitions/{jobRequisition}/candidates/{candidate}/stage', [CandidateController::class, 'updateStage'])
                ->name('recruitment.requisitions.candidates.stage');
        });
    });

    // Same create-before-wildcard ordering requirement as above.
    Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
        Route::get('performance/cycles/create', [PerformanceReviewCycleController::class, 'create'])->name('performance.cycles.create');
        Route::post('performance/cycles', [PerformanceReviewCycleController::class, 'store'])->name('performance.cycles.store');
    });

    Route::middleware('role:HR Admin|HR Manager|Department Manager|Team Lead')->group(function (): void {
        Route::resource('performance/cycles', PerformanceReviewCycleController::class)
            ->only(['index', 'show'])
            ->parameters(['cycles' => 'performanceReviewCycle'])
            ->names('performance.cycles');
    });

    Route::scopeBindings()->group(function (): void {
        // Any authenticated employee may submit their own self-review — ownership is
        // enforced inside SubmitSelfReview itself, not by route-level role gating.
        Route::post('performance/cycles/{cycle}/reviews/{review}/self', [PerformanceReviewController::class, 'submitSelf'])
            ->name('performance.reviews.submit-self');

        Route::middleware('role:HR Admin|HR Manager|Department Manager|Team Lead')->group(function (): void {
            Route::post('performance/cycles/{cycle}/reviews/{review}/manager', [PerformanceReviewController::class, 'submitManager'])
                ->name('performance.reviews.submit-manager');
        });
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
