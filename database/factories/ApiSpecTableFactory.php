<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApiSpec;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiSpecTable>
 */
class ApiSpecTableFactory extends Factory
{
    public function definition(): array
    {
        $table = fake()->word().'s';

        return [
            'api_spec_id' => ApiSpec::factory(),
            'table_name' => $table,
            'resource_name' => $table,
            'operations' => [
                'list' => true,
                'show' => true,
                'create' => false,
                'update' => false,
                'delete' => false,
            ],
            'configuration' => [],
            'sort_order' => 0,
        ];
    }

    public function fullCrud(): static
    {
        return $this->state(fn () => [
            'operations' => [
                'list' => true,
                'show' => true,
                'create' => true,
                'update' => true,
                'delete' => true,
            ],
        ]);
    }

    public function readOnly(): static
    {
        return $this->state(fn () => [
            'operations' => [
                'list' => true,
                'show' => true,
                'create' => false,
                'update' => false,
                'delete' => false,
            ],
        ]);
    }
}
