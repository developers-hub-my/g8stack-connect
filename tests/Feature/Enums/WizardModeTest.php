<?php

use App\Enums\WizardMode;

it('has the correct values', function () {
    expect(WizardMode::cases())->toHaveCount(3)
        ->and(WizardMode::SIMPLE->value)->toBe('simple')
        ->and(WizardMode::GUIDED->value)->toBe('guided')
        ->and(WizardMode::ADVANCED->value)->toBe('advanced');
});

it('has labels for all cases', function () {
    foreach (WizardMode::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

it('has descriptions for all cases', function () {
    foreach (WizardMode::cases() as $case) {
        expect($case->description())->toBeString()->not->toBeEmpty();
    }
});
