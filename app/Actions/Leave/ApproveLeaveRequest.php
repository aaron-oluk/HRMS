<?php

namespace App\Actions\Leave;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class ApproveLeaveRequest
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(LeaveRequest $leaveRequest, User $actor): LeaveRequest
    {
        if (! $this->teamScope->canActOn($leaveRequest->employee_id, $actor)) {
            throw new AuthorizationException('You may only approve requests from your direct reports.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $leaveRequest;
    }
}
