<?php

declare(strict_types=1);

namespace App\Services\Connectors;

use App\Contracts\DataSourceConnector;
use App\DataTransferObjects\ConnectionResult;
use App\DataTransferObjects\PreviewResult;
use App\DataTransferObjects\SchemaResult;
use App\Models\ConnectionAudit;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

abstract class AbstractDatabaseConnector implements DataSourceConnector
{
    protected ?string $connectionName = null;

    protected ?int $userId = null;

    protected ?int $dataSourceId = null;

    abstract protected function getDriver(): string;

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function setDataSourceId(int $dataSourceId): static
    {
        $this->dataSourceId = $dataSourceId;

        return $this;
    }

    public function connect(array $credentials): ConnectionResult
    {
        try {
            $this->connectionName = 'g8connect_'.uniqid();

            Config::set("database.connections.{$this->connectionName}", array_merge(
                $this->buildConnectionConfig($credentials),
                ['driver' => $this->getDriver()]
            ));

            Schema::connection($this->connectionName)->getConnection()->getPdo();

            $this->logAudit('connect', 'success', 'Connection established successfully.');

            return new ConnectionResult(
                success: true,
                message: 'Connection established successfully.',
                metadata: ['driver' => $this->getDriver()],
            );
        } catch (\Throwable $e) {
            $this->logAudit('connect', 'failed', $e->getMessage());

            return new ConnectionResult(
                success: false,
                message: 'Connection failed: '.$e->getMessage(),
            );
        }
    }

    public function introspect(): SchemaResult
    {
        try {
            if (! $this->connectionName) {
                return new SchemaResult(success: false, message: 'Not connected.');
            }

            $tables = Schema::connection($this->connectionName)->getTables();

            $connection = Schema::connection($this->connectionName)->getConnection();
            $databaseName = $connection->getDatabaseName();
            $driver = $connection->getDriverName();

            $searchSchema = $connection->getConfig('search_path')
                ?? $connection->getConfig('schema')
                ?? null;

            $tableNames = collect($tables)
                ->filter(function ($table) use ($connection, $databaseName, $driver, $searchSchema) {
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

                    // Oracle: filter by connected user's schema (owner)
                    if ($driver === 'oracle') {
                        $owner = strtolower($connection->getConfig('username'));

                        return strtolower($table['schema'] ?? '') === $owner;
                    }

                    // MySQL/MSSQL: filter by database name
                    return $table['schema'] === $databaseName;
                })
                ->pluck('name')
                ->all();

            $this->logAudit('introspect', 'success', 'Schema introspected successfully.');

            return new SchemaResult(
                success: true,
                tables: $tableNames,
                message: 'Introspected '.count($tableNames).' tables.',
            );
        } catch (\Throwable $e) {
            $this->logAudit('introspect', 'failed', $e->getMessage());

            return new SchemaResult(
                success: false,
                message: 'Introspection failed: '.$e->getMessage(),
            );
        }
    }

    public function preview(string $table, int $limit = 5): PreviewResult
    {
        try {
            if (! $this->connectionName) {
                return new PreviewResult(success: false, message: 'Not connected.');
            }

            $maxRows = config('datasource.max_preview_rows', 5);
            $limit = min($limit, $maxRows);

            $columns = Schema::connection($this->connectionName)->getColumns($table);
            $columnNames = collect($columns)->pluck('name')->all();

            $rows = Schema::connection($this->connectionName)
                ->getConnection()
                ->table($table)
                ->limit($limit)
                ->get()
                ->toArray();

            $this->logAudit('preview', 'success', "Previewed {$table} ({$limit} rows).");

            return new PreviewResult(
                success: true,
                rows: array_map(fn ($row) => (array) $row, $rows),
                columns: $columnNames,
                count: count($rows),
            );
        } catch (\Throwable $e) {
            $this->logAudit('preview', 'failed', $e->getMessage());

            return new PreviewResult(
                success: false,
                message: 'Preview failed: '.$e->getMessage(),
            );
        }
    }

    public function isReadOnly(): bool
    {
        return config('datasource.enforce_readonly', true);
    }

    public function disconnect(): void
    {
        if ($this->connectionName) {
            app('db')->purge($this->connectionName);
            $this->logAudit('disconnect', 'success', 'Disconnected.');
            $this->connectionName = null;
        }
    }

    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }

    public function getColumnsForTable(string $table): array
    {
        if (! $this->connectionName) {
            return [];
        }

        return Schema::connection($this->connectionName)->getColumns($table);
    }

    abstract protected function buildConnectionConfig(array $credentials): array;

    protected function logAudit(string $action, string $status, string $message): void
    {
        if ($this->userId) {
            ConnectionAudit::create([
                'user_id' => $this->userId,
                'data_source_id' => $this->dataSourceId,
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'metadata' => ['driver' => $this->getDriver()],
                'ip_address' => request()?->ip(),
            ]);
        }
    }
}
