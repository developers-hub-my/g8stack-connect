<?php

use App\Enums\SpecStatus;
use App\Enums\WizardMode;
use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\ApiSpecVersion;
use App\Models\DataSource;
use App\Models\User;

it('can create an api spec', function () {
    $spec = ApiSpec::factory()->create();

    expect($spec)->toBeInstanceOf(ApiSpec::class)
        ->and($spec->uuid)->not->toBeEmpty()
        ->and($spec->name)->toBeString();
});

it('casts wizard_mode to WizardMode enum', function () {
    $spec = ApiSpec::factory()->guided()->create();

    expect($spec->wizard_mode)->toBe(WizardMode::GUIDED);
});

it('casts status to SpecStatus enum', function () {
    $spec = ApiSpec::factory()->pushed()->create();

    expect($spec->status)->toBe(SpecStatus::PUSHED);
});

it('casts openapi_spec to array', function () {
    $spec = ApiSpec::factory()->create([
        'openapi_spec' => ['openapi' => '3.1.0'],
    ]);

    expect($spec->openapi_spec)->toBeArray()
        ->and($spec->openapi_spec['openapi'])->toBe('3.1.0');
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $spec = ApiSpec::factory()->create(['user_id' => $user->id]);

    expect($spec->user)->toBeInstanceOf(User::class)
        ->and($spec->user->id)->toBe($user->id);
});

it('belongs to a data source', function () {
    $dataSource = DataSource::factory()->create();
    $spec = ApiSpec::factory()->create(['data_source_id' => $dataSource->id]);

    expect($spec->dataSource)->toBeInstanceOf(DataSource::class)
        ->and($spec->dataSource->id)->toBe($dataSource->id);
});

it('has many fields', function () {
    $spec = ApiSpec::factory()->create();
    ApiSpecField::factory()->count(3)->create(['api_spec_id' => $spec->id]);

    expect($spec->fields)->toHaveCount(3);
});

it('has many versions', function () {
    $spec = ApiSpec::factory()->create();
    ApiSpecVersion::factory()->create(['api_spec_id' => $spec->id, 'version_number' => 1]);
    ApiSpecVersion::factory()->create(['api_spec_id' => $spec->id, 'version_number' => 2]);

    expect($spec->versions)->toHaveCount(2);
});

it('uses soft deletes', function () {
    $spec = ApiSpec::factory()->create();
    $spec->delete();

    expect($spec->trashed())->toBeTrue()
        ->and(ApiSpec::withTrashed()->find($spec->id))->not->toBeNull();
});
