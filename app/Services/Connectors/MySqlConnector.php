<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class MySqlConnector extends AbstractDatabaseConnector
{
    protected function getDriver(): string
    {
        return 'mysql';
    }

    protected function buildConnectionConfig(array $credentials): array
    {
        return [
            'host' => $credentials['host'] ?? '127.0.0.1',
            'port' => $credentials['port'] ?? 3306,
            'database' => $credentials['database'] ?? '',
            'username' => $credentials['username'] ?? '',
            'password' => $credentials['password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];
    }
}
