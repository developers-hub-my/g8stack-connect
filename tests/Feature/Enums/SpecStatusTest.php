<?php

use App\Enums\SpecStatus;

it('has the correct values', function () {
    expect(SpecStatus::cases())->toHaveCount(5)
        ->and(SpecStatus::PENDING->value)->toBe('pending')
        ->and(SpecStatus::PUSHED->value)->toBe('pushed')
        ->and(SpecStatus::APPROVED->value)->toBe('approved')
        ->and(SpecStatus::REJECTED->value)->toBe('rejected')
        ->and(SpecStatus::DEPLOYED->value)->toBe('deployed');
});

it('has labels for all cases', function () {
    foreach (SpecStatus::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

it('has descriptions for all cases', function () {
    foreach (SpecStatus::cases() as $case) {
        expect($case->description())->toBeString()->not->toBeEmpty();
    }
});
