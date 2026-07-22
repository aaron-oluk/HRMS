<?php

namespace App\Support\Approvals;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeamScope
{
    /**
     * Roles with tenant-wide (unscoped) access to employee-scoped data.
     *
     * @var list<string>
     */
    protected const UNSCOPED_ROLES = [
        'HR Admin', 'HR Manager', 'HR Specialist', 'Auditor', 'Accountant', 'Executive',
    ];

    /**
     * Whether $actor may approve/reject a request submitted by the employee with $employeeId.
     */
    public function canActOn(int $employeeId, User $actor): bool
    {
        return match ($this->strategy($actor)) {
            'unscoped' => true,
            'department' => $this->departmentEmployeeIds($actor)->contains($employeeId),
            'branch' => $this->branchEmployeeIds($actor)->contains($employeeId),
            'area' => $this->areaEmployeeIds($actor)->contains($employeeId),
            default => $actor->employee?->directReportEmployments()
                ->where('employee_id', $employeeId)
                ->exists() ?? false,
        };
    }

    /**
     * Constrain a query (by its employee-id column) to $actor's scope: unscoped (tenant-wide),
     * their own department (Department Manager), their own branch (Branch Manager), every
     * branch in their own area (Area Manager), or their direct reports (Team Lead, default).
     */
    public function scopeToTeam(Builder $query, User $actor, string $employeeIdColumn = 'employee_id'): Builder
    {
        return match ($this->strategy($actor)) {
            'unscoped' => $query,
            'department' => $query->whereIn($employeeIdColumn, $this->departmentEmployeeIds($actor)),
            'branch' => $query->whereIn($employeeIdColumn, $this->branchEmployeeIds($actor)),
            'area' => $query->whereIn($employeeIdColumn, $this->areaEmployeeIds($actor)),
            default => $query->whereIn(
                $employeeIdColumn,
                $actor->employee?->directReportEmployments()->pluck('employee_id') ?? collect(),
            ),
        };
    }

    protected function strategy(User $actor): string
    {
        if ($actor->hasRole(self::UNSCOPED_ROLES)) {
            return 'unscoped';
        }

        if ($actor->hasRole('Department Manager')) {
            return 'department';
        }

        if ($actor->hasRole('Branch Manager')) {
            return 'branch';
        }

        if ($actor->hasRole('Area Manager')) {
            return 'area';
        }

        return 'direct-reports';
    }

    protected function departmentEmployeeIds(User $actor): Collection
    {
        $departmentId = $actor->employee?->currentEmployment?->department_id;

        if ($departmentId === null) {
            return collect();
        }

        return Employee::whereHas(
            'currentEmployment',
            fn ($query) => $query->where('department_id', $departmentId),
        )->pluck('id');
    }

    protected function branchEmployeeIds(User $actor): Collection
    {
        $branchId = $actor->employee?->currentEmployment?->branch_id;

        if ($branchId === null) {
            return collect();
        }

        return Employee::whereHas(
            'currentEmployment',
            fn ($query) => $query->where('branch_id', $branchId),
        )->pluck('id');
    }

    protected function areaEmployeeIds(User $actor): Collection
    {
        $areaId = $actor->employee?->currentEmployment?->branch?->area_id;

        if ($areaId === null) {
            return collect();
        }

        return Employee::whereHas(
            'currentEmployment.branch',
            fn ($query) => $query->where('area_id', $areaId),
        )->pluck('id');
    }
}
