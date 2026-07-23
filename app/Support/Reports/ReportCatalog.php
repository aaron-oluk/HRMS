<?php

namespace App\Support\Reports;

use App\Models\User;

class ReportCatalog
{
    /**
     * @var array<string, array{title: string, description: string, icon: string, route: string, category: string}>
     */
    public const REPORTS = [
        'headcount' => [
            'title' => 'Headcount by department',
            'description' => 'Active employee count per department.',
            'icon' => 'bx-group',
            'route' => 'reports.headcount-by-department',
            'category' => 'Workforce',
        ],
        'recruitment' => [
            'title' => 'Recruitment pipeline',
            'description' => 'Candidates by stage, roles by status.',
            'icon' => 'bx-briefcase-alt-2',
            'route' => 'reports.recruitment-pipeline',
            'category' => 'Recruitment',
        ],
        'leave' => [
            'title' => 'Leave utilization',
            'description' => 'Entitled vs. used leave days per employee.',
            'icon' => 'bx-calendar-check',
            'route' => 'reports.leave-utilization',
            'category' => 'Workforce',
        ],
        'attendance' => [
            'title' => 'Attendance summary',
            'description' => 'Worked hours per employee over a date range.',
            'icon' => 'bx-time-five',
            'route' => 'reports.attendance-summary',
            'category' => 'Workforce',
        ],
        'payroll' => [
            'title' => 'Payroll cost summary',
            'description' => 'Gross/net pay and statutory totals per period.',
            'icon' => 'bx-receipt',
            'route' => 'reports.payroll-cost-summary',
            'category' => 'Finance',
        ],
    ];

    /**
     * Explicit priority order per role. Roles not listed here (e.g. Auditor) — and the rare
     * case of a user holding none of ROLE_PRECEDENCE — fall back to the plain registry order,
     * since a read-only audit role has no reason to bias toward one report over another.
     *
     * @var array<string, list<string>>
     */
    public const ROLE_ORDER = [
        'HR Admin' => ['headcount', 'recruitment', 'leave', 'attendance', 'payroll'],
        'HR Manager' => ['headcount', 'recruitment', 'leave', 'attendance', 'payroll'],
        'Executive' => ['headcount', 'payroll', 'recruitment', 'leave', 'attendance'],
        'Accountant' => ['payroll', 'headcount', 'leave', 'attendance', 'recruitment'],
    ];

    /**
     * Checked in this order for a user holding more than one of these roles.
     *
     * @var list<string>
     */
    private const ROLE_PRECEDENCE = ['HR Admin', 'HR Manager', 'Executive', 'Accountant'];

    /**
     * @return list<string>
     */
    public static function orderForUser(User $user): array
    {
        foreach (self::ROLE_PRECEDENCE as $role) {
            if ($user->hasRole($role)) {
                return self::ROLE_ORDER[$role];
            }
        }

        return self::keys();
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::REPORTS);
    }
}
