<?php

namespace App\Actions\Performance;

use App\Models\OneOnOneMeeting;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class ScheduleOneOnOne
{
    public function __construct(protected TeamScope $teamScope) {}

    /**
     * @param  array{scheduled_at: string, agenda?: string|null}  $data
     */
    public function handle(int $employeeId, User $actor, array $data): OneOnOneMeeting
    {
        if (! $this->teamScope->canActOn($employeeId, $actor)) {
            throw new AuthorizationException('You may only schedule 1-on-1s with your own direct reports or department.');
        }

        $meeting = OneOnOneMeeting::create([
            'employee_id' => $employeeId,
            'manager_employee_id' => $actor->employee->id,
            'scheduled_at' => $data['scheduled_at'],
            'agenda' => $data['agenda'] ?? null,
        ]);

        $meeting->employee->user?->notify(new GenericNotification(
            title: '1-on-1 scheduled',
            message: 'Your manager scheduled a 1-on-1 with you for '.$meeting->scheduled_at->format('d M Y, H:i').'.',
            icon: 'bx-calendar-event',
            url: route('performance.my', absolute: false),
        ));

        return $meeting;
    }
}
