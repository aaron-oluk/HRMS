<?php

namespace App\Http\Controllers\Web;

use App\Actions\Employees\CreateEmployee;
use App\Actions\Employees\UpdateEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\LeaveType;
use App\Support\Audit\AccessAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        $employees = Employee::with('currentEmployment.position', 'entity')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('employee_number', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', ['employees' => $employees, 'search' => $search]);
    }

    public function create(): View
    {
        return view('employees.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(EmployeeRequest $request, CreateEmployee $createEmployee): RedirectResponse
    {
        $employee = $createEmployee->handle($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'entity',
            'employments' => fn ($query) => $query->with('department', 'position', 'grade', 'branch'),
            'currentEmployment.department',
            'currentEmployment.position',
            'documents',
            'bankAccounts',
            'mobileMoneyAccounts',
            'compensationItems',
            'notes',
        ]);

        $viewer = auth()->user();
        $visibleSensitiveFields = collect([
            'salary' => 'employees.view-salary',
            'identity-numbers' => 'employees.view-identity-numbers',
            'bank-details' => 'employees.view-bank-details',
        ])->filter(fn (string $permission) => $viewer->can($permission))->keys()->all();

        if ($visibleSensitiveFields !== []) {
            AccessAudit::sensitiveFieldViewed($employee, $viewer, $visibleSensitiveFields);
        }

        return view('employees.show', [
            'employee' => $employee,
            'leaveBalances' => $this->leaveBalances($employee),
            'hoursThisWeek' => $this->hoursLoggedThisWeek($employee),
            'performanceTrend' => $this->performanceTrend($employee),
            'attendanceCalendar' => $this->attendanceCalendar($employee),
        ]);
    }

    /**
     * @return array{all: array{name: string, used: float, total: float}, types: list<array{name: string, used: float, total: float}>}
     */
    private function leaveBalances(Employee $employee): array
    {
        $usedByType = $employee->leaveRequests()
            ->approved()
            ->forYear(now()->year)
            ->selectRaw('leave_type_id, SUM(days) as used_days')
            ->groupBy('leave_type_id')
            ->pluck('used_days', 'leave_type_id');

        $types = LeaveType::where('entity_id', $employee->entity_id)
            ->where('status', 'active')
            ->orderBy('id')
            ->take(3)
            ->get()
            ->map(fn (LeaveType $type) => [
                'name' => $type->name,
                'used' => (float) ($usedByType[$type->id] ?? 0),
                'total' => (float) $type->default_days_per_year,
            ]);

        return [
            'all' => [
                'name' => 'All Leaves',
                'used' => round($types->sum('used'), 1),
                'total' => round($types->sum('total'), 1),
            ],
            'types' => $types->values()->all(),
        ];
    }

    /**
     * @return list<array{label: string, hours: float}>
     */
    private function hoursLoggedThisWeek(Employee $employee): array
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $attendanceByDate = $employee->attendanceDays()
            ->whereBetween('date', [$startOfWeek->toDateString(), $startOfWeek->copy()->addDays(6)->toDateString()])
            ->get()
            ->keyBy(fn ($day) => $day->date->toDateString());

        return collect(range(0, 6))->map(function (int $offset) use ($startOfWeek, $attendanceByDate) {
            $date = $startOfWeek->copy()->addDays($offset);
            $day = $attendanceByDate->get($date->toDateString());

            return [
                'label' => $date->format('D'),
                'hours' => $day ? round($day->worked_minutes / 60, 1) : 0.0,
            ];
        })->all();
    }

    /**
     * A percentage trend across review cycles (rating / 5 * 100), not literal calendar
     * months — the codebase's review cadence is per-cycle (e.g. "2026 H1"), not monthly.
     *
     * @return list<array{label: string, score: float}>
     */
    private function performanceTrend(Employee $employee): array
    {
        return $employee->performanceReviews()
            ->with('cycle')
            ->whereNotNull('manager_rating')
            ->get()
            ->sortBy(fn ($review) => $review->cycle->start_date)
            ->map(fn ($review) => [
                'label' => $review->cycle->name,
                'score' => round(((float) $review->manager_rating / 5) * 100, 1),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string> day-of-month => attendance status
     */
    private function attendanceCalendar(Employee $employee): array
    {
        $now = now();

        return $employee->attendanceDays()
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get()
            ->mapWithKeys(fn ($day) => [$day->date->day => $day->status])
            ->all();
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', ['employee' => $employee, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee): RedirectResponse
    {
        $updateEmployee->handle($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Employee updated.');
    }
}
