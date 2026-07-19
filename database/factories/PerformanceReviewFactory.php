<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceReview>
 */
class PerformanceReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'performance_review_cycle_id' => fn (array $attributes) => PerformanceReviewCycle::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'reviewer_employee_id' => null,
            'status' => 'pending',
        ];
    }
}
