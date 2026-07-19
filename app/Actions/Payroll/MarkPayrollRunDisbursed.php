<?php

namespace App\Actions\Payroll;

use App\Models\PayrollRun;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Validation\ValidationException;

class MarkPayrollRunDisbursed
{
    public function handle(PayrollRun $payrollRun, User $actor): PayrollRun
    {
        if ($payrollRun->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only an approved payroll run can be marked as disbursed.',
            ]);
        }

        $payrollRun->update([
            'status' => 'disbursed',
            'disbursed_by' => $actor->id,
            'disbursed_at' => now(),
        ]);

        $payrollRun->lines()->with('employee.user')->get()->each(
            fn ($line) => $line->employee->user?->notify(new GenericNotification(
                title: 'Payslip available',
                message: "Your payslip for {$payrollRun->period_month->format('F Y')} is ready to view.",
                icon: 'bx-receipt',
                url: route('profile.edit'),
            ))
        );

        return $payrollRun;
    }
}
