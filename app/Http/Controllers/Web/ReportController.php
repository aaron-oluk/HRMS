<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobRequisition;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollRunLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function headcountByDepartment(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $rows = Employment::whereNull('effective_to')
            ->where('employments.status', 'active')
            ->join('departments', 'departments.id', '=', 'employments.department_id')
            ->where('departments.tenant_id', $tenantId)
            ->selectRaw('departments.name as department, count(*) as headcount')
            ->groupBy('departments.name')
            ->orderByDesc('headcount')
            ->get();

        if ($request->query('format') === 'csv') {
            return $this->toCsv('headcount-by-department', ['Department', 'Headcount'], $rows->map(fn ($r) => [$r->department, $r->headcount]));
        }

        return view('reports.headcount-by-department', ['rows' => $rows]);
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

        return view('reports.recruitment-pipeline', ['byStage' => $byStage, 'byRequisitionStatus' => $byRequisitionStatus]);
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
