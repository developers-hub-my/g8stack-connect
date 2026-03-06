<?php

declare(strict_types=1);

namespace App\Services\Introspectors;

use App\Contracts\Introspector;
use App\Models\DataSource;
use App\Models\DataSourceSchema;
use App\Services\Connectors\AbstractDatabaseConnector;
use Illuminate\Support\Facades\Schema;

class DatabaseIntrospector implements Introspector
{
    public function __construct(
        protected AbstractDatabaseConnector $connector,
    ) {}

    public function getTables(): array
    {
        $connectionName = $this->connector->getConnectionName();

        if (! $connectionName) {
            return [];
        }

        $connection = Schema::connection($connectionName)->getConnection();
        $tables = Schema::connection($connectionName)->getTables();
        $databaseName = $connection->getDatabaseName();
        $driver = $connection->getDriverName();

        $searchSchema = $connection->getConfig('search_path')
            ?? $connection->getConfig('schema')
            ?? null;

        return collect($tables)
            ->filter(function ($table) use ($databaseName, $driver, $searchSchema) {
                if (! isset($table['schema'])) {
                    return true;
                }

                if ($driver === 'sqlite') {
                    return $table['schema'] === 'main';
                }

                // PostgreSQL: filter by schema (e.g. 'public'), not database name
                if ($driver === 'pgsql') {
                    return $table['schema'] === ($searchSchema ?? 'public');
                }

                // MySQL/MSSQL: filter by database name
                return $table['schema'] === $databaseName;
            })
            ->pluck('name')
            ->all();
    }

    public function getColumns(string $table): array
    {
        $connectionName = $this->connector->getConnectionName();

        if (! $connectionName) {
            return [];
        }

        return Schema::connection($connectionName)->getColumns($table);
    }

    public function getColumnTypes(string $table): array
    {
        $columns = $this->getColumns($table);

        return collect($columns)->mapWithKeys(fn (array $column) => [
            $column['name'] => $column['type_name'] ?? $column['type'] ?? 'unknown',
        ])->all();
    }

    public function storeSchemas(DataSource $dataSource): array
    {
        $tables = $this->getTables();
        $schemas = [];

        foreach ($tables as $tableName) {
            $columns = $this->getColumns($tableName);
            $columnData = collect($columns)->map(fn (array $col) => [
                'name' => $col['name'],
                'type' => $col['type_name'] ?? $col['type'] ?? 'unknown',
                'nullable' => $col['nullable'] ?? false,
                'default' => $col['default'] ?? null,
            ])->all();

            $schemas[] = DataSourceSchema::updateOrCreate(
                [
                    'data_source_id' => $dataSource->id,
                    'table_name' => $tableName,
                ],
                [
                    'columns' => $columnData,
                    'primary_keys' => $this->getPrimaryKeys($tableName),
                    'indexes' => $this->getIndexes($tableName),
                ]
            );
        }

        return $schemas;
    }

    protected function getPrimaryKeys(string $table): array
    {
        $connectionName = $this->connector->getConnectionName();

        if (! $connectionName) {
            return [];
        }

        $indexes = Schema::connection($connectionName)->getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['primary'] ?? false) === true) {
                return $index['columns'] ?? [];
            }
        }

        return [];
    }

    protected function getIndexes(string $table): array
    {
        $connectionName = $this->connector->getConnectionName();

        if (! $connectionName) {
            return [];
        }

        return Schema::connection($connectionName)->getIndexes($table);
    }
}
