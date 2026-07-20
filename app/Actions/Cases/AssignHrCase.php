<?php

namespace App\Actions\Cases;

use App\Models\HrCase;
use App\Models\User;
use App\Notifications\GenericNotification;

class AssignHrCase
{
    public function handle(HrCase $case, User $assignee): HrCase
    {
        $case->update([
            'assigned_to' => $assignee->id,
            'status' => $case->status === 'open' ? 'in_progress' : $case->status,
        ]);

        $assignee->notify(new GenericNotification(
            title: 'Case assigned to you: '.$case->subject,
            message: "A {$case->category} case was assigned to you.",
            icon: 'bx-support',
            url: route('cases.show', $case, absolute: false),
        ));

        return $case;
    }
}
