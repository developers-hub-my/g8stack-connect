<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class OracleConnector extends AbstractDatabaseConnector
{
    protected function getDriver(): string
    {
        return 'oracle';
    }

    protected function buildConnectionConfig(array $credentials): array
    {
        return [
            'host' => $credentials['host'] ?? '127.0.0.1',
            'port' => $credentials['port'] ?? 1521,
            'database' => $credentials['service_name'] ?? $credentials['database'] ?? '',
            'service_name' => $credentials['service_name'] ?? $credentials['database'] ?? '',
            'username' => $credentials['username'] ?? '',
            'password' => $credentials['password'] ?? '',
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ];
    }
}
