<?php

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
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\SecuritySettingsController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/settings/security', [SecuritySettingsController::class, 'edit'])->name('security.edit');

    Route::resource('entities', EntityController::class)->except('show');
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('positions', PositionController::class)->except('show');
    Route::resource('grades', GradeController::class)->except('show');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::resource('employees', EmployeeController::class)->except('destroy');

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
