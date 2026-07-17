<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Support\Approvals\TeamScope;
use App\Support\Leave\LeaveBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke(Request $request, LeaveBalance $leaveBalance, TeamScope $teamScope): View
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $employee = $user->employee;

        $myLeaveBalance = null;
        if ($employee) {
            $myLeaveBalance = LeaveType::where('entity_id', $employee->entity_id)
                ->where('status', 'active')
                ->get()
                ->sum(fn (LeaveType $type) => $leaveBalance->available($employee, $type, now()->year));
        }

        $pendingApprovalsCount = null;
        if ($user->can('leave.approve')) {
            $pendingApprovalsCount = $teamScope->scopeToTeam(LeaveRequest::pending(), $user)->count();
        }

        return view('dashboard', [
            'employeeCount' => $tenant?->employees()->count() ?? 0,
            'entityCount' => $tenant?->entities()->count() ?? 0,
            'onLeaveTodayCount' => $this->onLeaveTodayCount($user),
            'pendingApprovalsCount' => $pendingApprovalsCount,
            'myLeaveBalance' => $myLeaveBalance,
            'departmentHeadcount' => $this->departmentHeadcount($user, $tenant?->id),
            'activity' => $this->activity($user, $employee),
            'upcomingLeave' => $this->upcomingLeave($user),
        ]);
    }

    protected function onLeaveTodayCount($user): ?int
    {
        if (! $user->can('employees.view')) {
            return null;
        }

        return LeaveRequest::approved()
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->count();
    }

    protected function departmentHeadcount($user, ?int $tenantId): Collection
    {
        if (! $user->can('org.view') || ! $tenantId) {
            return collect();
        }

        return Employment::whereNull('effective_to')
            ->where('employments.status', 'active')
            ->join('departments', 'departments.id', '=', 'employments.department_id')
            ->where('departments.tenant_id', $tenantId)
            ->selectRaw('departments.name as department, count(*) as total')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->get();
    }

    protected function activity($user, ?Employee $employee): Collection
    {
        $activity = collect();
        $companyWide = $user->can('employees.view');

        if (! $companyWide && ! $employee) {
            return $activity;
        }

        $employeeScope = fn ($query) => $companyWide ? $query : $query->where('employee_id', $employee->id);

        $employeeScope(Employee::query())->latest()->limit(5)->get()->each(
            fn (Employee $e) => $activity->push([
                'icon' => 'bx-user-plus',
                'text' => "{$e->fullName()} joined as a new employee",
                'time' => $e->created_at,
            ])
        );

        $employeeScope(LeaveRequest::with('employee', 'leaveType')->latest())->limit(5)->get()->each(
            fn (LeaveRequest $r) => $activity->push([
                'icon' => 'bx-calendar-plus',
                'text' => "{$r->employee->fullName()} requested {$r->leaveType->name}",
                'time' => $r->created_at,
            ])
        );

        $employeeScope(LeaveRequest::with('employee', 'leaveType')->whereIn('status', ['approved', 'rejected'])->latest('approved_at'))
            ->limit(5)->get()->each(
                fn (LeaveRequest $r) => $activity->push([
                    'icon' => $r->status === 'approved' ? 'bx-check-circle' : 'bx-x-circle',
                    'text' => "{$r->employee->fullName()}'s {$r->leaveType->name} request was {$r->status}",
                    'time' => $r->approved_at,
                ])
            );

        return $activity->filter(fn ($item) => $item['time'] !== null)->sortByDesc('time')->take(8)->values();
    }

    protected function upcomingLeave($user): Collection
    {
        if (! $user->can('employees.view')) {
            return collect();
        }

        return LeaveRequest::with('employee', 'leaveType')
            ->approved()
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->limit(5)
            ->get();
    }
}
