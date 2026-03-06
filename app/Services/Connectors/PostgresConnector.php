<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class PostgresConnector extends AbstractDatabaseConnector
{
    protected function getDriver(): string
    {
        return 'pgsql';
    }

    protected function buildConnectionConfig(array $credentials): array
    {
        return [
            'host' => $credentials['host'] ?? '127.0.0.1',
            'port' => $credentials['port'] ?? 5432,
            'database' => $credentials['database'] ?? '',
            'username' => $credentials['username'] ?? '',
            'password' => $credentials['password'] ?? '',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => $credentials['schema'] ?? 'public',
        ];
    }
}
