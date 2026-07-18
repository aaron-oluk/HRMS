<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Attendance\ClockIn;
use App\Actions\Attendance\ClockOut;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClockRequest;
use App\Http\Resources\AttendanceDayResource;
use App\Models\AttendanceDay;
use App\Support\Approvals\TeamScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function myTimesheet(Request $request)
    {
        $days = $request->user()->employee->attendanceDays()
            ->whereDate('date', '>=', now()->startOfWeek()->toDateString())
            ->whereDate('date', '<=', now()->endOfWeek()->toDateString())
            ->orderBy('date')
            ->get();

        return AttendanceDayResource::collection($days);
    }

    public function teamToday(Request $request, TeamScope $teamScope)
    {
        Gate::authorize('attendance.view-team');

        $query = AttendanceDay::with('employee')->whereDate('date', now()->toDateString());

        return AttendanceDayResource::collection($teamScope->scopeToTeam($query, $request->user())->get());
    }

    public function clockIn(ClockRequest $request, ClockIn $clockIn)
    {
        $clockIn->handle($request->user()->employee, [...$request->validated(), 'source' => 'api']);

        return response()->noContent(201);
    }

    public function clockOut(ClockRequest $request, ClockOut $clockOut)
    {
        $clockOut->handle($request->user()->employee, [...$request->validated(), 'source' => 'api']);

        return response()->noContent();
    }
}
