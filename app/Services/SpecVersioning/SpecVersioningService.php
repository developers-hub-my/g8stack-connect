<?php

declare(strict_types=1);

namespace App\Services\SpecVersioning;

use App\Models\ApiSpec;
use App\Models\ApiSpecVersion;

class SpecVersioningService
{
    public function createVersion(ApiSpec $apiSpec, array $openApiSpec, array $configuration, string $changeSummary): ApiSpecVersion
    {
        $latestVersion = $apiSpec->versions()->max('version_number') ?? 0;

        $version = ApiSpecVersion::create([
            'api_spec_id' => $apiSpec->id,
            'version_number' => $latestVersion + 1,
            'openapi_spec' => $openApiSpec,
            'configuration' => $configuration,
            'change_summary' => $changeSummary,
            'created_by' => auth()->id(),
        ]);

        $apiSpec->update([
            'openapi_spec' => $openApiSpec,
            'configuration' => $configuration,
        ]);

        return $version;
    }
}
