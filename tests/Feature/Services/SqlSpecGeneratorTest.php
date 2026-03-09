<?php

use App\Models\ApiSpec;
use App\Models\DataSource;
use App\Models\User;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\SqlSpecGenerator;

beforeEach(function () {
    $this->generator = new SqlSpecGenerator(new PiiDetectionService);
    $this->user = User::factory()->create();
    $this->dataSource = DataSource::factory()->create(['user_id' => $this->user->id]);
    $this->spec = ApiSpec::factory()->create([
        'user_id' => $this->user->id,
        'data_source_id' => $this->dataSource->id,
        'wizard_mode' => 'advanced',
        'slug' => 'hr-api',
    ]);
});

it('generates a valid openapi 3.1 spec', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'active-employees',
        'result_columns' => [
            ['name' => 'id', 'type' => 'integer'],
            ['name' => 'name', 'type' => 'varchar'],
            ['name' => 'email', 'type' => 'varchar'],
        ],
        'parameters' => ['department'],
    ]);

    expect($result['openapi'])->toBe('3.1.0')
        ->and($result['info']['x-generator'])->toBe('G8Connect')
        ->and($result['info']['x-query-mode'])->toBe('advanced');
});

it('generates GET-only endpoint', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'sales-summary',
        'result_columns' => [
            ['name' => 'region', 'type' => 'varchar'],
            ['name' => 'total', 'type' => 'decimal'],
        ],
        'parameters' => [],
    ]);

    $paths = array_keys($result['paths']);
    expect($paths)->toHaveCount(1);

    $path = $paths[0];
    expect($result['paths'][$path])->toHaveKey('get')
        ->and($result['paths'][$path])->not->toHaveKey('post')
        ->and($result['paths'][$path])->not->toHaveKey('put')
        ->and($result['paths'][$path])->not->toHaveKey('delete');
});

it('includes query parameters in spec', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'filtered-data',
        'result_columns' => [
            ['name' => 'id', 'type' => 'integer'],
        ],
        'parameters' => ['status', 'department'],
    ]);

    $path = array_keys($result['paths'])[0];
    $params = $result['paths'][$path]['get']['parameters'];
    $paramNames = collect($params)->pluck('name')->all();

    expect($paramNames)->toContain('status', 'department', 'page', 'per_page');

    // User-defined params should be required
    $statusParam = collect($params)->firstWhere('name', 'status');
    expect($statusParam['required'])->toBeTrue();

    // Pagination params should not be required
    $pageParam = collect($params)->firstWhere('name', 'page');
    expect($pageParam['required'])->toBeFalse();
});

it('excludes pii columns from spec', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'user-data',
        'result_columns' => [
            ['name' => 'id', 'type' => 'integer'],
            ['name' => 'name', 'type' => 'varchar'],
            ['name' => 'password', 'type' => 'varchar'],
            ['name' => 'ic_number', 'type' => 'varchar'],
        ],
        'parameters' => [],
    ]);

    $schemaName = array_keys($result['components']['schemas'])[0];
    $properties = array_keys($result['components']['schemas'][$schemaName]['properties']);

    expect($properties)->toContain('id', 'name')
        ->and($properties)->not->toContain('password', 'ic_number');
});

it('maps result column types to openapi types', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'typed-data',
        'result_columns' => [
            ['name' => 'count', 'type' => 'integer'],
            ['name' => 'amount', 'type' => 'decimal'],
            ['name' => 'active', 'type' => 'boolean'],
            ['name' => 'created', 'type' => 'date'],
            ['name' => 'label', 'type' => 'varchar'],
        ],
        'parameters' => [],
    ]);

    $schemaName = array_keys($result['components']['schemas'])[0];
    $props = $result['components']['schemas'][$schemaName]['properties'];

    expect($props['count']['type'])->toBe('integer')
        ->and($props['amount']['type'])->toBe('number')
        ->and($props['active']['type'])->toBe('boolean')
        ->and($props['created']['type'])->toBe('string')
        ->and($props['label']['type'])->toBe('string');
});

it('uses slug-based endpoint path', function () {
    $result = $this->generator->generate($this->spec, [
        'endpoint_name' => 'monthly-report',
        'result_columns' => [
            ['name' => 'month', 'type' => 'varchar'],
        ],
        'parameters' => [],
    ]);

    $paths = array_keys($result['paths']);
    expect($paths[0])->toBe('/api/connect/hr-api/monthly-report');
});
