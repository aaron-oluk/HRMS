<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobRequisition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecruitmentPipelineController extends Controller
{
    public function index(Request $request): View
    {
        $requisitionsQuery = JobRequisition::orderBy('title');

        // Same department-scoping as JobRequisitionController::index — Department Manager only
        // holds recruitment.view, never recruitment.manage, and has no employee-keyed TeamScope
        // equivalent for requisitions.
        if ($request->user()->hasRole('Department Manager')) {
            $departmentId = $request->user()->employee?->currentEmployment?->department_id;
            $requisitionsQuery->where('department_id', $departmentId);
        }

        $requisitions = $requisitionsQuery->get();

        $candidatesQuery = Candidate::with('jobRequisition')
            ->whereIn('job_requisition_id', $requisitions->pluck('id'))
            ->latest();

        if ($request->filled('job_requisition_id')) {
            $candidatesQuery->where('job_requisition_id', $request->integer('job_requisition_id'));
        }

        $candidates = $candidatesQuery->get();
        $candidatesByStage = $candidates->groupBy('status');

        return view('recruitment.pipeline', [
            'requisitions' => $requisitions,
            'openRequisitions' => $requisitions->whereIn('status', ['draft', 'open']),
            'candidatesByStage' => $candidatesByStage,
            'totalCandidates' => $candidates->count(),
            'selectedJobRequisitionId' => $request->integer('job_requisition_id') ?: null,
        ]);
    }
}
