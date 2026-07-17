<?php

namespace App\Actions\Attendance;

use App\Models\ClockEvent;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ClockIn
{
    public function __construct(protected RecomputeAttendanceDay $recomputeAttendanceDay) {}

    /**
     * @param  array{latitude?: float|null, longitude?: float|null, idempotency_key?: string|null}  $data
     */
    public function handle(Employee $employee, array $data = []): ClockEvent
    {
        if ($this->hasOpenClockIn($employee)) {
            throw ValidationException::withMessages([
                'clock_in' => 'You are already clocked in.',
            ]);
        }

        $event = $employee->clockEvents()->create([
            'type' => 'clock_in',
            'occurred_at' => now(),
            'source' => $data['source'] ?? 'web',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        $this->recomputeAttendanceDay->handle($employee, Carbon::now());

        return $event;
    }

    public function hasOpenClockIn(Employee $employee): bool
    {
        $lastEvent = $employee->clockEvents()
            ->whereDate('occurred_at', now()->toDateString())
            ->latest('occurred_at')
            ->first();

        return $lastEvent?->type === 'clock_in';
    }
}
