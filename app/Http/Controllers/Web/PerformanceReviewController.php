<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\SubmitManagerReview;
use App\Actions\Performance\SubmitSelfReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\PerformanceReviewSubmissionRequest;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use Illuminate\Http\RedirectResponse;

class PerformanceReviewController extends Controller
{
    public function submitSelf(
        PerformanceReviewSubmissionRequest $request,
        PerformanceReviewCycle $cycle,
        PerformanceReview $review,
        SubmitSelfReview $submitSelfReview
    ): RedirectResponse {
        $submitSelfReview->handle($review, $request->user()->employee, $request->validated());

        return redirect()->route('profile.edit')->with('status', 'Self-review submitted.');
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
