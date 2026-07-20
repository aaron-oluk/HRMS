<?php

namespace App\Actions\Cases;

use App\Models\Employee;
use App\Models\HrCase;
use App\Models\User;
use App\Notifications\GenericNotification;

class SubmitHrCase
{
    /**
     * @param  array{category: string, subject: string, description: string}  $data
     */
    public function handle(Employee $employee, array $data): HrCase
    {
        $case = $employee->hrCases()->create($data);

        User::whereHas('roles', fn ($query) => $query->whereIn('name', ['HR Admin', 'HR Manager', 'HR Specialist']))
            ->where('tenant_id', $employee->tenant_id)
            ->get()
            ->each(fn (User $staff) => $staff->notify(new GenericNotification(
                title: 'New HR case: '.$case->subject,
                message: "{$employee->fullName()} submitted a new {$case->category} case.",
                icon: 'bx-support',
                url: route('cases.show', $case, absolute: false),
            )));

        return $case;
    }
}
