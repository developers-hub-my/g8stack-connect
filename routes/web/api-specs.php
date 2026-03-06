<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])
    ->as('api-specs.')
    ->prefix('api-specs')
    ->group(function () {

        Route::get('/', function () {
            return view('api-specs.index');
        })->name('index');

        Route::get('/create', function () {
            return view('api-specs.create');
        })->name('create');

        Route::get('/{uuid}', function (string $uuid) {
            return view('api-specs.show', compact('uuid'));
        })->name('show');

        Route::get('/{uuid}/edit', function (string $uuid) {
            return view('api-specs.edit', compact('uuid'));
        })->name('edit');

        Route::get('/{uuid}/versions', function (string $uuid) {
            return view('api-specs.versions', compact('uuid'));
        })->name('versions');

        Route::get('/{uuid}/configure', function (string $uuid) {
            return view('api-specs.configure', compact('uuid'));
        })->name('configure');

        Route::get('/{uuid}/preview', function (string $uuid) {
            $apiSpec = \App\Models\ApiSpec::where('uuid', $uuid)->firstOrFail();

            abort_unless(auth()->user()->can('view', $apiSpec), 403);

            return view('api-specs.spec-viewer', compact('apiSpec'));
        })->name('preview');

        Route::get('/{uuid}/spec.json', function (string $uuid) {
            $apiSpec = \App\Models\ApiSpec::where('uuid', $uuid)->firstOrFail();

            abort_unless(auth()->user()->can('view', $apiSpec), 403);

            return response()->json(
                $apiSpec->openapi_spec ?? ['openapi' => '3.1.0', 'info' => ['title' => $apiSpec->name, 'version' => '0.0.0'], 'paths' => []],
                headers: ['Content-Type' => 'application/json']
            );
        })->name('spec.json');

    });
