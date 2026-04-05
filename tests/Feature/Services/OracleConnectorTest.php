<?php

use App\Enums\DataSourceType;
use App\Services\Connectors\ConnectorFactory;
use App\Services\Connectors\OracleConnector;

it('returns oracle as driver name', function () {
    $connector = new OracleConnector;

    $reflection = new ReflectionMethod($connector, 'getDriver');

    expect($reflection->invoke($connector))->toBe('oracle');
});

it('builds correct connection config with service_name', function () {
    $connector = new OracleConnector;

    $reflection = new ReflectionMethod($connector, 'buildConnectionConfig');

    $config = $reflection->invoke($connector, [
        'host' => '10.0.0.1',
        'port' => 1522,
        'service_name' => 'MYSERVICE',
        'username' => 'testuser',
        'password' => 'secret',
    ]);

    expect($config)
        ->toHaveKey('host', '10.0.0.1')
        ->toHaveKey('port', 1522)
        ->toHaveKey('service_name', 'MYSERVICE')
        ->toHaveKey('database', 'MYSERVICE')
        ->toHaveKey('username', 'testuser')
        ->toHaveKey('password', 'secret')
        ->toHaveKey('charset', 'AL32UTF8');
});

it('falls back to database when service_name is not provided', function () {
    $connector = new OracleConnector;

    $reflection = new ReflectionMethod($connector, 'buildConnectionConfig');

    $config = $reflection->invoke($connector, [
        'database' => 'FREEPDB1',
        'username' => 'testuser',
        'password' => 'secret',
    ]);

    expect($config)
        ->toHaveKey('service_name', 'FREEPDB1')
        ->toHaveKey('database', 'FREEPDB1');
});

it('defaults to port 1521 and AL32UTF8 charset', function () {
    $connector = new OracleConnector;

    $reflection = new ReflectionMethod($connector, 'buildConnectionConfig');

    $config = $reflection->invoke($connector, []);

    expect($config)
        ->toHaveKey('port', 1521)
        ->toHaveKey('charset', 'AL32UTF8')
        ->toHaveKey('host', '127.0.0.1');
});

it('includes ORACLE in DataSourceType enum', function () {
    $values = array_column(DataSourceType::cases(), 'value');

    expect($values)->toContain('oracle');
});

it('marks ORACLE as database type not file type', function () {
    expect(DataSourceType::ORACLE->isDatabase())->toBeTrue()
        ->and(DataSourceType::ORACLE->isFile())->toBeFalse();
});

it('has correct label and description', function () {
    expect(DataSourceType::ORACLE->label())->toBe('Oracle Database')
        ->and(DataSourceType::ORACLE->description())->toBe('Connect to an Oracle database server.');
});

it('connects to Oracle and introspects tables', function () {
    $connector = ConnectorFactory::make(DataSourceType::ORACLE);

    $result = $connector->connect([
        'host' => '127.0.0.1',
        'port' => env('G8_ORACLE_PORT', 1521),
        'service_name' => 'FREEPDB1',
        'username' => 'g8test',
        'password' => 'g8testpass',
    ]);

    expect($result->success)->toBeTrue();

    $schema = $connector->introspect();

    expect($schema->success)->toBeTrue()
        ->and($schema->tables)->toContain('employees')
        ->and($schema->tables)->toContain('departments')
        ->and($schema->tables)->toContain('projects');

    $preview = $connector->preview('employees', 5);

    expect($preview->success)->toBeTrue()
        ->and($preview->count)->toBeGreaterThan(0)
        ->and($preview->columns)->toContain('name')
        ->and($preview->columns)->toContain('email');

    $connector->disconnect();
})->skip(! env('G8_ORACLE_ENABLED', false), 'Oracle Docker container not available');
