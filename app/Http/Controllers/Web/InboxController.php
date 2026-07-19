<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceReview;
use App\Support\Approvals\TeamScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request, TeamScope $teamScope): View
    {
        $user = $request->user();
        $items = collect();

        if ($user->can('leave.approve')) {
            $teamScope->scopeToTeam(LeaveRequest::with('employee', 'leaveType')->pending(), $user)
                ->get()
                ->each(fn (LeaveRequest $r) => $items->push([
                    'type' => 'Leave request',
                    'icon' => 'bx-calendar-check',
                    'employee' => $r->employee->fullName(),
                    'summary' => "{$r->leaveType->name} · {$r->days} day(s) from {$r->start_date->toFormattedDateString()}",
                    'submitted_at' => $r->created_at,
                    'approve_route' => route('leave.approve', $r),
                    'reject_route' => route('leave.reject', $r),
                ]));
        }

        if ($user->can('attendance.approve-overtime')) {
            $teamScope->scopeToTeam(OvertimeRequest::with('employee')->pending(), $user)
                ->get()
                ->each(fn (OvertimeRequest $r) => $items->push([
                    'type' => 'Overtime request',
                    'icon' => 'bx-time-five',
                    'employee' => $r->employee->fullName(),
                    'summary' => "{$r->hours} hour(s) on {$r->date->toFormattedDateString()}",
                    'submitted_at' => $r->created_at,
                    'approve_route' => route('attendance.overtime.approve', $r),
                    'reject_route' => route('attendance.overtime.reject', $r),
                ]));
        }

        if ($user->can('performance.review')) {
            $teamScope->scopeToTeam(PerformanceReview::with('employee', 'cycle')->awaitingManagerReview(), $user)
                ->get()
                ->each(fn (PerformanceReview $r) => $items->push([
                    'type' => 'Performance review',
                    'icon' => 'bx-line-chart',
                    'employee' => $r->employee->fullName(),
                    'summary' => "Self-review submitted for {$r->cycle->name}",
                    'submitted_at' => $r->self_submitted_at,
                    'action_route' => route('performance.cycles.show', $r->cycle),
                    'action_label' => 'Review',
                ]));
        }

        $items = $items->sortBy('submitted_at')->values();

        return view('inbox.index', ['items' => $items]);
    }
}
