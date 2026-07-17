<?php

namespace App\Actions\Attendance;

use App\Models\Employee;
use App\Models\OvertimeRequest;

class SubmitOvertimeRequest
{
    /**
     * @param  array{date: string, hours: float, reason?: string|null}  $data
     */
    public function handle(Employee $employee, array $data): OvertimeRequest
    {
        return $employee->overtimeRequests()->create([
            'date' => $data['date'],
            'hours' => $data['hours'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);
    }
}
