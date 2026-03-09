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

it('identifies file-based sources', function () {
    expect(DataSourceType::CSV->isFile())->toBeTrue()
        ->and(DataSourceType::JSON->isFile())->toBeTrue()
        ->and(DataSourceType::EXCEL->isFile())->toBeTrue()
        ->and(DataSourceType::POSTGRESQL->isFile())->toBeFalse()
        ->and(DataSourceType::MYSQL->isFile())->toBeFalse()
        ->and(DataSourceType::MSSQL->isFile())->toBeFalse()
        ->and(DataSourceType::SQLITE->isFile())->toBeFalse();
});

it('identifies database sources', function () {
    expect(DataSourceType::POSTGRESQL->isDatabase())->toBeTrue()
        ->and(DataSourceType::MYSQL->isDatabase())->toBeTrue()
        ->and(DataSourceType::MSSQL->isDatabase())->toBeTrue()
        ->and(DataSourceType::SQLITE->isDatabase())->toBeTrue()
        ->and(DataSourceType::CSV->isDatabase())->toBeFalse()
        ->and(DataSourceType::JSON->isDatabase())->toBeFalse()
        ->and(DataSourceType::EXCEL->isDatabase())->toBeFalse();
});
