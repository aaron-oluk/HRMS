<?php

namespace App\Support\Leave;

use App\Models\Employee;
use App\Models\LeaveType;

class LeaveBalance
{
    public function entitled(Employee $employee, LeaveType $leaveType, int $year): float
    {
        return (float) $leaveType->default_days_per_year;
    }

    public function used(Employee $employee, LeaveType $leaveType, int $year): float
    {
        return (float) $employee->leaveRequests()
            ->where('leave_type_id', $leaveType->id)
            ->approved()
            ->forYear($year)
            ->sum('days');
    }

    public function available(Employee $employee, LeaveType $leaveType, int $year): float
    {
        return $this->entitled($employee, $leaveType, $year) - $this->used($employee, $leaveType, $year);
    }
}
