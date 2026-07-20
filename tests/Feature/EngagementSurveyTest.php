<?php

use App\Models\Entity;
use App\Models\Survey;

test('hr admin can launch a survey with questions', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->post(route('engagement.surveys.store'), [
        'title' => 'Q3 pulse check',
        'is_anonymous' => true,
        'questions' => [
            ['text' => 'How are you feeling?', 'type' => 'rating'],
            ['text' => 'Anything else?', 'type' => 'text'],
        ],
    ])->assertRedirect();

    $survey = Survey::where('title', 'Q3 pulse check')->firstOrFail();
    expect($survey->status)->toBe('active');
    expect($survey->questions)->toHaveCount(2);
});

test('an employee can respond to a survey once', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $survey = Survey::create(['tenant_id' => $tenant->id, 'title' => 'Pulse', 'status' => 'active']);
    $question = $survey->questions()->create(['tenant_id' => $tenant->id, 'text' => 'Rate us', 'type' => 'rating', 'order' => 0]);

    $this->actingAs($employeeUser)->post(route('engagement.surveys.respond', $survey), [
        'answers' => [['question_id' => $question->id, 'rating_value' => 5]],
    ])->assertRedirect(route('engagement.surveys.show', $survey));

    expect($survey->responses()->where('employee_id', $employee->id)->exists())->toBeTrue();

    // A second submission is rejected.
    $this->actingAs($employeeUser)->post(route('engagement.surveys.respond', $survey), [
        'answers' => [['question_id' => $question->id, 'rating_value' => 3]],
    ]);

    expect($survey->responses()->where('employee_id', $employee->id)->count())->toBe(1);
});

test('an employee cannot launch or manage surveys', function () {
    [, $employeeUser] = tenantWithRole('Employee');

    $this->actingAs($employeeUser)->get(route('engagement.surveys.index'))->assertForbidden();
});

test('a survey from another tenant is invisible via the global scope', function () {
    [$tenantA, $hrAdminA] = tenantWithRole('HR Admin');
    $surveyA = Survey::create(['tenant_id' => $tenantA->id, 'title' => 'A', 'status' => 'active']);

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('engagement.surveys.show', $surveyA))->assertNotFound();
});
