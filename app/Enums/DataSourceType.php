<?php

declare(strict_types=1);

namespace App\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum DataSourceType: string implements Contract
{
    use InteractsWithEnum;

    case POSTGRESQL = 'postgresql';
    case MYSQL = 'mysql';
    case MSSQL = 'mssql';
    case SQLITE = 'sqlite';

    public function label(): string
    {
        return match ($this) {
            self::POSTGRESQL => 'PostgreSQL',
            self::MYSQL => 'MySQL',
            self::MSSQL => 'Microsoft SQL Server',
            self::SQLITE => 'SQLite',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::POSTGRESQL => 'Connect to a PostgreSQL database server.',
            self::MYSQL => 'Connect to a MySQL database server.',
            self::MSSQL => 'Connect to a Microsoft SQL Server database.',
            self::SQLITE => 'Connect to a SQLite database file.',
        };
    }
}
