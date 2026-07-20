<?php

use App\Actions\Performance\CreatePerformanceReviewCycle;
use App\Models\Entity;
use App\Models\PerformanceReviewCycle;

function makeCycleForPeerFeedback(): PerformanceReviewCycle
{
    return app(CreatePerformanceReviewCycle::class)->handle([
        'name' => '2026 H2',
        'start_date' => now()->startOfYear()->toDateString(),
        'end_date' => now()->endOfYear()->toDateString(),
    ]);
}

test('a manager can nominate a peer to give feedback on their direct report', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);
    [$peer, $peerUser] = employeeUser($tenant, $entity, 'Employee');

    $cycle = makeCycleForPeerFeedback();
    $review = $cycle->reviews()->where('employee_id', $report->id)->firstOrFail();

    $this->actingAs($bossUser)->post(route('performance.feedback-requests.store', $review), [
        'reviewer_employee_id' => $peer->id,
    ])->assertRedirect();

    $feedbackRequest = $review->feedbackRequests()->firstOrFail();
    expect($feedbackRequest->reviewer_employee_id)->toBe($peer->id);
    expect($feedbackRequest->status)->toBe('pending');
});

test('a manager cannot nominate peers for an employee outside their team', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$stranger] = employeeUser($tenant, $entity, 'Employee'); // no reportsTo
    [$peer] = employeeUser($tenant, $entity, 'Employee');

    $cycle = makeCycleForPeerFeedback();
    $review = $cycle->reviews()->where('employee_id', $stranger->id)->firstOrFail();

    $this->actingAs($bossUser)->post(route('performance.feedback-requests.store', $review), [
        'reviewer_employee_id' => $peer->id,
    ])->assertForbidden();
});

test('only the nominated peer can submit their feedback', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);
    [$peer, $peerUser] = employeeUser($tenant, $entity, 'Employee');
    [, $otherUser] = employeeUser($tenant, $entity, 'Employee');

    $cycle = makeCycleForPeerFeedback();
    $review = $cycle->reviews()->where('employee_id', $report->id)->firstOrFail();

    $this->actingAs($bossUser)->post(route('performance.feedback-requests.store', $review), [
        'reviewer_employee_id' => $peer->id,
    ]);
    $feedbackRequest = $review->feedbackRequests()->firstOrFail();

    // A different employee (not the nominated peer) cannot submit it.
    $this->actingAs($otherUser)->post(route('performance.feedback-requests.submit', $feedbackRequest), [
        'rating' => 5,
    ])->assertForbidden();
    expect($feedbackRequest->fresh()->status)->toBe('pending');

    // The nominated peer can.
    $this->actingAs($peerUser)->post(route('performance.feedback-requests.submit', $feedbackRequest), [
        'rating' => 4,
        'comments' => 'Great collaborator.',
    ])->assertRedirect();
    expect($feedbackRequest->fresh()->status)->toBe('submitted');
    expect($feedbackRequest->fresh()->rating)->toBe(4);
});
