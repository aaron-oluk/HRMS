<?php

namespace Database\Factories;

use App\Models\SignableDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SignableDocument>
 */
class SignableDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uploaded_by' => User::factory(),
            'signer_user_id' => User::factory(),
            'tenant_id' => fn (array $attributes) => User::find($attributes['uploaded_by'])->tenant_id,
            'title' => fake()->sentence(3),
            'original_path' => 'signable-documents/'.fake()->uuid().'.pdf',
            'status' => 'sent',
            'page_count' => 1,
            'sent_at' => now(),
        ];
    }
}
