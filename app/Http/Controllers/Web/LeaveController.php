<?php

namespace App\Http\Controllers\Web;

use App\Actions\Leave\ApproveLeaveRequest;
use App\Actions\Leave\RejectLeaveRequest;
use App\Actions\Leave\SubmitLeaveRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveApprovalRequest;
use App\Http\Requests\LeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Support\Approvals\TeamScope;
use App\Support\Leave\LeaveBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeaveController extends Controller
{
    public function index(Request $request, LeaveBalance $leaveBalance, TeamScope $teamScope): View
    {
        $employee = $request->user()->employee;

        $myRequests = $employee
            ? $employee->leaveRequests()->with('leaveType')->latest('start_date')->get()
            : collect();

        $balances = collect();
        if ($employee) {
            $balances = LeaveType::where('entity_id', $employee->entity_id)
                ->where('status', 'active')
                ->get()
                ->map(fn (LeaveType $type) => [
                    'type' => $type,
                    'available' => $leaveBalance->available($employee, $type, now()->year),
                ]);
        }

        $teamRequests = collect();
        if ($request->user()->can('leave.approve')) {
            $query = LeaveRequest::with('employee', 'leaveType')->pending()->latest('start_date');
            $teamRequests = $teamScope->scopeToTeam($query, $request->user())->get();
        }

        return view('leave.index', [
            'leaveTypes' => $employee ? LeaveType::where('entity_id', $employee->entity_id)->where('status', 'active')->get() : collect(),
            'myRequests' => $myRequests,
            'balances' => $balances,
            'teamRequests' => $teamRequests,
        ]);
    }

    public function store(LeaveRequestRequest $request, SubmitLeaveRequest $submitLeaveRequest): RedirectResponse
    {
        $submitLeaveRequest->handle($request->user()->employee, $request->validated());

        return redirect()->route('leave.index')->with('status', 'Time off request submitted.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, ApproveLeaveRequest $approveLeaveRequest): RedirectResponse
    {
        Gate::authorize('leave.approve');

        $approveLeaveRequest->handle($leaveRequest, $request->user());

        return redirect()->route('leave.index')->with('status', 'Request approved.');
    }

    public function reject(LeaveApprovalRequest $request, LeaveRequest $leaveRequest, RejectLeaveRequest $rejectLeaveRequest): RedirectResponse
    {
        $rejectLeaveRequest->handle($leaveRequest, $request->user(), $request->validated('reason'));

        return redirect()->route('leave.index')->with('status', 'Request rejected.');
    }
}
