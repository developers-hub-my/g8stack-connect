<?php

declare(strict_types=1);

namespace App\Services\Introspectors;

use App\Contracts\Introspector;
use App\Models\DataSource;
use App\Models\DataSourceSchema;
use App\Services\Connectors\AbstractFileConnector;

class FileIntrospector implements Introspector
{
    public function __construct(
        protected AbstractFileConnector $connector,
        protected DataSource $dataSource,
    ) {}

    public function getTables(): array
    {
        $result = $this->connector->introspect();

        return $result->tables;
    }

    public function getColumns(string $table): array
    {
        return $this->connector->getColumnsForTable($table);
    }

    public function getColumnTypes(string $table): array
    {
        return collect($this->getColumns($table))
            ->pluck('type', 'name')
            ->all();
    }

    public function storeSchemas(): void
    {
        $tables = $this->getTables();

        foreach ($tables as $tableName) {
            $columns = $this->getColumns($tableName);

            DataSourceSchema::updateOrCreate(
                [
                    'data_source_id' => $this->dataSource->id,
                    'table_name' => $tableName,
                ],
                [
                    'columns' => $columns,
                    'primary_keys' => [],
                    'indexes' => [],
                ],
            );
        }
    }
}
