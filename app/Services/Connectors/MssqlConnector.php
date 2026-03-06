<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class MssqlConnector extends AbstractDatabaseConnector
{
    protected function getDriver(): string
    {
        return 'sqlsrv';
    }

    protected function buildConnectionConfig(array $credentials): array
    {
        return [
            'host' => $credentials['host'] ?? '127.0.0.1',
            'port' => $credentials['port'] ?? 1433,
            'database' => $credentials['database'] ?? '',
            'username' => $credentials['username'] ?? '',
            'password' => $credentials['password'] ?? '',
            'charset' => 'utf8',
            'prefix' => '',
        ];
    }
}
