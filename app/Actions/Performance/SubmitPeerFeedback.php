<?php

namespace App\Actions\Performance;

use App\Models\Employee;
use App\Models\PerformanceFeedbackRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class SubmitPeerFeedback
{
    /**
     * @param  array{rating: int, comments?: string|null}  $data
     */
    public function handle(PerformanceFeedbackRequest $request, Employee $actor, array $data): PerformanceFeedbackRequest
    {
        // Pure ownership, not TeamScope — the reviewer is an arbitrary peer with no
        // hierarchical relationship to the subject, so only the nominated reviewer
        // themselves may submit this specific request.
        if ($request->reviewer_employee_id !== $actor->id) {
            throw new AuthorizationException('You may only submit feedback you were asked to give.');
        }

        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'This feedback request has already been submitted.',
            ]);
        }

        $request->update([
            'rating' => $data['rating'],
            'comments' => $data['comments'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $request;
    }
}
