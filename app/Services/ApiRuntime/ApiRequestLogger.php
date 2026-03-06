<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiRequestLog;
use App\Models\ApiSpec;
use App\Models\ApiSpecKey;
use Illuminate\Http\Request;

class ApiRequestLogger
{
    public function log(
        Request $request,
        ApiSpec $spec,
        ?ApiSpecKey $key,
        int $statusCode,
        float $startTime,
        ?string $resourceName = null,
    ): void {
        ApiRequestLog::create([
            'api_spec_id' => $spec->id,
            'api_spec_key_id' => $key?->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'resource_name' => $resourceName,
            'ip_address' => $request->ip(),
            'status_code' => $statusCode,
            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
            'created_at' => now(),
        ]);
    }
}
