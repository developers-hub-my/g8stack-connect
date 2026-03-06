<?php

use App\Http\Controllers\Api\V1\DynamicApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.version:v1'])->prefix('api/v1')->group(function () {
    Route::get('{slug}', [DynamicApiController::class, 'index']);
    Route::get('{slug}/{id}', [DynamicApiController::class, 'show']);
    Route::post('{slug}', [DynamicApiController::class, 'store']);
    Route::put('{slug}/{id}', [DynamicApiController::class, 'update']);
    Route::delete('{slug}/{id}', [DynamicApiController::class, 'destroy']);
});
