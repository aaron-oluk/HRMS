<?php

namespace App\Actions\Attendance;

use App\Models\ClockEvent;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ClockOut
{
    public function __construct(protected ClockIn $clockIn, protected RecomputeAttendanceDay $recomputeAttendanceDay) {}

    /**
     * @param  array{latitude?: float|null, longitude?: float|null, idempotency_key?: string|null}  $data
     */
    public function handle(Employee $employee, array $data = []): ClockEvent
    {
        if (! $this->clockIn->hasOpenClockIn($employee)) {
            throw ValidationException::withMessages([
                'clock_out' => 'You are not currently clocked in.',
            ]);
        }

        $event = $employee->clockEvents()->create([
            'type' => 'clock_out',
            'occurred_at' => now(),
            'source' => $data['source'] ?? 'web',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        $this->recomputeAttendanceDay->handle($employee, Carbon::now());

        return $event;
    }
}
