<?php

use App\Enums\DataSourceType;

it('has the correct values', function () {
    expect(DataSourceType::cases())->toHaveCount(7)
        ->and(DataSourceType::POSTGRESQL->value)->toBe('postgresql')
        ->and(DataSourceType::MYSQL->value)->toBe('mysql')
        ->and(DataSourceType::MSSQL->value)->toBe('mssql')
        ->and(DataSourceType::SQLITE->value)->toBe('sqlite')
        ->and(DataSourceType::CSV->value)->toBe('csv')
        ->and(DataSourceType::JSON->value)->toBe('json')
        ->and(DataSourceType::EXCEL->value)->toBe('excel');
});

it('has labels for all cases', function () {
    foreach (DataSourceType::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

it('has descriptions for all cases', function () {
    foreach (DataSourceType::cases() as $case) {
        expect($case->description())->toBeString()->not->toBeEmpty();
    }
});
