<?php

use App\Actions\Performance\CreatePerformanceReviewCycle;
use App\Models\Entity;
use App\Models\PerformanceReviewCycle;

function makeCycle(): PerformanceReviewCycle
{
    return app(CreatePerformanceReviewCycle::class)->handle([
        'name' => '2026 H2',
        'start_date' => now()->startOfYear()->toDateString(),
        'end_date' => now()->endOfYear()->toDateString(),
    ]);
}

test('creating a cycle opens a pending review for every active employee', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $cycle = makeCycle();

    $reviews = $cycle->reviews;
    expect($reviews->pluck('employee_id'))->toContain($report->id);
    expect($reviews->firstWhere('employee_id', $report->id)->status)->toBe('pending');
});

test('an employee can submit their own self-review', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$report, $reportUser] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $cycle = makeCycle();
    $review = $cycle->reviews()->where('employee_id', $report->id)->firstOrFail();

    $this->actingAs($reportUser)->post(route('performance.reviews.submit-self', [$cycle, $review]), [
        'rating' => 4,
        'comments' => 'Good quarter.',
    ])->assertRedirect(route('performance.my'));

    expect($review->fresh()->status)->toBe('self_submitted');
    expect($review->fresh()->self_rating)->toBe(4);
});

test('a manager can complete the review once the employee has self-submitted', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$report, $reportUser] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $cycle = makeCycle();
    $review = $cycle->reviews()->where('employee_id', $report->id)->firstOrFail();

    $this->actingAs($reportUser)->post(route('performance.reviews.submit-self', [$cycle, $review]), ['rating' => 4]);

    $this->actingAs($bossUser)->post(route('performance.reviews.submit-manager', [$cycle, $review]), [
        'rating' => 5,
        'comments' => 'Great work.',
    ])->assertRedirect(route('performance.cycles.show', $cycle));

    expect($review->fresh()->status)->toBe('completed');
    expect($review->fresh()->manager_rating)->toBe(5);
});

test('a manager cannot review an employee outside their team', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$stranger, $strangerUser] = employeeUser($tenant, $entity, 'Employee'); // no reportsTo

    $cycle = makeCycle();
    $review = $cycle->reviews()->where('employee_id', $stranger->id)->firstOrFail();

    $this->actingAs($strangerUser)->post(route('performance.reviews.submit-self', [$cycle, $review]), ['rating' => 3]);

    $this->actingAs($bossUser)->post(route('performance.reviews.submit-manager', [$cycle, $review]), ['rating' => 5]);

    expect($review->fresh()->status)->toBe('self_submitted');
});

test('a manager cannot submit their review before the employee self-submits', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $cycle = makeCycle();
    $review = $cycle->reviews()->where('employee_id', $report->id)->firstOrFail();

    $this->actingAs($bossUser)->post(route('performance.reviews.submit-manager', [$cycle, $review]), ['rating' => 5]);

    expect($review->fresh()->status)->toBe('pending');
});

test('an hr specialist cannot access performance reviews', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');

    $cycle = makeCycle();

    $this->actingAs($specialist)->get(route('performance.cycles.show', $cycle))->assertForbidden();
});

test('a review from another tenant is invisible via the global scope', function () {
    tenantWithRole('HR Admin');
    $cycleA = makeCycle();

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('performance.cycles.show', $cycleA))->assertNotFound();
});
