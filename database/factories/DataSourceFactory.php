<?php

namespace Database\Factories;

use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataSource>
 */
class DataSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Database',
            'type' => fake()->randomElement(DataSourceType::cases()),
            'credentials' => [
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'test_db',
                'username' => 'readonly_user',
                'password' => 'secret_password',
            ],
            'status' => ConnectionStatus::DISCONNECTED,
            'user_id' => User::factory(),
            'metadata' => [],
        ];
    }

    public function connected(): static
    {
        return $this->state(fn () => [
            'status' => ConnectionStatus::CONNECTED,
        ]);
    }

    public function introspected(): static
    {
        return $this->state(fn () => [
            'status' => ConnectionStatus::INTROSPECTED,
        ]);
    }

    public function mysql(): static
    {
        return $this->state(fn () => [
            'type' => DataSourceType::MYSQL,
        ]);
    }

    public function sqlite(): static
    {
        return $this->state(fn () => [
            'type' => DataSourceType::SQLITE,
            'credentials' => [
                'database' => ':memory:',
            ],
        ]);
    }
}
