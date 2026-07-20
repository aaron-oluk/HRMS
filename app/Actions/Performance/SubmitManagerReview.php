<?php

namespace App\Actions\Performance;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\Approvals\TeamScope;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class SubmitManagerReview
{
    public function __construct(protected TeamScope $teamScope) {}

    /**
     * @param  array{rating: int, comments?: string|null}  $data
     */
    public function handle(PerformanceReview $review, User $actor, array $data): PerformanceReview
    {
        if (! $this->teamScope->canActOn($review->employee_id, $actor)) {
            throw new AuthorizationException('You may only review your own direct reports or department.');
        }

        if ($review->status !== 'self_submitted') {
            throw ValidationException::withMessages([
                'status' => 'The employee has not submitted their self-review yet.',
            ]);
        }

        $review->update([
            'manager_rating' => $data['rating'],
            'manager_comments' => $data['comments'] ?? null,
            'manager_submitted_at' => now(),
            'status' => 'completed',
        ]);

        $review->employee->user?->notify(new GenericNotification(
            title: 'Performance review completed',
            message: "Your review for {$review->cycle->name} has been completed by your manager.",
            icon: 'bx-line-chart',
            url: route('performance.my'),
        ));

        return $review;
    }
}
