<?php

use App\Http\Controllers\Web\Admin\SuperAdminController;
use App\Http\Controllers\Web\Admin\TenantController;
use App\Http\Controllers\Web\Admin\ThemeController;
use App\Http\Controllers\Web\AreaController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\CandidateController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeBankAccountController;
use App\Http\Controllers\Web\EmployeeCompensationController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeDocumentController;
use App\Http\Controllers\Web\EmployeeMobileMoneyController;
use App\Http\Controllers\Web\EmployeeNoteController;
use App\Http\Controllers\Web\EmploymentController;
use App\Http\Controllers\Web\EntityController;
use App\Http\Controllers\Web\GradeController;
use App\Http\Controllers\Web\HrCaseController;
use App\Http\Controllers\Web\ImpersonationController;
use App\Http\Controllers\Web\InboxController;
use App\Http\Controllers\Web\JobRequisitionController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\LeaveTypeController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OneOnOneController;
use App\Http\Controllers\Web\OrganizationController;
use App\Http\Controllers\Web\PayrollRunController;
use App\Http\Controllers\Web\PeerFeedbackController;
use App\Http\Controllers\Web\PerformanceGoalController;
use App\Http\Controllers\Web\PerformanceReviewController;
use App\Http\Controllers\Web\PerformanceReviewCycleController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\SignableDocumentController;
use App\Http\Controllers\Web\SignatureController;
use App\Http\Controllers\Web\SurveyController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! request()->user()) {
        return view('landing');
    }

    return redirect()->route(request()->user()->isPlatformAdmin() ? 'admin.tenants.index' : 'dashboard');
})->name('landing');

