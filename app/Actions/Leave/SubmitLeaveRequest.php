<?php

namespace App\Actions\Leave;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Support\Leave\LeaveBalance;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SubmitLeaveRequest
{
    public function __construct(protected LeaveBalance $leaveBalance) {}

    /**
     * @param  array{leave_type_id: int, start_date: string, end_date: string, reason?: string|null}  $data
     */
    public function handle(Employee $employee, array $data): LeaveRequest
    {
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $days = $this->countLeaveDays($employee, $start, $end);

        if ($days <= 0) {
            throw ValidationException::withMessages([
                'end_date' => 'The selected range contains no working days.',
            ]);
        }

        $available = $this->leaveBalance->available($employee, $leaveType, $start->year);

        if ($days > $available) {
            throw ValidationException::withMessages([
                'start_date' => "Only {$available} day(s) available for {$leaveType->name}.",
            ]);
        }

        return $employee->leaveRequests()->create([
            'leave_type_id' => $leaveType->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => $leaveType->requires_approval ? 'pending' : 'approved',
            'approved_at' => $leaveType->requires_approval ? null : now(),
        ]);
    }

    protected function countLeaveDays(Employee $employee, Carbon $start, Carbon $end): int
    {
        $holidays = Holiday::where('entity_id', $employee->entity_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->all();

        $days = 0;

        foreach (Carbon::parse($start)->daysUntil($end) as $date) {
            if (! $date->isWeekend() && ! in_array($date->toDateString(), $holidays, true)) {
                $days++;
            }
        }

        return $days;
    }
}
