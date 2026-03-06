<?php

use App\Enums\ConnectionStatus;

it('has the correct values', function () {
    expect(ConnectionStatus::cases())->toHaveCount(4)
        ->and(ConnectionStatus::CONNECTED->value)->toBe('connected')
        ->and(ConnectionStatus::FAILED->value)->toBe('failed')
        ->and(ConnectionStatus::INTROSPECTED->value)->toBe('introspected')
        ->and(ConnectionStatus::DISCONNECTED->value)->toBe('disconnected');
});

it('has labels for all cases', function () {
    foreach (ConnectionStatus::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

it('has descriptions for all cases', function () {
    foreach (ConnectionStatus::cases() as $case) {
        expect($case->description())->toBeString()->not->toBeEmpty();
    }
});
