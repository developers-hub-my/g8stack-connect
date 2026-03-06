<?php

use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Models\ApiSpec;
use App\Models\ConnectionAudit;
use App\Models\DataSource;
use App\Models\DataSourceSchema;
use App\Models\User;

it('can create a data source', function () {
    $dataSource = DataSource::factory()->create();

    expect($dataSource)->toBeInstanceOf(DataSource::class)
        ->and($dataSource->uuid)->not->toBeEmpty()
        ->and($dataSource->name)->toBeString();
});

it('casts type to DataSourceType enum', function () {
    $dataSource = DataSource::factory()->mysql()->create();

    expect($dataSource->type)->toBe(DataSourceType::MYSQL);
});

it('casts status to ConnectionStatus enum', function () {
    $dataSource = DataSource::factory()->connected()->create();

    expect($dataSource->status)->toBe(ConnectionStatus::CONNECTED);
});

it('encrypts credentials at rest', function () {
    $dataSource = DataSource::factory()->create([
        'credentials' => ['password' => 'super_secret'],
    ]);

    $raw = $dataSource->getRawOriginal('credentials');

    expect($raw)->not->toContain('super_secret')
        ->and($dataSource->credentials)->toBeArray()
        ->and($dataSource->credentials['password'])->toBe('super_secret');
});

it('hides credentials from serialization', function () {
    $dataSource = DataSource::factory()->create();

    $array = $dataSource->toArray();

    expect($array)->not->toHaveKey('credentials');
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $dataSource = DataSource::factory()->create(['user_id' => $user->id]);

    expect($dataSource->user)->toBeInstanceOf(User::class)
        ->and($dataSource->user->id)->toBe($user->id);
});

it('has many schemas', function () {
    $dataSource = DataSource::factory()->create();
    DataSourceSchema::factory()->count(3)->create(['data_source_id' => $dataSource->id]);

    expect($dataSource->schemas)->toHaveCount(3);
});

it('has many specs', function () {
    $dataSource = DataSource::factory()->create();
    ApiSpec::factory()->count(2)->create(['data_source_id' => $dataSource->id]);

    expect($dataSource->specs)->toHaveCount(2);
});

it('has many connection audits', function () {
    $dataSource = DataSource::factory()->create();
    ConnectionAudit::factory()->count(2)->create(['data_source_id' => $dataSource->id]);

    expect($dataSource->connectionAudits)->toHaveCount(2);
});

it('uses soft deletes', function () {
    $dataSource = DataSource::factory()->create();
    $dataSource->delete();

    expect($dataSource->trashed())->toBeTrue()
        ->and(DataSource::withTrashed()->find($dataSource->id))->not->toBeNull();
});
