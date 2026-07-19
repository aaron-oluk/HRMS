<?php

namespace App\Actions\Recruitment;

use App\Models\Candidate;
use Illuminate\Validation\ValidationException;

class MoveCandidateStage
{
    public function handle(Candidate $candidate, string $status): Candidate
    {
        if (! in_array($status, Candidate::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'That is not a valid pipeline stage.',
            ]);
        }

        $candidate->update(['status' => $status]);

        return $candidate;
    }
}
