<?php

use App\Models\ConnectionAudit;
use App\Models\DataSource;
use App\Models\User;

it('can create a connection audit', function () {
    $audit = ConnectionAudit::factory()->create();

    expect($audit)->toBeInstanceOf(ConnectionAudit::class)
        ->and($audit->uuid)->not->toBeEmpty()
        ->and($audit->action)->toBeString();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $audit = ConnectionAudit::factory()->create(['user_id' => $user->id]);

    expect($audit->user)->toBeInstanceOf(User::class)
        ->and($audit->user->id)->toBe($user->id);
});

it('belongs to a data source', function () {
    $dataSource = DataSource::factory()->create();
    $audit = ConnectionAudit::factory()->create(['data_source_id' => $dataSource->id]);

    expect($audit->dataSource)->toBeInstanceOf(DataSource::class)
        ->and($audit->dataSource->id)->toBe($dataSource->id);
});

it('does not use soft deletes', function () {
    $audit = ConnectionAudit::factory()->create();

    expect(method_exists($audit, 'trashed'))->toBeFalse();
});

it('casts metadata to array', function () {
    $audit = ConnectionAudit::factory()->create([
        'metadata' => ['source_type' => 'mysql', 'host' => 'localhost'],
    ]);

    expect($audit->metadata)->toBeArray()
        ->and($audit->metadata['source_type'])->toBe('mysql');
});
