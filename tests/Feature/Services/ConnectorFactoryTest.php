<?php

use App\Enums\DataSourceType;
use App\Services\Connectors\ConnectorFactory;
use App\Services\Connectors\MssqlConnector;
use App\Services\Connectors\MySqlConnector;
use App\Services\Connectors\PostgresConnector;
use App\Services\Connectors\SqliteConnector;

it('resolves mysql connector', function () {
    $connector = ConnectorFactory::make(DataSourceType::MYSQL);

    expect($connector)->toBeInstanceOf(MySqlConnector::class);
});

it('resolves postgresql connector', function () {
    $connector = ConnectorFactory::make(DataSourceType::POSTGRESQL);

    expect($connector)->toBeInstanceOf(PostgresConnector::class);
});

it('resolves mssql connector', function () {
    $connector = ConnectorFactory::make(DataSourceType::MSSQL);

    expect($connector)->toBeInstanceOf(MssqlConnector::class);
});

it('resolves sqlite connector', function () {
    $connector = ConnectorFactory::make(DataSourceType::SQLITE);

    expect($connector)->toBeInstanceOf(SqliteConnector::class);
});

it('can connect to sqlite in memory', function () {
    $connector = ConnectorFactory::make(DataSourceType::SQLITE);

    $result = $connector->connect(['database' => ':memory:']);

    expect($result->success)->toBeTrue()
        ->and($result->message)->toContain('successfully');

    $connector->disconnect();
});

it('can introspect a sqlite database', function () {
    $connector = ConnectorFactory::make(DataSourceType::SQLITE);
    $connector->connect(['database' => ':memory:']);

    $connection = app('db')->connection($connector->getConnectionName());
    $connection->statement('CREATE TABLE test_users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

    $result = $connector->introspect();

    expect($result->success)->toBeTrue()
        ->and($result->tables)->toContain('test_users');

    $connector->disconnect();
});

it('enforces read-only by default', function () {
    $connector = ConnectorFactory::make(DataSourceType::SQLITE);

    expect($connector->isReadOnly())->toBeTrue();
});
