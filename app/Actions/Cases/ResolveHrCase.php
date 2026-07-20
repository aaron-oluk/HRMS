<?php

namespace App\Actions\Cases;

use App\Models\HrCase;
use App\Notifications\GenericNotification;

class ResolveHrCase
{
    public function handle(HrCase $case): HrCase
    {
        $case->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $case->employee->user?->notify(new GenericNotification(
            title: 'Your case was resolved: '.$case->subject,
            message: 'HR has marked your case as resolved. Reply if you need anything further.',
            icon: 'bx-support',
            url: route('cases.show', $case),
        ));

        return $case;
    }
}
