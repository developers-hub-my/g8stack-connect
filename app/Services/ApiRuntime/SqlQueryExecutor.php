<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use App\Services\SqlValidator;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SqlQueryExecutor
{
    /**
     * Hardcoded query timeout in seconds — NOT configurable.
     */
    private const int QUERY_TIMEOUT_SECONDS = 10;

    /**
     * Hardcoded maximum row cap — NOT configurable.
     */
    private const int MAX_ROW_CAP = 1000;

    public function __construct(
        protected SqlValidator $validator,
    ) {}

    /**
     * Execute an SQL query endpoint and return paginated results.
     */
    public function execute(ApiSpec $spec, ApiSpecTable $table, Request $request): array
    {
        $sql = $table->sql_query;
        $parameters = $table->sql_parameters ?? [];

        // Validate the SQL (should already be validated, but enforce at runtime too)
        $validation = $this->validator->validate($sql);
        if (! $validation->valid) {
            throw new \RuntimeException('Invalid SQL query: '.implode(' ', $validation->errors));
        }

        // Bind parameters from request
        $bindings = $this->resolveBindings($parameters, $request);

        // Connect to the data source
        $connection = $this->connect($spec);

        // Set query timeout at driver level
        $this->setQueryTimeout($connection);

        // Wrap SQL with row cap
        $cappedSql = $this->applyRowCap($sql);

        // Execute
        $rows = $connection->select($cappedSql, $bindings);

        // Convert to arrays
        $data = array_map(fn ($row) => (array) $row, $rows);

        // Apply pagination on results
        return $this->paginate($data, $request, $spec);
    }

    /**
     * Dry-run a query to get result shape (column names and types) without exposing data.
     */
    public function dryRun(ApiSpec $spec, string $sql): array
    {
        $validation = $this->validator->validate($sql);
        if (! $validation->valid) {
            throw new \RuntimeException('Invalid SQL query: '.implode(' ', $validation->errors));
        }

        $connection = $this->connect($spec);
        $this->setQueryTimeout($connection);

        // Execute with LIMIT 1 to get column metadata
        $limitedSql = "SELECT * FROM ({$sql}) AS _dry_run LIMIT 1";

        $rows = $connection->select($limitedSql);

        if (empty($rows)) {
            // No rows returned — try to get columns from empty result
            $limitedSql = "SELECT * FROM ({$sql}) AS _dry_run LIMIT 0";
            $rows = $connection->select($limitedSql);
        }

        // Infer column types from result
        $columns = [];
        if (! empty($rows)) {
            $firstRow = (array) $rows[0];
            foreach ($firstRow as $name => $value) {
                $columns[] = [
                    'name' => $name,
                    'type' => $this->inferTypeFromValue($value),
                ];
            }
        }

        return [
            'columns' => $columns,
            'parameters' => $validation->parameters,
            'tables' => $validation->tables,
        ];
    }

    /**
     * Resolve parameter bindings from the request query string.
     */
    protected function resolveBindings(array $parameters, Request $request): array
    {
        $bindings = [];

        foreach ($parameters as $param) {
            $value = $request->input($param);
            if ($value === null) {
                throw new \InvalidArgumentException("Missing required query parameter: {$param}");
            }
            $bindings[] = $value;
        }

        return $bindings;
    }

    /**
     * Wrap the SQL query with a row cap — hardcoded, non-configurable.
     */
    protected function applyRowCap(string $sql): string
    {
        // Remove trailing semicolon
        $sql = rtrim(trim($sql), ';');

        return "SELECT * FROM ({$sql}) AS _capped LIMIT ".self::MAX_ROW_CAP;
    }

    /**
     * Set query timeout at the database driver level.
     */
    protected function setQueryTimeout(Connection $connection): void
    {
        $driver = $connection->getDriverName();

        match ($driver) {
            'mysql' => $connection->statement('SET SESSION MAX_EXECUTION_TIME = '.(self::QUERY_TIMEOUT_SECONDS * 1000)),
            'pgsql' => $connection->statement('SET statement_timeout = '.(self::QUERY_TIMEOUT_SECONDS * 1000)),
            'sqlsrv' => $connection->statement('SET LOCK_TIMEOUT '.(self::QUERY_TIMEOUT_SECONDS * 1000)),
            default => null, // SQLite doesn't support query timeouts natively
        };
    }

    /**
     * Paginate the result set from memory.
     */
    protected function paginate(array $data, Request $request, ApiSpec $spec): array
    {
        $config = $spec->configuration ?? [];
        $pagination = $config['pagination'] ?? true;

        if (! $pagination) {
            return ['data' => $data];
        }

        $perPage = min(
            (int) $request->input('per_page', $config['per_page'] ?? 15),
            100,
        );
        $page = max((int) $request->input('page', 1), 1);
        $total = count($data);

        $sliced = array_slice($data, ($page - 1) * $perPage, $perPage);

        return [
            'data' => $sliced,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / max($perPage, 1)),
            ],
        ];
    }

    /**
     * Infer OpenAPI-compatible type from a PHP value.
     */
    protected function inferTypeFromValue(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'decimal',
            is_bool($value) => 'boolean',
            is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) => 'date',
            is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $value) => 'datetime',
            default => 'varchar',
        };
    }

    /**
     * Connect to the spec's data source.
     */
    protected function connect(ApiSpec $spec): Connection
    {
        $dataSource = $spec->dataSource;
        $credentials = $dataSource->credentials;
        $connectionName = 'g8connect_sql_'.$spec->id;

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
}
