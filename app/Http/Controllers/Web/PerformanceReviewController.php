<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\SubmitManagerReview;
use App\Actions\Performance\SubmitSelfReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\PerformanceReviewSubmissionRequest;
use App\Models\PerformanceFeedbackRequest;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function mine(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        $reviews = $employee
            ? PerformanceReview::where('employee_id', $employee->id)->with('cycle')->get()->sortByDesc(fn ($review) => $review->cycle->start_date)->values()
            : collect();

        $goals = $employee ? $employee->performanceGoals()->latest()->get() : collect();

        $feedbackRequests = $employee
            ? PerformanceFeedbackRequest::where('reviewer_employee_id', $employee->id)->with('review.employee', 'review.cycle')->latest()->get()
            : collect();

        // Peer feedback ABOUT this employee (as opposed to $feedbackRequests above, which is
        // feedback this employee was asked to GIVE about someone else).
        $receivedFeedback = $employee
            ? PerformanceFeedbackRequest::whereHas('review', fn ($query) => $query->where('employee_id', $employee->id))
                ->whereNotNull('rating')
                ->with('reviewer.currentEmployment.position', 'review.cycle')
                ->latest('submitted_at')
                ->get()
            : collect();

        $oneOnOnes = $employee ? $employee->oneOnOnes()->latest('scheduled_at')->get() : collect();

        return view('performance.my', [
            'performanceReviews' => $reviews,
            'goals' => $goals,
            'feedbackRequests' => $feedbackRequests,
            'receivedFeedback' => $receivedFeedback,
            'oneOnOnes' => $oneOnOnes,
        ]);
    }

    public function submitSelf(
        PerformanceReviewSubmissionRequest $request,
        PerformanceReviewCycle $cycle,
        PerformanceReview $review,
        SubmitSelfReview $submitSelfReview
    ): RedirectResponse {
        $submitSelfReview->handle($review, $request->user()->employee, $request->validated());

        return redirect()->route('performance.my')->with('status', 'Self-review submitted.');
    }

    public function submitManager(
        PerformanceReviewSubmissionRequest $request,
        PerformanceReviewCycle $cycle,
        PerformanceReview $review,
        SubmitManagerReview $submitManagerReview
    ): RedirectResponse {
        $submitManagerReview->handle($review, $request->user(), $request->validated());

        return redirect()->route('performance.cycles.show', $cycle)->with('status', 'Manager review submitted.');
    }
}
