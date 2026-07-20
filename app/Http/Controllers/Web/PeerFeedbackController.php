<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\RequestPeerFeedback;
use App\Actions\Performance\SubmitPeerFeedback;
use App\Http\Controllers\Controller;
use App\Http\Requests\PeerFeedbackNominationRequest;
use App\Http\Requests\PerformanceReviewSubmissionRequest;
use App\Models\Employee;
use App\Models\PerformanceFeedbackRequest;
use App\Models\PerformanceReview;
use Illuminate\Http\RedirectResponse;

class PeerFeedbackController extends Controller
{
    public function store(PeerFeedbackNominationRequest $request, PerformanceReview $review, RequestPeerFeedback $requestPeerFeedback): RedirectResponse
    {
        $reviewer = Employee::findOrFail($request->validated('reviewer_employee_id'));
        $requestPeerFeedback->handle($review, $reviewer, $request->user());

        return redirect()->route('performance.cycles.show', $review->cycle)->with('status', 'Feedback requested.');
    }

    public function submit(PerformanceReviewSubmissionRequest $request, PerformanceFeedbackRequest $feedbackRequest, SubmitPeerFeedback $submitPeerFeedback): RedirectResponse
    {
        $submitPeerFeedback->handle($feedbackRequest, $request->user()->employee, $request->validated());

        return redirect()->route('profile.edit')->with('status', 'Feedback submitted.');
    }
}