// The platform admin console: onboarding a new company (tenant) is deliberately not
// self-service (see App\Actions\Tenancy\CreateTenant) — reached only by a platform admin
// (Global super admin or a scoped Org Admin, see App\Models\User::isPlatformAdmin()), via
// its own middleware rather than the role:/permission: ones used below, since neither tier
// holds a tenant-scoped role at all.
Route::middleware(['auth', 'platform-admin'])->prefix('admin')->name('admin.')->group(function (): void {
    // Listing is shared by both tiers — Admin\TenantController@index filters the list to
    // assignedTenants for a scoped Org Admin, and shows everything for a Global admin.
    Route::resource('tenants', TenantController::class)->only(['index']);

    // Onboarding a new company and managing other platform admins are Global-only — an Org
    // Admin never creates tenants or grants/revokes anyone else's platform access.
    Route::middleware('super-admin')->group(function (): void {
        Route::resource('tenants', TenantController::class)->only(['create', 'store']);
        Route::resource('super-admins', SuperAdminController::class)->only(['index', 'create', 'store']);
        Route::resource('themes', ThemeController::class)->except(['show']);
    });

    // Everything below acts on one specific tenant — a Global admin always passes; an Org
    // Admin only passes for tenants explicitly assigned to them (see
    // App\Http\Middleware\EnsureAdminCanAccessTenant).
    Route::middleware('admin-tenant-access')->group(function (): void {
        Route::resource('tenants', TenantController::class)->only(['show', 'edit', 'update']);
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');
        Route::post('tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::put('tenants/{tenant}/modules', [TenantController::class, 'updateModules'])->name('tenants.modules.update');
    });
});

// Stopping impersonation deliberately sits outside the super-admin middleware group above —
// while impersonating, the authenticated user is the tenant's HR Admin, not a super admin.
Route::middleware('auth')->post('/stop-impersonating', [ImpersonationController::class, 'stop'])->name('impersonation.stop');

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
        Route::resource('areas', AreaController::class)->only(['index']);
        Route::resource('branches', BranchController::class)->only(['index']);
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('positions', PositionController::class)->only(['index']);
        Route::resource('grades', GradeController::class)->only(['index']);
        Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
        Route::resource('shifts', ShiftController::class)->only(['index']);
    });

    Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
        Route::resource('entities', EntityController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('areas', AreaController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('branches', BranchController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('departments', DepartmentController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('positions', PositionController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('grades', GradeController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::middleware('role:HR Admin')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('role:HR Admin|HR Manager')->prefix('organization')->name('organization.')->group(function (): void {
        Route::get('/', [OrganizationController::class, 'edit'])->name('edit');
        Route::put('general', [OrganizationController::class, 'updateGeneral'])->name('update-general');
        Route::put('statutory', [OrganizationController::class, 'updateStatutory'])->name('update-statutory');
        Route::put('structure', [OrganizationController::class, 'updateStructure'])->name('update-structure');
        Route::put('theme', [OrganizationController::class, 'updateTheme'])->name('update-theme');
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

    Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager|Branch Manager|Area Manager|Team Lead|Auditor|Accountant|Executive')->group(function (): void {
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
    Route::middleware('tenant-module:payroll')->group(function (): void {
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

        // Any authenticated employee may view their own payslips — ownership-based, no role gate.
        Route::get('payroll/my-payslips', [PayrollRunController::class, 'mine'])->name('payroll.my-payslips');
    });

    Route::middleware('tenant-module:recruitment')->group(function (): void {
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
    });

    Route::middleware('tenant-module:performance')->group(function (): void {
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

        // Any authenticated employee may view their own performance data — ownership-based, no role gate.
        Route::get('performance/my', [PerformanceReviewController::class, 'mine'])->name('performance.my');

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

        // Goals are self-owned (like self-reviews) — any employee manages their own, ownership
        // enforced inside UpsertGoal. Managers see their team's goals read-only via TeamScope
        // on the cycle show page, no separate route needed for that.
        Route::post('performance/goals', [PerformanceGoalController::class, 'store'])->name('performance.goals.store');
        Route::put('performance/goals/{goal}', [PerformanceGoalController::class, 'update'])->name('performance.goals.update');

        Route::middleware('role:HR Admin|HR Manager|Department Manager|Team Lead')->group(function (): void {
            Route::post('performance/reviews/{review}/feedback-requests', [PeerFeedbackController::class, 'store'])
                ->name('performance.feedback-requests.store');
        });

        // Submitting a nominated peer-feedback request is ownership-gated inside SubmitPeerFeedback
        // (arbitrary peer relationship, not a hierarchy TeamScope can express) — no role middleware.
        Route::post('performance/feedback-requests/{feedbackRequest}/submit', [PeerFeedbackController::class, 'submit'])
            ->name('performance.feedback-requests.submit');

        Route::middleware('role:HR Admin|HR Manager|Department Manager|Team Lead')->group(function (): void {
            Route::post('performance/one-on-ones', [OneOnOneController::class, 'store'])->name('performance.one-on-ones.store');
            Route::post('performance/one-on-ones/{meeting}/notes', [OneOnOneController::class, 'notes'])->name('performance.one-on-ones.notes');
        });
    });

    Route::middleware(['role:HR Admin|HR Manager|Auditor|Accountant|Executive', 'tenant-module:reports'])->prefix('reports')->name('reports.')->group(function (): void {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('headcount-by-department', [ReportController::class, 'headcountByDepartment'])->name('headcount-by-department');
        Route::get('leave-utilization', [ReportController::class, 'leaveUtilization'])->name('leave-utilization');
        Route::get('attendance-summary', [ReportController::class, 'attendanceSummary'])->name('attendance-summary');
        Route::get('payroll-cost-summary', [ReportController::class, 'payrollCostSummary'])->name('payroll-cost-summary');
        Route::get('recruitment-pipeline', [ReportController::class, 'recruitmentPipeline'])->name('recruitment-pipeline');
    });

    // Any authenticated user may upload/view their own reusable signature image — kept outside
    // the esignature module gate below since it has no separate nav gate of its own (the "My
    // Signature" link is always visible) and is harmless with no document-sending enabled.
    Route::post('profile/signature', [SignatureController::class, 'store'])->name('profile.signature.store');
    Route::get('profile/signature', [SignatureController::class, 'show'])->name('profile.signature.show');

    // 'signature' must also be registered before the documents/{document} wildcard below.
    Route::get('documents/signature', [SignatureController::class, 'edit'])->name('documents.signature.edit');

    Route::middleware('tenant-module:esignature')->group(function (): void {
        // Same create-before-wildcard ordering requirement as above.
        Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
            Route::get('documents/create', [SignableDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [SignableDocumentController::class, 'store'])->name('documents.store');
        });

        // index/show/page/download/sign are open to any authenticated user — the controller
        // itself restricts to the uploader, the signer, or an esignature.send holder, since
        // the same routes serve the sender's view and the signer's view.
        Route::get('documents', [SignableDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}', [SignableDocumentController::class, 'show'])->name('documents.show');
        Route::get('documents/{document}/page/{page}', [SignableDocumentController::class, 'page'])->name('documents.page');
        Route::get('documents/{document}/download', [SignableDocumentController::class, 'download'])->name('documents.download');
        Route::post('documents/{document}/sign', [SignableDocumentController::class, 'sign'])->name('documents.sign');
    });

    Route::middleware('tenant-module:engagement')->group(function (): void {
        // Same create-before-wildcard ordering requirement as above.
        Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
            Route::get('engagement/surveys/create', [SurveyController::class, 'create'])->name('engagement.surveys.create');
            Route::post('engagement/surveys', [SurveyController::class, 'store'])->name('engagement.surveys.store');
            Route::resource('engagement/surveys', SurveyController::class)
                ->only(['index'])
                ->parameters(['surveys' => 'survey'])
                ->names('engagement.surveys');
        });

        // Any authenticated employee can view a single survey — SurveyController::show()
        // renders the response form for anyone who hasn't answered yet, and only reveals
        // aggregate results to engagement.manage holders (the view itself gates that section).
        Route::resource('engagement/surveys', SurveyController::class)
            ->only(['show'])
            ->parameters(['surveys' => 'survey'])
            ->names('engagement.surveys');

        // Any authenticated employee may respond to a survey addressed to them — ownership
        // (and the one-response-per-employee rule) is enforced inside SubmitSurveyResponse.
        Route::post('engagement/surveys/{survey}/respond', [SurveyController::class, 'respond'])->name('engagement.surveys.respond');
    });

    Route::middleware('tenant-module:cases')->group(function (): void {
        // Cases: open to every authenticated user (any employee can submit/view/comment on
        // their own cases) — HR-wide visibility and assign/resolve are gated inside the
        // controller/action by the cases.manage permission, not by route middleware, since
        // the same routes serve both "my cases" and "all cases" depending on who's asking.
        Route::resource('cases', HrCaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('cases/{case}/comment', [HrCaseController::class, 'comment'])->name('cases.comment');

        Route::middleware('role:HR Admin|HR Manager|HR Specialist')->group(function (): void {
            Route::post('cases/{case}/assign', [HrCaseController::class, 'assign'])->name('cases.assign');
            Route::post('cases/{case}/resolve', [HrCaseController::class, 'resolve'])->name('cases.resolve');
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

        Route::middleware('role:HR Admin|HR Manager')->group(function (): void {
            Route::post('employees/{employee}/compensation-items', [EmployeeCompensationController::class, 'store'])
                ->name('employees.compensation-items.store');
            Route::delete('employees/{employee}/compensation-items/{compensationItem}', [EmployeeCompensationController::class, 'destroy'])
                ->name('employees.compensation-items.destroy');
        });

        Route::middleware('role:HR Admin|HR Manager|HR Specialist|Department Manager')->group(function (): void {
            Route::post('employees/{employee}/notes', [EmployeeNoteController::class, 'store'])
                ->name('employees.notes.store');
            Route::delete('employees/{employee}/notes/{note}', [EmployeeNoteController::class, 'destroy'])
                ->name('employees.notes.destroy');
        });
    });
});
