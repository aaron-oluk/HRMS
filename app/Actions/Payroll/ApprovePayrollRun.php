<?php

namespace App\Actions\Payroll;

use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovePayrollRun
{
    public function handle(PayrollRun $payrollRun, User $actor): PayrollRun
    {
        if ($payrollRun->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'status' => 'Only a payroll run pending approval can be approved.',
            ]);
        }

        $payrollRun->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $payrollRun;
    }
}
