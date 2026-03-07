<?php

declare(strict_types=1);

use App\Services\Connectors\JsonConnector;

beforeEach(function () {
    $this->jsonPath = tempnam(sys_get_temp_dir(), 'json_test_');
});

afterEach(function () {
    if (file_exists($this->jsonPath)) {
        unlink($this->jsonPath);
    }
});

it('connects to a json array file', function () {
    file_put_contents($this->jsonPath, json_encode([
        ['id' => 1, 'name' => 'Alice', 'active' => true],
        ['id' => 2, 'name' => 'Bob', 'active' => false],
    ]));

    $connector = new JsonConnector;
    $result = $connector->connect(['file_path' => $this->jsonPath]);

    expect($result->success)->toBeTrue()
        ->and($result->metadata['row_count'])->toBe(2)
        ->and($result->metadata['column_count'])->toBe(3);
});

it('connects to a json wrapper object with data key', function () {
    file_put_contents($this->jsonPath, json_encode([
        'data' => [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ],
    ]));

    $connector = new JsonConnector;
    $result = $connector->connect(['file_path' => $this->jsonPath]);

    expect($result->success)->toBeTrue()
        ->and($result->metadata['row_count'])->toBe(2);
});

it('fails on invalid json', function () {
    file_put_contents($this->jsonPath, 'not valid json{{{');

    $connector = new JsonConnector;
    $result = $connector->connect(['file_path' => $this->jsonPath]);

    expect($result->success)->toBeFalse();
});

it('fails on non-tabular json', function () {
    file_put_contents($this->jsonPath, json_encode(['key' => 'value', 'number' => 42]));

    $connector = new JsonConnector;
    $result = $connector->connect(['file_path' => $this->jsonPath]);

    expect($result->success)->toBeFalse();
});

it('previews json rows', function () {
    file_put_contents($this->jsonPath, json_encode([
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
        ['id' => 3, 'name' => 'Charlie'],
    ]));

    $connector = new JsonConnector;
    $connector->connect(['file_path' => $this->jsonPath]);

    $result = $connector->preview('data', 2);

    expect($result->success)->toBeTrue()
        ->and($result->count)->toBe(2);
});

it('infers integer type from json data', function () {
    file_put_contents($this->jsonPath, json_encode([
        ['id' => 1, 'name' => 'Alice', 'score' => 95.5],
        ['id' => 2, 'name' => 'Bob', 'score' => 87.3],
    ]));

    $connector = new JsonConnector;
    $connector->connect(['file_path' => $this->jsonPath]);

    $columns = $connector->getColumnsForTable('data');
    $types = collect($columns)->pluck('type', 'name')->all();

    expect($types['id'])->toBe('integer')
        ->and($types['name'])->toBe('varchar')
        ->and($types['score'])->toBe('decimal');
});

it('is always read only', function () {
    $connector = new JsonConnector;

    expect($connector->isReadOnly())->toBeTrue();
});
