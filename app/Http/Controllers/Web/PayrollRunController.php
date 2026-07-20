<?php

namespace App\Http\Controllers\Web;

use App\Actions\Payroll\ApprovePayrollRun;
use App\Actions\Payroll\GeneratePayrollRun;
use App\Actions\Payroll\MarkPayrollRunDisbursed;
use App\Actions\Payroll\SubmitPayrollRunForApproval;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRunRequest;
use App\Models\Entity;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Support\Approvals\TeamScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function index(): View
    {
        $runs = PayrollRun::with('entity')->latest('period_month')->paginate(15);

        return view('payroll.runs.index', ['runs' => $runs]);
    }

    public function show(Request $request, PayrollRun $payrollRun, TeamScope $teamScope): View
    {
        $user = $request->user();
        $lineDetail = $user->can('payroll.view');

        $lines = collect();
        $summary = null;

        if ($lineDetail) {
            $lines = $payrollRun->lines()->with('employee')->get();
        } else {
            $lineQuery = $teamScope->scopeToTeam(PayrollRunLine::where('payroll_run_id', $payrollRun->id), $user);
            $summary = [
                'headcount' => (clone $lineQuery)->count(),
                'gross_pay' => (clone $lineQuery)->sum('gross_pay'),
                'net_pay' => (clone $lineQuery)->sum('net_pay'),
            ];
        }

        return view('payroll.runs.show', [
            'payrollRun' => $payrollRun,
            'lineDetail' => $lineDetail,
            'lines' => $lines,
            'summary' => $summary,
        ]);
    }

    public function create(): View
    {
        return view('payroll.runs.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function mine(Request $request): View
    {
        $employee = $request->user()->employee;

        $payslips = $employee
            ? $employee->payrollRunLines()->with('payrollRun')->get()->sortByDesc(fn ($line) => $line->payrollRun->period_month)->values()
            : collect();

        return view('payroll.my-payslips', ['payslips' => $payslips]);
    }

    public function store(PayrollRunRequest $request, GeneratePayrollRun $generatePayrollRun): RedirectResponse
    {
        $entity = Entity::findOrFail($request->validated('entity_id'));
        $run = $generatePayrollRun->handle($entity, $request->validated('period_month'), $request->user());

        return redirect()->route('payroll.runs.show', $run)->with('status', 'Payroll run generated.');
    }

    public function submit(PayrollRun $payrollRun, SubmitPayrollRunForApproval $submitPayrollRunForApproval): RedirectResponse
    {
        $submitPayrollRunForApproval->handle($payrollRun);

        return redirect()->route('payroll.runs.show', $payrollRun)->with('status', 'Payroll run submitted for approval.');
    }

    public function approve(Request $request, PayrollRun $payrollRun, ApprovePayrollRun $approvePayrollRun): RedirectResponse
    {
        $approvePayrollRun->handle($payrollRun, $request->user());

        return redirect()->route('payroll.runs.show', $payrollRun)->with('status', 'Payroll run approved.');
    }

    public function disburse(Request $request, PayrollRun $payrollRun, MarkPayrollRunDisbursed $markPayrollRunDisbursed): RedirectResponse
    {
        $markPayrollRunDisbursed->handle($payrollRun, $request->user());

        return redirect()->route('payroll.runs.show', $payrollRun)->with('status', 'Payroll run marked as disbursed.');
    }
}
