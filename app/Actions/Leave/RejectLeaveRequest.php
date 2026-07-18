<?php

namespace App\Actions\Leave;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class RejectLeaveRequest
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(LeaveRequest $leaveRequest, User $actor, ?string $reason = null): LeaveRequest
    {
        if (! $this->teamScope->canActOn($leaveRequest->employee_id, $actor)) {
            throw new AuthorizationException('You may only reject requests from your direct reports.');
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $leaveRequest->employee->user?->notify(new GenericNotification(
            title: 'Leave request rejected',
            message: "Your {$leaveRequest->leaveType->name} request for {$leaveRequest->start_date->toFormattedDateString()} was rejected".($reason ? ": {$reason}" : '.'),
            icon: 'bxs-x-circle',
            url: route('leave.index'),
        ));

        return $leaveRequest;
    }
}
