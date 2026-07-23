<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDay;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobRequisition;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollRunLine;
use App\Models\ReportFavorite;
use App\Support\Reports\ReportCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $order = ReportCatalog::orderForUser($user);
        $favoriteKeys = ReportFavorite::where('user_id', $user->id)->pluck('report_key');

        $reports = collect($order)->map(fn (string $key) => [
            'key' => $key,
            ...ReportCatalog::REPORTS[$key],
            'favorited' => $favoriteKeys->contains($key),
            'trend' => $this->trendFor($key),
        ]);

        return view('reports.index', [
            'favorites' => $reports->where('favorited', true)->values(),
            'reports' => $reports->where('favorited', false)->values(),
        ]);
    }

    /**
     * @return list<array{label: string, value: int|float}>
     */
    private function trendFor(string $key): array
    {
        return match ($key) {
            'headcount' => $this->headcountTrend(),
            'leave' => $this->leaveTrend(),
            'attendance' => $this->attendanceTrend(),
            'payroll' => $this->payrollTrend(),
            'recruitment' => $this->recruitmentTrend(),
        };
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function headcountTrend(): array
    {
        $monthEnds = collect(range(5, 0))->map(fn (int $i) => min(now(), now()->subMonths($i)->endOfMonth()));

        $selects = $monthEnds->map(fn (Carbon $date, int $i) => "count(distinct case when effective_from <= '{$date->toDateString()}' and (effective_to is null or effective_to >= '{$date->toDateString()}') then employee_id end) as m{$i}")->implode(', ');

        $row = Employment::where('status', 'active')->selectRaw($selects)->first();

        return $monthEnds->map(fn (Carbon $date, int $i) => [
            'label' => $date->format('M'),
            'value' => (int) $row->{"m{$i}"},
        ])->values()->all();
    }

    /**
     * Grouped in PHP rather than via a DB-specific date-truncation function (e.g. Postgres'
     * date_trunc), so this works identically across the app's Postgres database and the
     * SQLite database the test suite runs against.
     *
     * @return list<array{label: string, value: float}>
     */
    private function leaveTrend(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $totals = LeaveRequest::approved()
            ->where('start_date', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['start_date', 'days'])
            ->groupBy(fn ($row) => Carbon::parse($row->start_date)->format('Y-m'))
            ->map(fn ($group) => $group->sum('days'));

        return $months->map(fn (Carbon $month) => [
            'label' => $month->format('M'),
            'value' => (float) ($totals[$month->format('Y-m')] ?? 0),
        ])->all();
    }

    /**
     * @return list<array{label: string, value: float}>
     */
    private function attendanceTrend(): array
    {
        $rows = AttendanceDay::whereBetween('date', [today()->subDays(6), today()])
            ->selectRaw('date, sum(worked_minutes) as minutes')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $rows->map(fn ($row) => [
            'label' => Carbon::parse($row->date)->format('D'),
            'value' => round($row->minutes / 60, 1),
        ])->all();
    }

    /**
     * @return list<array{label: string, value: float}>
     */
    private function payrollTrend(): array
    {
        $rows = PayrollRunLine::query()
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_run_lines.payroll_run_id')
            ->selectRaw('payroll_runs.period_month, sum(payroll_run_lines.gross_pay) as gross_pay')
            ->groupBy('payroll_runs.period_month')
            ->orderByDesc('payroll_runs.period_month')
            ->limit(6)
            ->get()
            ->reverse();

        return $rows->map(fn ($row) => [
            'label' => Carbon::parse($row->period_month)->format('M'),
            'value' => (float) $row->gross_pay,
        ])->values()->all();
    }

    /**
     * Grouped in PHP for the same portability reason as leaveTrend() above.
     *
     * @return list<array{label: string, value: int}>
     */
    private function recruitmentTrend(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $totals = Candidate::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn ($row) => $row->created_at->format('Y-m'))
            ->map->count();

        return $months->map(fn (Carbon $month) => [
            'label' => $month->format('M'),
            'value' => (int) ($totals[$month->format('Y-m')] ?? 0),
        ])->all();
    }

    public function headcountByDepartment(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $rows = Employment::whereNull('effective_to')
            ->where('employments.status', 'active')
            ->join('departments', 'departments.id', '=', 'employments.department_id')
            ->where('departments.tenant_id', $tenantId)
            ->selectRaw('departments.id as department_id, departments.name as department, count(*) as headcount')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('headcount')
            ->get();

        if ($request->query('format') === 'csv') {
            return $this->toCsv('headcount-by-department', ['Department', 'Headcount'], $rows->map(fn ($r) => [$r->department, $r->headcount]));
        }

        $chartData = $rows->map(fn ($r) => [
            'label' => $r->department,
            'value' => $r->headcount,
            'href' => route('reports.headcount-by-department', ['department_id' => $r->department_id]),
        ])->all();

        $selectedDepartment = null;
        $departmentEmployees = collect();

        if ($request->filled('department_id')) {
            $selectedDepartment = Department::find($request->integer('department_id'));

            if ($selectedDepartment) {
                $departmentEmployees = Employee::whereHas(
                    'currentEmployment',
                    fn ($query) => $query->where('department_id', $selectedDepartment->id)
                )->with('currentEmployment.position')->orderBy('first_name')->get();
            }
        }

        return view('reports.headcount-by-department', [
            'rows' => $rows,
            'chartData' => $chartData,
            'selectedDepartment' => $selectedDepartment,
            'departmentEmployees' => $departmentEmployees,
        ]);
    }

    public function leaveUtilization(Request $request)
    {
        $year = (int) $request->query('year', now()->year);

        $rows = Employee::where('status', 'active')
            ->with('entity')
            ->get()
            ->map(function (Employee $employee) use ($year) {
                $entitled = LeaveType::where('entity_id', $employee->entity_id)->where('status', 'active')->sum('default_days_per_year');
                $used = LeaveRequest::where('employee_id', $employee->id)->approved()->forYear($year)->sum('days');

                return [
                    'employee' => $employee->fullName(),
                    'entitled' => (float) $entitled,
                    'used' => (float) $used,
                ];
            });

        if ($request->query('format') === 'csv') {
            return $this->toCsv('leave-utilization', ['Employee', 'Entitled', 'Used'], $rows->map(fn ($r) => [$r['employee'], $r['entitled'], $r['used']]));
        }

        return view('reports.leave-utilization', ['rows' => $rows, 'year' => $year]);
    }

    public function attendanceSummary(Request $request)
    {
        $start = $request->query('start_date', now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', now()->toDateString());

        $rows = Employee::where('status', 'active')
            ->get()
            ->map(function (Employee $employee) use ($start, $end) {
                $minutes = $employee->attendanceDays()
                    ->whereBetween('date', [$start, $end])
                    ->sum('worked_minutes');

                return [
                    'employee' => $employee->fullName(),
                    'hours' => round($minutes / 60, 1),
                ];
            })
            ->filter(fn ($r) => $r['hours'] > 0)
            ->values();

        if ($request->query('format') === 'csv') {
            return $this->toCsv('attendance-summary', ['Employee', 'Hours worked'], $rows->map(fn ($r) => [$r['employee'], $r['hours']]));
        }

        return view('reports.attendance-summary', ['rows' => $rows, 'start' => $start, 'end' => $end]);
    }

    public function payrollCostSummary(Request $request)
    {
        $rows = PayrollRunLine::query()
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_run_lines.payroll_run_id')
            ->selectRaw('payroll_runs.period_month, count(*) as headcount, sum(payroll_run_lines.gross_pay) as gross_pay, sum(payroll_run_lines.paye_amount) as paye, sum(payroll_run_lines.nssf_employee_amount) as nssf_employee, sum(payroll_run_lines.net_pay) as net_pay')
            ->groupBy('payroll_runs.period_month')
            ->orderByDesc('payroll_runs.period_month')
            ->get();

        if ($request->query('format') === 'csv') {
            return $this->toCsv('payroll-cost-summary', ['Period', 'Headcount', 'Gross pay', 'PAYE', 'NSSF (employee)', 'Net pay'], $rows->map(fn ($r) => [
                $r->period_month, $r->headcount, $r->gross_pay, $r->paye, $r->nssf_employee, $r->net_pay,
            ]));
        }

        return view('reports.payroll-cost-summary', ['rows' => $rows]);
    }

    public function recruitmentPipeline(Request $request)
    {
        $byStage = Candidate::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $byRequisitionStatus = JobRequisition::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        if ($request->query('format') === 'csv') {
            $rows = collect(Candidate::STATUSES)->map(fn ($status) => [ucfirst(str_replace('_', ' ', $status)), $byStage[$status] ?? 0]);

            return $this->toCsv('recruitment-pipeline', ['Stage', 'Candidates'], $rows);
        }

        $chartData = collect(Candidate::STATUSES)->map(fn ($status) => [
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'value' => $byStage[$status] ?? 0,
            'href' => route('reports.recruitment-pipeline', ['stage' => $status]),
        ])->all();

        $selectedStage = null;
        $stageCandidates = collect();

        if ($request->filled('stage') && in_array($request->query('stage'), Candidate::STATUSES, true)) {
            $selectedStage = $request->query('stage');
            $stageCandidates = Candidate::where('status', $selectedStage)->with('jobRequisition')->latest()->get();
        }

        return view('reports.recruitment-pipeline', [
            'byStage' => $byStage,
            'byRequisitionStatus' => $byRequisitionStatus,
            'chartData' => $chartData,
            'selectedStage' => $selectedStage,
            'stageCandidates' => $stageCandidates,
        ]);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  Collection<int, array<int, mixed>>  $rows
     */
    protected function toCsv(string $filename, array $headers, $rows): Response
    {
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}
