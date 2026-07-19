<?php

namespace App\Actions\Performance;

use App\Models\Employee;
use App\Models\PerformanceReviewCycle;
use Illuminate\Support\Facades\DB;

class CreatePerformanceReviewCycle
{
    /**
     * @param  array{name: string, start_date: string, end_date: string}  $data
     */
    public function handle(array $data): PerformanceReviewCycle
    {
        return DB::transaction(function () use ($data) {
            $cycle = PerformanceReviewCycle::create($data + ['status' => 'active']);

            Employee::where('status', 'active')->get()->each(
                fn (Employee $employee) => $cycle->reviews()->create([
                    'employee_id' => $employee->id,
                    'reviewer_employee_id' => $employee->currentEmployment?->reporting_to_employee_id,
                ])
            );

            return $cycle;
        });
    }
}
