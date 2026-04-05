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
    case ORACLE = 'oracle';
    case CSV = 'csv';
    case JSON = 'json';
    case EXCEL = 'excel';

    public function label(): string
    {
        return match ($this) {
            self::POSTGRESQL => 'PostgreSQL',
            self::MYSQL => 'MySQL',
            self::MSSQL => 'Microsoft SQL Server',
            self::SQLITE => 'SQLite',
            self::ORACLE => 'Oracle Database',
            self::CSV => 'CSV File',
            self::JSON => 'JSON File',
            self::EXCEL => 'Excel File',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::POSTGRESQL => 'Connect to a PostgreSQL database server.',
            self::MYSQL => 'Connect to a MySQL database server.',
            self::MSSQL => 'Connect to a Microsoft SQL Server database.',
            self::SQLITE => 'Connect to a SQLite database file.',
            self::ORACLE => 'Connect to an Oracle database server.',
            self::CSV => 'Upload a CSV file as a read-only data source.',
            self::JSON => 'Upload a JSON file as a read-only data source.',
            self::EXCEL => 'Upload an Excel (.xlsx) file as a read-only data source.',
        };
    }

    public function isFile(): bool
    {
        return in_array($this, [self::CSV, self::JSON, self::EXCEL]);
    }

    public function isDatabase(): bool
    {
        return ! $this->isFile();
    }
}
