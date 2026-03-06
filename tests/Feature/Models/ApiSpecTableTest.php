<?php

declare(strict_types=1);

use App\Models\ApiSpec;
use App\Models\ApiSpecTable;

it('can be created with factory', function () {
    $table = ApiSpecTable::factory()->create();

    expect($table)->toBeInstanceOf(ApiSpecTable::class)
        ->and($table->resource_name)->toBeString()
        ->and($table->table_name)->toBeString();
});

it('belongs to an api spec', function () {
    $table = ApiSpecTable::factory()->create();

    expect($table->apiSpec)->toBeInstanceOf(ApiSpec::class);
});

it('has default operations as read-only', function () {
    $table = new ApiSpecTable;

    $defaults = $table->getDefaultOperations();

    expect($defaults['list'])->toBeTrue()
        ->and($defaults['show'])->toBeTrue()
        ->and($defaults['create'])->toBeFalse()
        ->and($defaults['update'])->toBeFalse()
        ->and($defaults['delete'])->toBeFalse();
});

it('checks if operation is allowed', function () {
    $table = ApiSpecTable::factory()->readOnly()->create();

    expect($table->isOperationAllowed('list'))->toBeTrue()
        ->and($table->isOperationAllowed('show'))->toBeTrue()
        ->and($table->isOperationAllowed('create'))->toBeFalse()
        ->and($table->isOperationAllowed('delete'))->toBeFalse();
});

it('allows all operations with full crud factory', function () {
    $table = ApiSpecTable::factory()->fullCrud()->create();

    expect($table->isOperationAllowed('list'))->toBeTrue()
        ->and($table->isOperationAllowed('show'))->toBeTrue()
        ->and($table->isOperationAllowed('create'))->toBeTrue()
        ->and($table->isOperationAllowed('update'))->toBeTrue()
        ->and($table->isOperationAllowed('delete'))->toBeTrue();
});

it('defaults to read-only when operations is null', function () {
    $table = ApiSpecTable::factory()->create(['operations' => null]);

    expect($table->isOperationAllowed('list'))->toBeTrue()
        ->and($table->isOperationAllowed('create'))->toBeFalse();
});

it('casts operations as array', function () {
    $table = ApiSpecTable::factory()->create();

    expect($table->operations)->toBeArray();
});
