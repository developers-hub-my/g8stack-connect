<?php

declare(strict_types=1);

use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\ApiSpecTable;
use App\Services\ApiRuntime\ApiValidationService;
use Illuminate\Validation\ValidationException;

it('passes validation for valid data', function () {
    $spec = ApiSpec::factory()->create();
    $table = ApiSpecTable::factory()->create(['api_spec_id' => $spec->id]);

    ApiSpecField::factory()->create([
        'api_spec_id' => $spec->id,
        'api_spec_table_id' => $table->id,
        'column_name' => 'name',
        'display_name' => 'name',
        'data_type' => 'varchar',
        'is_exposed' => true,
        'is_required' => true,
    ]);

    $service = new ApiValidationService;
    $result = $service->validate($spec, ['name' => 'John'], $table);

    expect($result)->toBe(['name' => 'John']);
});

it('throws validation exception for missing required fields', function () {
    $spec = ApiSpec::factory()->create();
    $table = ApiSpecTable::factory()->create(['api_spec_id' => $spec->id]);

    ApiSpecField::factory()->create([
        'api_spec_id' => $spec->id,
        'api_spec_table_id' => $table->id,
        'column_name' => 'email',
        'display_name' => 'email',
        'data_type' => 'varchar',
        'is_exposed' => true,
        'is_required' => true,
    ]);

    $service = new ApiValidationService;
    $service->validate($spec, [], $table);
})->throws(ValidationException::class);

it('allows missing required fields on update', function () {
    $spec = ApiSpec::factory()->create();
    $table = ApiSpecTable::factory()->create(['api_spec_id' => $spec->id]);

    ApiSpecField::factory()->create([
        'api_spec_id' => $spec->id,
        'api_spec_table_id' => $table->id,
        'column_name' => 'name',
        'display_name' => 'name',
        'data_type' => 'varchar',
        'is_exposed' => true,
        'is_required' => true,
    ]);

    $service = new ApiValidationService;
    $result = $service->validate($spec, [], $table, isUpdate: true);

    expect($result)->toBe([]);
});

it('validates integer data type', function () {
    $spec = ApiSpec::factory()->create();
    $table = ApiSpecTable::factory()->create(['api_spec_id' => $spec->id]);

    ApiSpecField::factory()->create([
        'api_spec_id' => $spec->id,
        'api_spec_table_id' => $table->id,
        'column_name' => 'age',
        'display_name' => 'age',
        'data_type' => 'integer',
        'is_exposed' => true,
    ]);

    $service = new ApiValidationService;
    $service->validate($spec, ['age' => 'not-a-number'], $table);
})->throws(ValidationException::class);

it('returns all data when no fields configured', function () {
    $spec = ApiSpec::factory()->create();

    $service = new ApiValidationService;
    $result = $service->validate($spec, ['foo' => 'bar']);

    expect($result)->toBe(['foo' => 'bar']);
});
