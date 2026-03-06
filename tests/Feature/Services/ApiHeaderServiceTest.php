<?php

declare(strict_types=1);

use App\Models\ApiSpec;
use App\Services\ApiRuntime\ApiHeaderService;

it('applies security headers to response', function () {
    $spec = ApiSpec::factory()->create();
    $response = response()->json(['data' => []]);

    $service = new ApiHeaderService;
    $result = $service->apply($response, $spec, microtime(true));

    expect($result->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($result->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($result->headers->get('X-XSS-Protection'))->toBe('1; mode=block');
});

it('applies request id and response time', function () {
    $spec = ApiSpec::factory()->create();
    $response = response()->json(['data' => []]);

    $service = new ApiHeaderService;
    $result = $service->apply($response, $spec, microtime(true));

    expect($result->headers->get('X-Request-Id'))->not->toBeNull()
        ->and($result->headers->get('X-Response-Time'))->toContain('ms');
});

it('applies custom headers from spec configuration', function () {
    $spec = ApiSpec::factory()->create([
        'configuration' => [
            'headers' => [
                'X-Powered-By' => 'G8Connect',
                'custom' => [
                    'X-Team' => 'Engineering',
                ],
            ],
        ],
    ]);

    $response = response()->json(['data' => []]);

    $service = new ApiHeaderService;
    $result = $service->apply($response, $spec, microtime(true));

    expect($result->headers->get('X-Powered-By'))->toBe('G8Connect')
        ->and($result->headers->get('X-Team'))->toBe('Engineering');
});

it('applies pagination headers', function () {
    $spec = ApiSpec::factory()->create();
    $response = response()->json(['data' => []]);

    $service = new ApiHeaderService;
    $service->applyPaginationHeaders($response, [
        'total' => 100,
        'current_page' => 2,
        'per_page' => 15,
    ]);

    expect($response->headers->get('X-Total-Count'))->toBe('100')
        ->and($response->headers->get('X-Page'))->toBe('2')
        ->and($response->headers->get('X-Per-Page'))->toBe('15');
});

it('applies cors headers from spec configuration', function () {
    $spec = ApiSpec::factory()->create([
        'configuration' => [
            'methods' => ['GET', 'POST'],
            'allowed_origins' => ['https://example.com'],
        ],
    ]);

    $response = response()->json(['data' => []]);

    $service = new ApiHeaderService;
    $result = $service->apply($response, $spec, microtime(true));

    expect($result->headers->get('Access-Control-Allow-Origin'))->toBe('https://example.com')
        ->and($result->headers->get('Access-Control-Allow-Methods'))->toBe('GET, POST');
});
