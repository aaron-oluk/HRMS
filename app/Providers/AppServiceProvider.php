<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Support\Approvals\TeamScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view): void {
            $user = Auth::user();

            $pendingLeaveApprovalsCount = 0;
            $pendingOvertimeApprovalsCount = 0;

            if ($user?->can('leave.approve')) {
                $query = LeaveRequest::pending();
                $pendingLeaveApprovalsCount = app(TeamScope::class)->scopeToTeam($query, $user)->count();
            }

            if ($user?->can('attendance.approve-overtime')) {
                $query = OvertimeRequest::pending();
                $pendingOvertimeApprovalsCount = app(TeamScope::class)->scopeToTeam($query, $user)->count();
            }

            $view->with('pendingLeaveApprovalsCount', $pendingLeaveApprovalsCount);
            $view->with('pendingOvertimeApprovalsCount', $pendingOvertimeApprovalsCount);
        });
    }
}
