<?php

namespace Database\Factories;

use App\Enums\SpecStatus;
use App\Enums\WizardMode;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiSpec>
 */
class ApiSpecFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'data_source_id' => DataSource::factory(),
            'name' => fake()->words(3, true).' API',
            'wizard_mode' => WizardMode::SIMPLE,
            'status' => SpecStatus::PENDING,
            'openapi_spec' => [],
            'selected_tables' => [fake()->word().'s'],
            'configuration' => [],
        ];
    }

    public function simple(): static
    {
        return $this->state(fn () => [
            'wizard_mode' => WizardMode::SIMPLE,
        ]);
    }

    public function guided(): static
    {
        return $this->state(fn () => [
            'wizard_mode' => WizardMode::GUIDED,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => SpecStatus::PENDING,
        ]);
    }

    public function pushed(): static
    {
        return $this->state(fn () => [
            'status' => SpecStatus::PUSHED,
        ]);
    }
}
