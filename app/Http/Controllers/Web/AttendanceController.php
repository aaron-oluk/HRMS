<?php

namespace App\Http\Controllers\Web;

use App\Actions\Attendance\ApproveOvertimeRequest;
use App\Actions\Attendance\ClockIn;
use App\Actions\Attendance\ClockOut;
use App\Actions\Attendance\RejectOvertimeRequest;
use App\Actions\Attendance\SubmitOvertimeRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClockRequest;
use App\Http\Requests\OvertimeApprovalRequest;
use App\Http\Requests\OvertimeRequestRequest;
use App\Models\AttendanceDay;
use App\Models\OvertimeRequest;
use App\Support\Approvals\TeamScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function index(Request $request, TeamScope $teamScope): View
    {
        $employee = $request->user()->employee;

        $myTimesheet = $employee
            ? $employee->attendanceDays()
                ->whereDate('date', '>=', now()->startOfWeek()->toDateString())
                ->whereDate('date', '<=', now()->endOfWeek()->toDateString())
                ->orderBy('date')
                ->get()
            : collect();

        $todayEvent = $employee
            ? $employee->clockEvents()->whereDate('occurred_at', now()->toDateString())->latest('occurred_at')->first()
            : null;

        $myOvertimeRequests = $employee
            ? $employee->overtimeRequests()->latest('date')->get()
            : collect();

        $teamToday = collect();
        if ($request->user()->can('attendance.view-team')) {
            $query = AttendanceDay::with('employee')->whereDate('date', now()->toDateString());
            $teamToday = $teamScope->scopeToTeam($query, $request->user())->get();
        }

        $overtimeApprovals = collect();
        if ($request->user()->can('attendance.approve-overtime')) {
            $query = OvertimeRequest::with('employee')->pending()->latest('date');
            $overtimeApprovals = $teamScope->scopeToTeam($query, $request->user())->get();
        }

        return view('attendance.index', [
            'clockedIn' => $todayEvent?->type === 'clock_in',
            'myTimesheet' => $myTimesheet,
            'myOvertimeRequests' => $myOvertimeRequests,
            'teamToday' => $teamToday,
            'overtimeApprovals' => $overtimeApprovals,
            'canRequestOvertime' => $employee !== null,
        ]);
    }

    public function clockIn(ClockRequest $request, ClockIn $clockIn): RedirectResponse
    {
        $clockIn->handle($request->user()->employee, $request->validated());

        return redirect()->route('attendance.index')->with('status', 'Clocked in.');
    }

    public function clockOut(ClockRequest $request, ClockOut $clockOut): RedirectResponse
    {
        $clockOut->handle($request->user()->employee, $request->validated());

        return redirect()->route('attendance.index')->with('status', 'Clocked out.');
    }

    public function storeOvertime(OvertimeRequestRequest $request, SubmitOvertimeRequest $submitOvertimeRequest): RedirectResponse
    {
        $submitOvertimeRequest->handle($request->user()->employee, $request->validated());

        return redirect()->route('attendance.index')->with('status', 'Overtime request submitted.');
    }

    public function approveOvertime(Request $request, OvertimeRequest $overtimeRequest, ApproveOvertimeRequest $approveOvertimeRequest): RedirectResponse
    {
        Gate::authorize('attendance.approve-overtime');

        $approveOvertimeRequest->handle($overtimeRequest, $request->user());

        return redirect()->route('attendance.index')->with('status', 'Overtime approved.');
    }

    public function rejectOvertime(OvertimeApprovalRequest $request, OvertimeRequest $overtimeRequest, RejectOvertimeRequest $rejectOvertimeRequest): RedirectResponse
    {
        $rejectOvertimeRequest->handle($overtimeRequest, $request->user(), $request->validated('reason'));

        return redirect()->route('attendance.index')->with('status', 'Overtime rejected.');
    }
}
