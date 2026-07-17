<?php

namespace App\Support\Approvals;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeamScope
{
    /**
     * Whether $actor may approve/reject a request submitted by the employee with $employeeId.
     * HR Admin (or anyone with employees.manage) is unscoped; everyone else may only act
     * on their own direct reports (Employment.reporting_to_employee_id).
     */
    public function canActOn(int $employeeId, User $actor): bool
    {
        if ($actor->can('employees.manage')) {
            return true;
        }

        return $actor->employee?->directReportEmployments()
            ->where('employee_id', $employeeId)
            ->exists() ?? false;
    }

    /**
     * Constrain a query (by its employee-id column) to $actor's direct reports, unless
     * $actor is unscoped (employees.manage), in which case the query is left untouched.
     */
    public function scopeToTeam(Builder $query, User $actor, string $employeeIdColumn = 'employee_id'): Builder
    {
        if ($actor->can('employees.manage')) {
            return $query;
        }

        $teamIds = $actor->employee?->directReportEmployments()->pluck('employee_id') ?? collect();

        return $query->whereIn($employeeIdColumn, $teamIds);
    }
}
