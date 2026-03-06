<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApiSpec;
use App\Models\ApiSpecKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiSpecKey>
 */
class ApiSpecKeyFactory extends Factory
{
    public function definition(): array
    {
        $key = ApiSpecKey::generateKey();

        return [
            'api_spec_id' => ApiSpec::factory(),
            'name' => fake()->words(2, true).' Key',
            'key_hash' => ApiSpecKey::hashKey($key),
            'key_prefix' => ApiSpecKey::prefixFromKey($key),
            'rate_limit' => 60,
            'allowed_ips' => null,
            'allowed_origins' => null,
            'expires_at' => null,
            'last_used_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withIpRestriction(array $ips): static
    {
        return $this->state(fn () => [
            'allowed_ips' => $ips,
        ]);
    }
}
