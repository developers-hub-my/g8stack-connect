<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiSpecKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $spec = $request->attributes->get('api_spec');

        if (! $spec) {
            return $next($request);
        }

        $requiresAuth = $spec->configuration['auth_enabled'] ?? false;

        if (! $requiresAuth) {
            return $next($request);
        }

        $apiKey = $request->header('X-API-Key') ?? $request->bearerToken();

        if (! $apiKey) {
            return response()->json([
                'message' => 'API key is required.',
            ], 401);
        }

        $keyHash = ApiSpecKey::hashKey($apiKey);

        $specKey = ApiSpecKey::where('key_hash', $keyHash)
            ->where('api_spec_id', $spec->id)
            ->whereNull('deleted_at')
            ->first();

        if (! $specKey) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        if ($specKey->isExpired()) {
            return response()->json([
                'message' => 'API key has expired.',
            ], 401);
        }

        if (! $specKey->isIpAllowed($request->ip())) {
            return response()->json([
                'message' => 'Request from this IP is not allowed.',
            ], 403);
        }

        $request->attributes->set('api_spec_key', $specKey);

        $specKey->touchLastUsed();

        return $next($request);
    }
}
