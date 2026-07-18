<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveTypeRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        return LeaveTypeResource::collection(LeaveType::latest()->paginate(25));
    }

    public function store(LeaveTypeRequest $request)
    {
        return LeaveTypeResource::make(LeaveType::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(LeaveType $leaveType)
    {
        return LeaveTypeResource::make($leaveType);
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType)
    {
        $leaveType->update($request->validated());

        return LeaveTypeResource::make($leaveType);
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();

        return response()->noContent();
    }
}
