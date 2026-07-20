<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceFeedbackRequest;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceFeedbackRequest>
 */
class PerformanceFeedbackRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'performance_review_id' => PerformanceReview::factory(),
            'reviewer_employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => PerformanceReview::find($attributes['performance_review_id'])->tenant_id,
            'requested_by' => User::factory(),
            'status' => 'pending',
        ];
    }
}
