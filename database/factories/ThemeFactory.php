<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->colorName();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'color_50' => '#f8fafc',
            'color_100' => '#f1f5f9',
            'color_500' => '#64748b',
            'color_600' => '#475569',
            'color_700' => '#334155',
            'color_800' => '#1e293b',
            'font_stack' => 'ui-sans-serif, system-ui, sans-serif',
            'is_default' => false,
        ];
    }
}
