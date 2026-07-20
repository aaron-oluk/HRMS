<?php

namespace App\Actions\Performance;

use App\Models\OneOnOneMeeting;
use App\Models\User;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class LogOneOnOneNotes
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(OneOnOneMeeting $meeting, User $actor, string $notes): OneOnOneMeeting
    {
        if (! $this->teamScope->canActOn($meeting->employee_id, $actor)) {
            throw new AuthorizationException('You may only log notes for your own direct reports or department.');
        }

        $meeting->update([
            'notes' => $notes,
            'status' => 'completed',
        ]);

        return $meeting;
    }
}
