<?php

use App\Models\DataSource;
use App\Models\DataSourceSchema;

it('can create a data source schema', function () {
    $schema = DataSourceSchema::factory()->create();

    expect($schema)->toBeInstanceOf(DataSourceSchema::class)
        ->and($schema->uuid)->not->toBeEmpty()
        ->and($schema->table_name)->toBeString();
});

it('casts columns to array', function () {
    $schema = DataSourceSchema::factory()->create();

    expect($schema->columns)->toBeArray();
});

it('casts primary_keys to array', function () {
    $schema = DataSourceSchema::factory()->create();

    expect($schema->primary_keys)->toBeArray();
});

it('belongs to a data source', function () {
    $dataSource = DataSource::factory()->create();
    $schema = DataSourceSchema::factory()->create(['data_source_id' => $dataSource->id]);

    expect($schema->dataSource)->toBeInstanceOf(DataSource::class)
        ->and($schema->dataSource->id)->toBe($dataSource->id);
});

it('supports withColumns factory state', function () {
    $schema = DataSourceSchema::factory()->withColumns(['id', 'name', 'email'])->create();

    $columnNames = collect($schema->columns)->pluck('name')->all();

    expect($columnNames)->toBe(['id', 'name', 'email']);
});
