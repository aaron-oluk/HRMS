<?php

namespace App\Http\Controllers\Web;

use App\Actions\Recruitment\MoveCandidateStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateRequest;
use App\Http\Requests\PipelineCandidateRequest;
use App\Http\Requests\RateCandidateRequest;
use App\Models\Candidate;
use App\Models\JobRequisition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function store(CandidateRequest $request, JobRequisition $jobRequisition): RedirectResponse
    {
        $jobRequisition->candidates()->create($request->validated());

        return redirect()->route('recruitment.requisitions.show', $jobRequisition)->with('status', 'Candidate added.');
    }

    /**
     * Flat create used by the Pipeline board's "Add Candidate" modal, which isn't scoped to a
     * single requisition page — the target job comes from the form body instead of the URL.
     */
    public function storeFromPipeline(PipelineCandidateRequest $request): RedirectResponse
    {
        $jobRequisition = JobRequisition::findOrFail($request->validated('job_requisition_id'));
        $jobRequisition->candidates()->create($request->safe()->except('job_requisition_id'));

        return redirect()->route('recruitment.pipeline')->with('status', 'Candidate added.');
    }

    public function show(Request $request, Candidate $candidate): View
    {
        $candidate->load(['jobRequisition', 'comments.author']);

        // Same department-scoping as JobRequisitionController::show.
        if ($request->user()->hasRole('Department Manager')) {
            $departmentId = $request->user()->employee?->currentEmployment?->department_id;
            abort_unless($candidate->jobRequisition->department_id === $departmentId, 403);
        }

        return view('recruitment.candidates.show', ['candidate' => $candidate]);
    }

    public function updateStage(Request $request, JobRequisition $jobRequisition, Candidate $candidate, MoveCandidateStage $moveCandidateStage): RedirectResponse
    {
        $moveCandidateStage->handle($candidate, $request->string('status')->value());

        return back()->with('status', 'Candidate stage updated.');
    }

    public function rate(RateCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->update(['rating' => $request->validated('rating')]);

        return back()->with('status', 'Candidate rated.');
    }
}
