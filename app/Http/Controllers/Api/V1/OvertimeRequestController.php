<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Attendance\SubmitOvertimeRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRequest;
use App\Http\Resources\OvertimeRequestResource;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->employee->overtimeRequests()
            ->latest('date')
            ->paginate(25);

        return OvertimeRequestResource::collection($requests);
    }

    public function store(OvertimeRequestRequest $request, SubmitOvertimeRequest $submitOvertimeRequest)
    {
        $overtimeRequest = $submitOvertimeRequest->handle($request->user()->employee, $request->validated());

        return OvertimeRequestResource::make($overtimeRequest)->response()->setStatusCode(201);
    }
}
