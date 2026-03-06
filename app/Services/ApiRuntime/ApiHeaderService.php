<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiHeaderService
{
    public function apply(JsonResponse $response, ApiSpec $spec, float $startTime): JsonResponse
    {
        $this->applySecurityHeaders($response);
        $this->applyStandardHeaders($response, $startTime);
        $this->applyCustomHeaders($response, $spec);
        $this->applyCorsHeaders($response, $spec);

        return $response;
    }

    public function applyPaginationHeaders(JsonResponse $response, array $meta): JsonResponse
    {
        if (isset($meta['total'])) {
            $response->headers->set('X-Total-Count', (string) $meta['total']);
        }

        if (isset($meta['current_page'])) {
            $response->headers->set('X-Page', (string) $meta['current_page']);
        }

        if (isset($meta['per_page'])) {
            $response->headers->set('X-Per-Page', (string) $meta['per_page']);
        }

        return $response;
    }

    protected function applySecurityHeaders(JsonResponse $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
    }

    protected function applyStandardHeaders(JsonResponse $response, float $startTime): void
    {
        $response->headers->set('X-Request-Id', (string) Str::uuid());
        $response->headers->set('X-Response-Time', round((microtime(true) - $startTime) * 1000).'ms');
    }

    protected function applyCustomHeaders(JsonResponse $response, ApiSpec $spec): void
    {
        $config = $spec->configuration ?? [];
        $headers = $config['headers'] ?? [];

        foreach ($headers as $name => $value) {
            if ($name === 'custom' && is_array($value)) {
                foreach ($value as $customName => $customValue) {
                    $response->headers->set($customName, $customValue);
                }

                continue;
            }

            if (is_string($value)) {
                $response->headers->set($name, $value);
            }
        }
    }

    protected function applyCorsHeaders(JsonResponse $response, ApiSpec $spec): void
    {
        $config = $spec->configuration ?? [];
        $origins = $config['allowed_origins'] ?? [];

        if (! empty($origins)) {
            $response->headers->set('Access-Control-Allow-Origin', implode(', ', $origins));
        }

        $methods = $config['methods'] ?? ['GET'];
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $methods));
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Authorization, X-API-Version');
    }
}
