<?php

namespace App\Actions\Cases;

use App\Models\HrCase;
use App\Models\HrCaseComment;
use App\Models\User;
use App\Notifications\GenericNotification;

class AddCaseComment
{
    public function handle(HrCase $case, User $author, string $body, bool $isInternal): HrCaseComment
    {
        $comment = $case->comments()->create([
            'author_user_id' => $author->id,
            'body' => $body,
            'is_internal' => $isInternal,
        ]);

        if (! $isInternal) {
            $recipient = $author->id === $case->employee->user?->id
                ? $case->assignee
                : $case->employee->user;

            $recipient?->notify(new GenericNotification(
                title: 'New reply on: '.$case->subject,
                message: "{$author->name} replied to the case.",
                icon: 'bx-support',
                url: route('cases.show', $case, absolute: false),
            ));
        }

        return $comment;
    }
}
