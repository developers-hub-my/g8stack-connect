<?php

namespace Database\Factories;

use App\Models\ApiSpec;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiSpecField>
 */
class ApiSpecFieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'api_spec_id' => ApiSpec::factory(),
            'column_name' => fake()->word(),
            'display_name' => fake()->word(),
            'data_type' => fake()->randomElement(['varchar', 'integer', 'text', 'boolean', 'timestamp']),
            'is_exposed' => true,
            'is_pii' => false,
            'is_required' => false,
            'is_filterable' => false,
            'is_sortable' => false,
            'sort_order' => 0,
        ];
    }

    public function pii(): static
    {
        return $this->state(fn () => [
            'is_pii' => true,
            'is_exposed' => false,
        ]);
    }
}
