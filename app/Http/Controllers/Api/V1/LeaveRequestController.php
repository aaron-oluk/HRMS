<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Leave\SubmitLeaveRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->employee->leaveRequests()
            ->with('leaveType')
            ->latest('start_date')
            ->paginate(25);

        return LeaveRequestResource::collection($requests);
    }

    public function store(LeaveRequestRequest $request, SubmitLeaveRequest $submitLeaveRequest)
    {
        $leaveRequest = $submitLeaveRequest->handle($request->user()->employee, $request->validated());

        return LeaveRequestResource::make($leaveRequest)->response()->setStatusCode(201);
    }
}
