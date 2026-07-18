<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceDay;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Shift;
use Illuminate\Support\Carbon;

class RecomputeAttendanceDay
{
    /**
     * Late-arrival grace period, in minutes, before a present day is marked "late".
     */
    protected const GRACE_MINUTES = 15;

    public function handle(Employee $employee, Carbon $date): AttendanceDay
    {
        $events = $employee->clockEvents()
            ->whereBetween('occurred_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('occurred_at')
            ->get();

        $clockIn = $events->firstWhere('type', 'clock_in');
        $clockOut = $events->where('type', 'clock_out')->last();

        $workedMinutes = 0;
        if ($clockIn && $clockOut && $clockOut->occurred_at->greaterThan($clockIn->occurred_at)) {
            $workedMinutes = $clockIn->occurred_at->diffInMinutes($clockOut->occurred_at);
        }

        $shift = Shift::where('entity_id', $employee->entity_id)->where('status', 'active')->first();

        $payload = [
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $date->toDateString(),
            'shift_id' => $shift?->id,
            'clock_in_at' => $clockIn?->occurred_at,
            'clock_out_at' => $clockOut?->occurred_at,
            'worked_minutes' => $workedMinutes,
            'status' => $this->determineStatus($employee, $date, $clockIn, $shift),
        ];

        // Eloquent's date cast serializes for storage via getDateFormat() ('Y-m-d H:i:s')
        // regardless of cast type, so a plain updateOrCreate(['date' => 'Y-m-d']) lookup can
        // miss a row whose stored value picked up a time suffix. whereDate() compares only
        // the date part and is reliable on every driver.
        $attendanceDay = AttendanceDay::where('employee_id', $employee->id)
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($attendanceDay) {
            $attendanceDay->update($payload);

            return $attendanceDay;
        }

        return AttendanceDay::create($payload);
    }

    protected function determineStatus(Employee $employee, Carbon $date, mixed $clockIn, ?Shift $shift): string
    {
        $onLeave = $employee->leaveRequests()
            ->approved()
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();

        if ($onLeave) {
            return 'on_leave';
        }

        $isHoliday = Holiday::where('entity_id', $employee->entity_id)
            ->whereDate('date', $date->toDateString())
            ->exists();

        if ($isHoliday) {
            return 'holiday';
        }

        if (! $clockIn) {
            return 'absent';
        }

        if ($shift) {
            $shiftStart = $date->copy()->setTimeFromTimeString($shift->start_time)->addMinutes(self::GRACE_MINUTES);

            if ($clockIn->occurred_at->greaterThan($shiftStart)) {
                return 'late';
            }
        }

        return 'present';
    }
}
