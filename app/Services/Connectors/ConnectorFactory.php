<?php

declare(strict_types=1);

namespace App\Services\Connectors;

use App\Enums\DataSourceType;

class ConnectorFactory
{
    public static function make(DataSourceType $type): AbstractDatabaseConnector
    {
        return match ($type) {
            DataSourceType::MYSQL => new MySqlConnector,
            DataSourceType::POSTGRESQL => new PostgresConnector,
            DataSourceType::MSSQL => new MssqlConnector,
            DataSourceType::SQLITE => new SqliteConnector,
        };
    }
}
