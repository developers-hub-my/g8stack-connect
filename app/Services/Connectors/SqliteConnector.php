<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class SqliteConnector extends AbstractDatabaseConnector
{
    protected function getDriver(): string
    {
        return 'sqlite';
    }

    protected function buildConnectionConfig(array $credentials): array
    {
        return [
            'database' => $credentials['database'] ?? ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];
    }
}
