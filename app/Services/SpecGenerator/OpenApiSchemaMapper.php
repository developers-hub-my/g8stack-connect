<?php

declare(strict_types=1);

namespace App\Services\SpecGenerator;

class OpenApiSchemaMapper
{
    public static function mapDbTypeToOpenApi(string $dbType): array
    {
        $dbType = strtolower($dbType);

        return match (true) {
            str_contains($dbType, 'int') => ['type' => 'integer'],
            str_contains($dbType, 'float') || str_contains($dbType, 'double') || str_contains($dbType, 'decimal') || str_contains($dbType, 'numeric') || str_contains($dbType, 'real') => ['type' => 'number'],
            str_contains($dbType, 'bool') => ['type' => 'boolean'],
            str_contains($dbType, 'json') || str_contains($dbType, 'jsonb') => ['type' => 'object'],
            str_contains($dbType, 'date') && ! str_contains($dbType, 'datetime') && ! str_contains($dbType, 'timestamp') => ['type' => 'string', 'format' => 'date'],
            str_contains($dbType, 'datetime') || str_contains($dbType, 'timestamp') => ['type' => 'string', 'format' => 'date-time'],
            str_contains($dbType, 'time') => ['type' => 'string', 'format' => 'time'],
            str_contains($dbType, 'uuid') => ['type' => 'string', 'format' => 'uuid'],
            str_contains($dbType, 'text') || str_contains($dbType, 'varchar') || str_contains($dbType, 'char') || str_contains($dbType, 'string') => ['type' => 'string'],
            str_contains($dbType, 'blob') || str_contains($dbType, 'binary') => ['type' => 'string', 'format' => 'binary'],
            default => ['type' => 'string'],
        };
    }
}
