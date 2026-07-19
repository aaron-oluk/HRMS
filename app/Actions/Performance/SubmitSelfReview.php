<?php

namespace App\Actions\Performance;

use App\Models\Employee;
use App\Models\PerformanceReview;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class SubmitSelfReview
{
    /**
     * @param  array{rating: int, comments?: string|null}  $data
     */
    public function handle(PerformanceReview $review, Employee $actor, array $data): PerformanceReview
    {
        if ($review->employee_id !== $actor->id) {
            throw new AuthorizationException('You may only submit your own review.');
        }

        if ($review->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'This review has already been submitted.',
            ]);
        }

        $review->update([
            'self_rating' => $data['rating'],
            'self_comments' => $data['comments'] ?? null,
            'self_submitted_at' => now(),
            'status' => 'self_submitted',
        ]);

        return $review;
    }
}
