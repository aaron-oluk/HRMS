<?php

namespace App\Actions\Payroll;

use App\Models\PayrollRun;
use Illuminate\Validation\ValidationException;

class SubmitPayrollRunForApproval
{
    public function handle(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Only a draft payroll run can be submitted for approval.',
            ]);
        }

        $payrollRun->update(['status' => 'pending_approval']);

        return $payrollRun;
    }
}
