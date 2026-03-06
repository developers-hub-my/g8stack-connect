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

    });
