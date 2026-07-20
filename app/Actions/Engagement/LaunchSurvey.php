<?php

namespace App\Actions\Engagement;

use App\Models\Employee;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\DB;

class LaunchSurvey
{
    /**
     * @param  array{title: string, description?: string|null, is_anonymous?: bool, closes_at?: string|null, questions: list<array{text: string, type: string}>}  $data
     */
    public function handle(array $data, User $actor): Survey
    {
        return DB::transaction(function () use ($data, $actor) {
            $survey = Survey::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_anonymous' => $data['is_anonymous'] ?? false,
                'closes_at' => $data['closes_at'] ?? null,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            foreach (array_values($data['questions']) as $order => $question) {
                $survey->questions()->create([
                    'text' => $question['text'],
                    'type' => $question['type'],
                    'order' => $order,
                ]);
            }

            Employee::where('status', 'active')->with('user')->get()->each(
                fn (Employee $employee) => $employee->user?->notify(new GenericNotification(
                    title: 'New survey: '.$survey->title,
                    message: 'HR would like your feedback — it only takes a minute.',
                    icon: 'bx-message-square-detail',
                    url: route('profile.edit'),
                ))
            );

            return $survey;
        });
    }
}
