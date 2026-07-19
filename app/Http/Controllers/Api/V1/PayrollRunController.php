<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payroll\GeneratePayrollRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Models\Entity;
use App\Models\PayrollRun;

class PayrollRunController extends Controller
{
    public function index()
    {
        $runs = PayrollRun::with('entity')->latest('period_month')->paginate(25);

        return PayrollRunResource::collection($runs);
    }

    public function show(PayrollRun $payrollRun)
    {
        return PayrollRunResource::make($payrollRun->load('entity'));
    }

    public function store(PayrollRunRequest $request, GeneratePayrollRun $generatePayrollRun)
    {
        $entity = Entity::findOrFail($request->validated('entity_id'));
        $run = $generatePayrollRun->handle($entity, $request->validated('period_month'), $request->user());

        return PayrollRunResource::make($run)->response()->setStatusCode(201);
    }
}
