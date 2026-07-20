<?php

namespace App\Actions\Performance;

use App\Models\Employee;
use App\Models\PerformanceGoal;
use Illuminate\Auth\Access\AuthorizationException;

class UpsertGoal
{
    /**
     * @param  array{title: string, description?: string|null, target_value?: float|null, current_value?: float|null, unit?: string|null, status: string, due_date?: string|null, performance_review_cycle_id?: int|null}  $data
     */
    public function handle(Employee $employee, array $data, ?PerformanceGoal $goal = null): PerformanceGoal
    {
        if ($goal && $goal->employee_id !== $employee->id) {
            throw new AuthorizationException('You may only manage your own goals.');
        }

        if ($goal) {
            $goal->update($data);

            return $goal;
        }

        return $employee->performanceGoals()->create($data);
    }
}
