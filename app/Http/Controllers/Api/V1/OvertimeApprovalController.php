<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Attendance\ApproveOvertimeRequest;
use App\Actions\Attendance\RejectOvertimeRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeApprovalRequest;
use App\Http\Resources\OvertimeRequestResource;
use App\Models\OvertimeRequest;
use App\Support\Approvals\TeamScope;
use Illuminate\Http\Request;

class OvertimeApprovalController extends Controller
{
    public function index(Request $request, TeamScope $teamScope)
    {
        $query = OvertimeRequest::with('employee')->pending()->latest('date');

        return OvertimeRequestResource::collection($teamScope->scopeToTeam($query, $request->user())->paginate(25));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest, ApproveOvertimeRequest $approveOvertimeRequest)
    {
        return OvertimeRequestResource::make($approveOvertimeRequest->handle($overtimeRequest, $request->user()));
    }

    public function reject(OvertimeApprovalRequest $request, OvertimeRequest $overtimeRequest, RejectOvertimeRequest $rejectOvertimeRequest)
    {
        return OvertimeRequestResource::make(
            $rejectOvertimeRequest->handle($overtimeRequest, $request->user(), $request->validated('reason'))
        );
    }
}
