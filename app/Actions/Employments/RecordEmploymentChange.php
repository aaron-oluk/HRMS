<?php

namespace App\Actions\Employments;

use App\Models\Employee;
use App\Models\Employment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecordEmploymentChange
{
    /**
     * Close the employee's current active employment (if any) and open a new
     * effective-dated one. Salary history is never overwritten.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Employee $employee, array $data): Employment
    {
        return DB::transaction(function () use ($employee, $data): Employment {
            $current = $employee->currentEmployment()->first();

            if ($current) {
                $current->update([
                    'effective_to' => Carbon::parse($data['effective_from'])->subDay(),
                    'status' => 'superseded',
                ]);
            }

            return $employee->employments()->create($data);
        });
    }
}
