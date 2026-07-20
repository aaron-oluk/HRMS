<?php

namespace App\Actions\Attendance;

use App\Models\OvertimeRequest;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class ApproveOvertimeRequest
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(OvertimeRequest $overtimeRequest, User $actor): OvertimeRequest
    {
        if (! $this->teamScope->canActOn($overtimeRequest->employee_id, $actor)) {
            throw new AuthorizationException('You may only approve requests from your direct reports.');
        }

        $overtimeRequest->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $overtimeRequest->employee->user?->notify(new GenericNotification(
            title: 'Overtime request approved',
            message: "Your {$overtimeRequest->hours}-hour overtime request for {$overtimeRequest->date->toFormattedDateString()} was approved.",
            icon: 'bxs-check-circle',
            url: route('attendance.index', absolute: false),
        ));

        return $overtimeRequest;
    }
}
