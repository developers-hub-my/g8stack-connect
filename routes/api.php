<?php

use App\Http\Controllers\Api\V1\DynamicApiController;
use App\Http\Middleware\ApiKeyAuthentication;
use App\Http\Middleware\ApiRateLimiter;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.version', ApiKeyAuthentication::class, ApiRateLimiter::class])
    ->prefix('api/connect')
    ->group(function () {
        // Spec root: list resources (grouped) or list records (single-table)
        Route::get('{slug}', [DynamicApiController::class, 'resources']);

        // Resource CRUD
        Route::get('{slug}/{resource}', [DynamicApiController::class, 'index']);
        Route::get('{slug}/{resource}/{id}', [DynamicApiController::class, 'show']);
        Route::post('{slug}/{resource}', [DynamicApiController::class, 'store']);
        Route::put('{slug}/{resource}/{id}', [DynamicApiController::class, 'update']);
        Route::delete('{slug}/{resource}/{id}', [DynamicApiController::class, 'destroy']);
    });
