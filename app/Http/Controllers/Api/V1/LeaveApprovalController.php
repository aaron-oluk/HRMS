<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Leave\ApproveLeaveRequest;
use App\Actions\Leave\RejectLeaveRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveApprovalRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Support\Approvals\TeamScope;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function index(Request $request, TeamScope $teamScope)
    {
        $query = LeaveRequest::with('employee', 'leaveType')->pending()->latest('start_date');

        return LeaveRequestResource::collection($teamScope->scopeToTeam($query, $request->user())->paginate(25));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, ApproveLeaveRequest $approveLeaveRequest)
    {
        return LeaveRequestResource::make($approveLeaveRequest->handle($leaveRequest, $request->user()));
    }

    public function reject(LeaveApprovalRequest $request, LeaveRequest $leaveRequest, RejectLeaveRequest $rejectLeaveRequest)
    {
        return LeaveRequestResource::make(
            $rejectLeaveRequest->handle($leaveRequest, $request->user(), $request->validated('reason'))
        );
    }
}
