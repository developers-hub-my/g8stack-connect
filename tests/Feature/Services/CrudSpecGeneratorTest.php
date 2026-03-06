<?php

use App\Models\DataSourceSchema;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\CrudSpecGenerator;

it('generates a valid openapi 3.1 spec', function () {
    $schema = DataSourceSchema::factory()->create([
        'table_name' => 'products',
        'columns' => [
            ['name' => 'id', 'type' => 'bigint', 'nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'nullable' => false],
            ['name' => 'price', 'type' => 'decimal', 'nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true],
        ],
    ]);

    $generator = new CrudSpecGenerator(new PiiDetectionService);
    $spec = $generator->generate($schema);

    expect($spec['openapi'])->toBe('3.1.0')
        ->and($spec['info']['title'])->toBe('Product API')
        ->and($spec['info']['x-generator'])->toBe('G8Connect')
        ->and($spec)->toHaveKey('paths')
        ->and($spec)->toHaveKey('components');
});

it('generates all 5 crud endpoints', function () {
    $schema = DataSourceSchema::factory()->create(['table_name' => 'users']);

    $generator = new CrudSpecGenerator(new PiiDetectionService);
    $spec = $generator->generate($schema);

    $paths = $spec['paths'];

    expect($paths)->toHaveKey('/api/users')
        ->and($paths)->toHaveKey('/api/users/{id}')
        ->and($paths['/api/users'])->toHaveKey('get')
        ->and($paths['/api/users'])->toHaveKey('post')
        ->and($paths['/api/users/{id}'])->toHaveKey('get')
        ->and($paths['/api/users/{id}'])->toHaveKey('put')
        ->and($paths['/api/users/{id}'])->toHaveKey('delete');
});

it('excludes pii columns from the spec', function () {
    $schema = DataSourceSchema::factory()->withColumns([
        'id', 'name', 'email', 'password', 'ic_number',
    ])->create();

    $generator = new CrudSpecGenerator(new PiiDetectionService);
    $spec = $generator->generate($schema);

    $modelName = str($schema->table_name)->singular()->studly()->toString();
    $properties = $spec['components']['schemas'][$modelName]['properties'];

    expect($properties)->toHaveKey('id')
        ->and($properties)->toHaveKey('name')
        ->and($properties)->toHaveKey('email')
        ->and($properties)->not->toHaveKey('password')
        ->and($properties)->not->toHaveKey('ic_number');
});

it('maps database types to openapi types correctly', function () {
    $schema = DataSourceSchema::factory()->create([
        'table_name' => 'items',
        'columns' => [
            ['name' => 'id', 'type' => 'integer', 'nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'nullable' => false],
            ['name' => 'price', 'type' => 'decimal', 'nullable' => false],
            ['name' => 'active', 'type' => 'boolean', 'nullable' => false],
            ['name' => 'data', 'type' => 'json', 'nullable' => true],
        ],
    ]);

    $generator = new CrudSpecGenerator(new PiiDetectionService);
    $spec = $generator->generate($schema);

    $properties = $spec['components']['schemas']['Item']['properties'];

    expect($properties['id']['type'])->toBe('integer')
        ->and($properties['name']['type'])->toBe('string')
        ->and($properties['price']['type'])->toBe('number')
        ->and($properties['active']['type'])->toBe('boolean')
        ->and($properties['data']['type'])->toBe('object');
});
