<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateCommentRequest;
use App\Models\Candidate;
use App\Models\CandidateComment;
use Illuminate\Http\RedirectResponse;

class CandidateCommentController extends Controller
{
    public function store(CandidateCommentRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->comments()->create($request->validated());

        return redirect()->route('recruitment.candidates.show', $candidate)->with('status', 'Comment added.');
    }

    public function destroy(Candidate $candidate, CandidateComment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->route('recruitment.candidates.show', $candidate)->with('status', 'Comment removed.');
    }
}
