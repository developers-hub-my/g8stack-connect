<?php

declare(strict_types=1);

namespace App\Services\Connectors;

use App\Contracts\DataSourceConnector;
use App\DataTransferObjects\ConnectionResult;
use App\DataTransferObjects\PreviewResult;
use App\DataTransferObjects\SchemaResult;
use App\Models\ConnectionAudit;

abstract class AbstractFileConnector implements DataSourceConnector
{
    protected ?string $filePath = null;

    protected ?string $originalFilename = null;

    protected ?int $userId = null;

    protected ?int $dataSourceId = null;

    protected array $parsedHeaders = [];

    protected array $parsedRows = [];

    abstract protected function getFileType(): string;

    abstract protected function parseFile(string $filePath): void;

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
            $filePath = $credentials['file_path'] ?? null;

            if (! $filePath || ! file_exists($filePath)) {
                $this->logAudit('connect', 'failed', 'File not found.');

                return new ConnectionResult(
                    success: false,
                    message: 'File not found or path is invalid.',
                );
            }

            $this->filePath = $filePath;
            $this->originalFilename = $credentials['original_filename'] ?? null;
            $this->parseFile($filePath);

            $this->logAudit('connect', 'success', 'File loaded successfully.');

            return new ConnectionResult(
                success: true,
                message: 'File loaded successfully.',
                metadata: [
                    'file_type' => $this->getFileType(),
                    'file_size' => filesize($filePath),
                    'row_count' => count($this->parsedRows),
                    'column_count' => count($this->parsedHeaders),
                ],
            );
        } catch (\Throwable $e) {
            $this->logAudit('connect', 'failed', $e->getMessage());

            return new ConnectionResult(
                success: false,
                message: 'Failed to load file: '.$e->getMessage(),
            );
        }
    }

    public function introspect(): SchemaResult
    {
        try {
            if (! $this->filePath) {
                return new SchemaResult(success: false, message: 'No file loaded.');
            }

            $tableName = $this->deriveTableName();

            $this->logAudit('introspect', 'success', "Introspected file as table: {$tableName}.");

            return new SchemaResult(
                success: true,
                tables: [$tableName],
                message: "File introspected as 1 table ({$tableName}).",
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
            if (! $this->filePath) {
                return new PreviewResult(success: false, message: 'No file loaded.');
            }

            $maxRows = config('datasource.max_preview_rows', 5);
            $limit = min($limit, $maxRows);

            $rows = array_slice($this->parsedRows, 0, $limit);

            $this->logAudit('preview', 'success', "Previewed {$limit} rows.");

            return new PreviewResult(
                success: true,
                rows: $rows,
                columns: $this->parsedHeaders,
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
        return true;
    }

    public function disconnect(): void
    {
        $this->filePath = null;
        $this->parsedHeaders = [];
        $this->parsedRows = [];
        $this->logAudit('disconnect', 'success', 'File source disconnected.');
    }

    public function getHeaders(): array
    {
        return $this->parsedHeaders;
    }

    public function getRows(): array
    {
        return $this->parsedRows;
    }

    public function getColumnsForTable(string $table): array
    {
        return collect($this->parsedHeaders)->map(fn (string $header) => [
            'name' => $header,
            'type' => $this->inferColumnType($header),
            'nullable' => true,
        ])->all();
    }

    protected function inferColumnType(string $column): string
    {
        $sampleValues = collect($this->parsedRows)
            ->take(100)
            ->pluck($column)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->values();

        if ($sampleValues->isEmpty()) {
            return 'varchar';
        }

        // Check if all values are integers
        if ($sampleValues->every(fn ($v) => is_int($v) || (is_string($v) && preg_match('/^-?\d+$/', $v)))) {
            return 'integer';
        }

        // Check if all values are numeric (float/decimal)
        if ($sampleValues->every(fn ($v) => is_numeric($v))) {
            return 'decimal';
        }

        // Check if all values are boolean-like
        if ($sampleValues->every(fn ($v) => in_array(strtolower((string) $v), ['true', 'false', '0', '1', 'yes', 'no']))) {
            return 'boolean';
        }

        // Check if all values look like dates
        if ($sampleValues->every(fn ($v) => strtotime((string) $v) !== false && preg_match('/\d{4}[-\/]\d{1,2}[-\/]\d{1,2}/', (string) $v))) {
            return 'date';
        }

        // Check if all values look like datetimes
        if ($sampleValues->every(fn ($v) => strtotime((string) $v) !== false && preg_match('/\d{4}[-\/]\d{1,2}[-\/]\d{1,2}[T ]\d{1,2}:\d{2}/', (string) $v))) {
            return 'datetime';
        }

        return 'varchar';
    }

    protected function deriveTableName(): string
    {
        if ($this->originalFilename) {
            $filename = pathinfo($this->originalFilename, PATHINFO_FILENAME);

            return str($filename)->snake()->toString();
        }

        if (! $this->filePath) {
            return 'data';
        }

        $filename = pathinfo($this->filePath, PATHINFO_FILENAME);

        return str($filename)->snake()->toString();
    }

    protected function logAudit(string $action, string $status, string $message): void
    {
        if ($this->userId) {
            ConnectionAudit::create([
                'user_id' => $this->userId,
                'data_source_id' => $this->dataSourceId,
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'metadata' => ['file_type' => $this->getFileType()],
                'ip_address' => request()?->ip(),
            ]);
        }
    }
}
