<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\TenantFeatureFlag;
use App\Models\Theme;
use App\Models\User;
use App\Support\Approvals\TeamScope;
use App\Support\Audit\AccessAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(fn (User $user, string $ability) => $user->is_super_admin ? true : null);

        Event::listen(Login::class, function (Login $event): void {
            AccessAudit::loginSucceeded($event->user);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AccessAudit::loginFailed($event->user, (string) ($event->credentials['email'] ?? ''));
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user) {
                AccessAudit::loggedOut($event->user);
            }
        });

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
            $view->with('recentNotifications', $user ? $user->notifications()->latest()->limit(8)->get() : collect());
            $view->with('unreadNotificationsCount', $user?->unreadNotifications()->count() ?? 0);

            $enabledModules = collect(TenantFeatureFlag::MODULES)
                ->mapWithKeys(fn (string $module) => [$module => $user?->tenant?->hasModule($module) ?? true]);
            $view->with('enabledModules', $enabledModules);

            $view->with('activeTheme', $user?->tenant?->activeTheme() ?? Theme::default());
        });
    }
}
