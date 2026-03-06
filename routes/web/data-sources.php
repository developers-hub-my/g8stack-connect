<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])
    ->as('data-sources.')
    ->prefix('data-sources')
    ->group(function () {

        Route::get('/', function () {
            return view('data-sources.index');
        })->name('index');

        Route::get('/create', function () {
            return view('data-sources.create');
        })->name('create');

        Route::get('/{uuid}', function (string $uuid) {
            return view('data-sources.show', compact('uuid'));
        })->name('show');

    });
