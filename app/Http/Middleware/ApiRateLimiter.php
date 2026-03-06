<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    public function __construct(
        protected RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $specKey = $request->attributes->get('api_spec_key');
        $spec = $request->attributes->get('api_spec');

        if (! $spec) {
            return $next($request);
        }

        $rateLimit = $specKey?->rate_limit ?? $spec->configuration['rate_limit'] ?? 60;
        $key = $specKey ? 'api_key:'.$specKey->id : 'api_ip:'.$spec->id.':'.$request->ip();

        if ($this->limiter->tooManyAttempts($key, $rateLimit)) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'message' => 'Too many requests.',
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $rateLimit,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->getTimestamp(),
            ]);
        }

        $this->limiter->hit($key, 60);

        $response = $next($request);

        if ($response instanceof \Illuminate\Http\JsonResponse || $response instanceof \Illuminate\Http\Response) {
            $response->headers->set('X-RateLimit-Limit', (string) $rateLimit);
            $response->headers->set('X-RateLimit-Remaining', (string) $this->limiter->remaining($key, $rateLimit));
        }

        return $response;
    }
}
