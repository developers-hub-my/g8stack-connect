<?php

namespace Database\Factories;

use App\Models\ApiSpec;
use App\Models\ApiSpecVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiSpecVersion>
 */
class ApiSpecVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'api_spec_id' => ApiSpec::factory(),
            'version_number' => 1,
            'openapi_spec' => [],
            'configuration' => [],
            'change_summary' => 'Initial version.',
            'created_by' => User::factory(),
        ];
    }
}
