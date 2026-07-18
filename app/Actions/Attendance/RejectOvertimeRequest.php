<?php

namespace App\Actions\Attendance;

use App\Models\OvertimeRequest;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class RejectOvertimeRequest
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(OvertimeRequest $overtimeRequest, User $actor, ?string $reason = null): OvertimeRequest
    {
        if (! $this->teamScope->canActOn($overtimeRequest->employee_id, $actor)) {
            throw new AuthorizationException('You may only reject requests from your direct reports.');
        }

        $overtimeRequest->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $overtimeRequest->employee->user?->notify(new GenericNotification(
            title: 'Overtime request rejected',
            message: "Your {$overtimeRequest->hours}-hour overtime request for {$overtimeRequest->date->toFormattedDateString()} was rejected".($reason ? ": {$reason}" : '.'),
            icon: 'bxs-x-circle',
            url: route('attendance.index'),
        ));

        return $overtimeRequest;
    }
}
