<?php

namespace App\Http\Controllers\Web;

use App\Actions\Recruitment\MoveCandidateStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use App\Models\JobRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function store(CandidateRequest $request, JobRequisition $jobRequisition): RedirectResponse
    {
        $jobRequisition->candidates()->create($request->validated());

        return redirect()->route('recruitment.requisitions.show', $jobRequisition)->with('status', 'Candidate added.');
    }

    public function updateStage(Request $request, JobRequisition $jobRequisition, Candidate $candidate, MoveCandidateStage $moveCandidateStage): RedirectResponse
    {
        $moveCandidateStage->handle($candidate, $request->string('status')->value());

        return redirect()->route('recruitment.requisitions.show', $jobRequisition)->with('status', 'Candidate stage updated.');
    }
}
