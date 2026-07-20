<?php

namespace App\Actions\Performance;

use App\Models\Employee;
use App\Models\PerformanceFeedbackRequest;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;

class RequestPeerFeedback
{
    public function __construct(protected TeamScope $teamScope) {}

    public function handle(PerformanceReview $review, Employee $reviewer, User $actor): PerformanceFeedbackRequest
    {
        // Nominating a peer is restricted to whoever could already act as this employee's
        // manager (same relationship SubmitManagerReview requires) — not a free-for-all.
        if (! $this->teamScope->canActOn($review->employee_id, $actor)) {
            throw new AuthorizationException('You may only request feedback for your own direct reports or department.');
        }

        $request = $review->feedbackRequests()->create([
            'reviewer_employee_id' => $reviewer->id,
            'requested_by' => $actor->id,
        ]);

        $reviewer->user?->notify(new GenericNotification(
            title: 'Feedback requested',
            message: "You've been asked to give feedback on {$review->employee->fullName()} for {$review->cycle->name}.",
            icon: 'bx-comment-detail',
            url: route('performance.my'),
        ));

        return $request;
    }
}
