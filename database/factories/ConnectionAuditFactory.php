<?php

namespace Database\Factories;

use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConnectionAudit>
 */
class ConnectionAuditFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'data_source_id' => DataSource::factory(),
            'action' => fake()->randomElement(['connect', 'introspect', 'preview', 'disconnect']),
            'status' => fake()->randomElement(['success', 'failed']),
            'message' => fake()->sentence(),
            'metadata' => ['source_type' => 'mysql'],
            'ip_address' => fake()->ipv4(),
        ];
    }
}
