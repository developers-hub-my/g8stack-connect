<?php

declare(strict_types=1);

use App\Services\Connectors\CsvConnector;

beforeEach(function () {
    $this->csvPath = tempnam(sys_get_temp_dir(), 'csv_test_');
    file_put_contents($this->csvPath, "name,age,email\nAlice,30,alice@example.com\nBob,25,bob@example.com\nCharlie,35,charlie@example.com\n");
});

afterEach(function () {
    if (file_exists($this->csvPath)) {
        unlink($this->csvPath);
    }
});

it('connects to a csv file successfully', function () {
    $connector = new CsvConnector;
    $result = $connector->connect(['file_path' => $this->csvPath]);

    expect($result->success)->toBeTrue()
        ->and($result->metadata['file_type'])->toBe('csv')
        ->and($result->metadata['row_count'])->toBe(3)
        ->and($result->metadata['column_count'])->toBe(3);
});

it('fails to connect with missing file', function () {
    $connector = new CsvConnector;
    $result = $connector->connect(['file_path' => '/nonexistent/file.csv']);

    expect($result->success)->toBeFalse();
});

it('introspects csv file as a single table', function () {
    $connector = new CsvConnector;
    $connector->connect(['file_path' => $this->csvPath]);

    $result = $connector->introspect();

    expect($result->success)->toBeTrue()
        ->and($result->tables)->toHaveCount(1);
});

it('previews csv rows', function () {
    $connector = new CsvConnector;
    $connector->connect(['file_path' => $this->csvPath]);

    $result = $connector->preview('data', 2);

    expect($result->success)->toBeTrue()
        ->and($result->count)->toBe(2)
        ->and($result->columns)->toBe(['name', 'age', 'email']);
});

it('infers column types from csv data', function () {
    $connector = new CsvConnector;
    $connector->connect(['file_path' => $this->csvPath]);

    $columns = $connector->getColumnsForTable('data');

    $types = collect($columns)->pluck('type', 'name')->all();

    expect($types['name'])->toBe('varchar')
        ->and($types['age'])->toBe('integer');
});

it('is always read only', function () {
    $connector = new CsvConnector;

    expect($connector->isReadOnly())->toBeTrue();
});
