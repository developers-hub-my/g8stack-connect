<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;
use App\Services\PiiDetection\PiiDetectionService;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ApiQueryService
{
    public function list(ApiSpec $spec, Request $request, ?string $tableName = null): array
    {
        $connection = $this->connect($spec);
        $table = $tableName ?? $this->getTable($spec);
        $columns = $this->getExposedColumns($spec, $table);

        $query = $connection->table($table)->select($columns);

        $this->applyFilters($spec, $query, $request, $table);
        $this->applySorting($spec, $query, $request, $table);

        $config = $spec->configuration ?? [];
        $pagination = $config['pagination'] ?? true;

        if ($pagination) {
            $perPage = min(
                (int) $request->input('per_page', $config['per_page'] ?? 15),
                100,
            );
            $page = max((int) $request->input('page', 1), 1);

            $total = $connection->table($table)->count();
            $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get()->toArray();

            return [
                'data' => array_map(fn ($row) => (array) $row, $rows),
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ];
        }

        $rows = $query->limit(1000)->get()->toArray();

        return [
            'data' => array_map(fn ($row) => (array) $row, $rows),
        ];
    }

    public function find(ApiSpec $spec, string $id, ?string $tableName = null): ?array
    {
        $connection = $this->connect($spec);
        $table = $tableName ?? $this->getTable($spec);
        $columns = $this->getExposedColumns($spec, $table);
        $primaryKey = $this->getPrimaryKey($spec, $table);

        $row = $connection->table($table)
            ->select($columns)
            ->where($primaryKey, $id)
            ->first();

        return $row ? (array) $row : null;
    }

    public function create(ApiSpec $spec, array $data, ?string $tableName = null): array
    {
        $connection = $this->connect($spec);
        $table = $tableName ?? $this->getTable($spec);

        $id = $connection->table($table)->insertGetId($data);

        return $this->find($spec, (string) $id, $table) ?? $data;
    }

    public function update(ApiSpec $spec, string $id, array $data, ?string $tableName = null): ?array
    {
        $connection = $this->connect($spec);
        $table = $tableName ?? $this->getTable($spec);
        $primaryKey = $this->getPrimaryKey($spec, $table);

        $affected = $connection->table($table)
            ->where($primaryKey, $id)
            ->update($data);

        if ($affected === 0) {
            $exists = $connection->table($table)->where($primaryKey, $id)->exists();
            if (! $exists) {
                return null;
            }
        }

        return $this->find($spec, $id, $table);
    }

    public function delete(ApiSpec $spec, string $id, ?string $tableName = null): bool
    {
        $connection = $this->connect($spec);
        $table = $tableName ?? $this->getTable($spec);
        $primaryKey = $this->getPrimaryKey($spec, $table);

        return $connection->table($table)
            ->where($primaryKey, $id)
            ->delete() > 0;
    }

    protected function connect(ApiSpec $spec): Connection
    {
        $dataSource = $spec->dataSource;
        $credentials = $dataSource->credentials;
        $connectionName = 'g8connect_runtime_'.$spec->id;

        if (! Config::has("database.connections.{$connectionName}")) {
            $driver = $dataSource->type->value;

            $laravelDriver = match ($driver) {
                'postgresql' => 'pgsql',
                'mssql' => 'sqlsrv',
                default => $driver,
            };

            $config = match ($laravelDriver) {
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => $credentials['database'] ?? '',
                ],
                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => $credentials['host'] ?? '127.0.0.1',
                    'port' => $credentials['port'] ?? 5432,
                    'database' => $credentials['database'] ?? '',
                    'username' => $credentials['username'] ?? '',
                    'password' => $credentials['password'] ?? '',
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema' => $credentials['schema'] ?? 'public',
                ],
                default => [
                    'driver' => $laravelDriver,
                    'host' => $credentials['host'] ?? '127.0.0.1',
                    'port' => $credentials['port'] ?? 3306,
                    'database' => $credentials['database'] ?? '',
                    'username' => $credentials['username'] ?? '',
                    'password' => $credentials['password'] ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
            };

            Config::set("database.connections.{$connectionName}", $config);
        }

        return app('db')->connection($connectionName);
    }

    protected function getTable(ApiSpec $spec): string
    {
        $tables = $spec->selected_tables ?? [];

        return $tables[0] ?? '';
    }

    protected function getExposedColumns(ApiSpec $spec, string $tableName): array
    {
        // Check table-specific fields first
        $specTable = $spec->tables()->where('table_name', $tableName)->first();

        if ($specTable) {
            $fields = $specTable->fields()->where('is_exposed', true)->get();
            if ($fields->isNotEmpty()) {
                return $fields->pluck('column_name')->values()->all();
            }
        }

        // Fall back to spec-level fields
        if ($spec->fields->isNotEmpty()) {
            return $spec->fields
                ->filter(fn ($f) => $f->is_exposed)
                ->pluck('column_name')
                ->values()
                ->all();
        }

        // Simple mode — use schema columns minus PII
        $schema = $spec->dataSource->schemas()
            ->where('table_name', $tableName)
            ->first();

        if (! $schema) {
            return ['*'];
        }

        $piiScanner = new PiiDetectionService;
        $columnNames = collect($schema->columns)->pluck('name')->all();
        $piiResult = $piiScanner->scan($columnNames);

        return collect($columnNames)
            ->reject(fn ($col) => in_array($col, $piiResult->flagged))
            ->values()
            ->all();
    }

    protected function getPrimaryKey(ApiSpec $spec, string $tableName): string
    {
        $schema = $spec->dataSource->schemas()
            ->where('table_name', $tableName)
            ->first();

        $primaryKeys = $schema->primary_keys ?? ['id'];

        return $primaryKeys[0] ?? 'id';
    }

    protected function applyFilters(ApiSpec $spec, $query, Request $request, string $tableName): void
    {
        $filters = $request->input('filter', []);

        if (empty($filters) || ! is_array($filters)) {
            return;
        }

        // Get filterable columns from table-specific or spec-level fields
        $specTable = $spec->tables()->where('table_name', $tableName)->first();
        $fields = $specTable
            ? $specTable->fields()->where('is_filterable', true)->where('is_exposed', true)->get()
            : $spec->fields->filter(fn ($f) => $f->is_filterable && $f->is_exposed);

        // Build reverse map: display_name → column_name for filter keys
        $reverseMap = [];
        $filterableColumns = [];
        foreach ($fields as $field) {
            $displayName = $field->display_name ?? $field->column_name;
            $reverseMap[$displayName] = $field->column_name;
            $filterableColumns[] = $field->column_name;
        }

        foreach ($filters as $key => $value) {
            $column = $reverseMap[$key] ?? $key;
            if (in_array($column, $filterableColumns)) {
                $query->where($column, $value);
            }
        }
    }

    protected function applySorting(ApiSpec $spec, $query, Request $request, string $tableName): void
    {
        $sort = $request->input('sort');

        if (! $sort) {
            return;
        }

        $direction = 'asc';
        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $sort = substr($sort, 1);
        }

        $specTable = $spec->tables()->where('table_name', $tableName)->first();
        $fields = $specTable
            ? $specTable->fields()->where('is_sortable', true)->where('is_exposed', true)->get()
            : $spec->fields->filter(fn ($f) => $f->is_sortable && $f->is_exposed);

        // Build reverse map for sort field
        $reverseMap = [];
        $sortableColumns = [];
        foreach ($fields as $field) {
            $displayName = $field->display_name ?? $field->column_name;
            $reverseMap[$displayName] = $field->column_name;
            $sortableColumns[] = $field->column_name;
        }

        $column = $reverseMap[$sort] ?? $sort;
        if (in_array($column, $sortableColumns)) {
            $query->orderBy($column, $direction);
        }
    }
}
