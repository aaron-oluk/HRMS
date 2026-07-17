<?php

use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeBankAccountController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\EmployeeDocumentController;
use App\Http\Controllers\Api\V1\EmployeeMobileMoneyController;
use App\Http\Controllers\Api\V1\EmploymentController;
use App\Http\Controllers\Api\V1\EntityController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// Order matters: auth:sanctum must run first so Auth::shouldUse('sanctum') takes effect
// before IdentifyTenant reads request()->user(); SubstituteBindings (route model binding)
// must run after IdentifyTenant so tenant-scoped models are bound under the right scope.
// SubstituteBindings is removed from the global 'api' group in bootstrap/app.php so it
// can be re-added here, after IdentifyTenant, instead of before it.
Route::middleware(['auth:sanctum', IdentifyTenant::class, SubstituteBindings::class])->group(function (): void {
    Route::get('/user', fn () => request()->user());

    Route::apiResource('entities', EntityController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('grades', GradeController::class);
    Route::apiResource('users', UserController::class)->except('destroy');

    Route::apiResource('employees', EmployeeController::class)->except('destroy');

    Route::scopeBindings()->group(function (): void {
        Route::apiResource('employees.employments', EmploymentController::class)
            ->only(['index', 'store']);

        Route::apiResource('employees.documents', EmployeeDocumentController::class)
            ->parameters(['documents' => 'document'])
            ->only(['index', 'store', 'destroy']);

        Route::apiResource('employees.bank-accounts', EmployeeBankAccountController::class)
            ->parameters(['bank-accounts' => 'bankAccount'])
            ->only(['index', 'store', 'update', 'destroy']);

        Route::apiResource('employees.mobile-money', EmployeeMobileMoneyController::class)
            ->parameters(['mobile-money' => 'mobileMoney'])
            ->only(['index', 'store', 'update', 'destroy']);
    });
});
