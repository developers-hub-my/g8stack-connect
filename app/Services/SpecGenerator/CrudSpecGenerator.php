<?php

declare(strict_types=1);

namespace App\Services\SpecGenerator;

use App\Contracts\PiiScanner;
use App\Contracts\SpecGenerator;
use App\Models\DataSourceSchema;

class CrudSpecGenerator implements SpecGenerator
{
    public function __construct(
        protected PiiScanner $piiScanner,
    ) {}

    public function generate(DataSourceSchema $schema, array $config = []): array
    {
        $tableName = $schema->table_name;
        $columns = $schema->columns ?? [];
        $resourceName = str($tableName)->singular()->kebab()->toString();
        $modelName = str($tableName)->singular()->studly()->toString();

        $columnNames = collect($columns)->pluck('name')->all();
        $piiResult = $this->piiScanner->scan($columnNames);

        $safeColumns = collect($columns)->filter(
            fn ($col) => ! in_array($col['name'], $piiResult->flagged)
        )->values()->all();

        $properties = $this->buildSchemaProperties($safeColumns);
        $requiredFields = collect($safeColumns)
            ->filter(fn ($col) => ! ($col['nullable'] ?? true) && $col['name'] !== 'id')
            ->pluck('name')
            ->values()
            ->all();

        $basePath = "/api/{$resourceName}s";

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => "{$modelName} API",
                'version' => '1.0.0',
                'description' => "Auto-generated CRUD API for {$tableName}.",
                'x-generator' => 'G8Connect',
            ],
            'paths' => [
                $basePath => [
                    'get' => $this->buildListEndpoint($modelName, $properties),
                    'post' => $this->buildCreateEndpoint($modelName, $properties, $requiredFields),
                ],
                "{$basePath}/{id}" => [
                    'get' => $this->buildShowEndpoint($modelName, $properties),
                    'put' => $this->buildUpdateEndpoint($modelName, $properties),
                    'delete' => $this->buildDeleteEndpoint($modelName),
                ],
            ],
            'components' => [
                'schemas' => [
                    $modelName => [
                        'type' => 'object',
                        'properties' => $properties,
                    ],
                ],
            ],
        ];
    }

    protected function buildSchemaProperties(array $columns): array
    {
        $properties = [];

        foreach ($columns as $col) {
            $properties[$col['name']] = OpenApiSchemaMapper::mapDbTypeToOpenApi($col['type'] ?? 'varchar');
        }

        return $properties;
    }

    protected function buildListEndpoint(string $modelName, array $properties): array
    {
        return [
            'summary' => "List all {$modelName} records",
            'operationId' => "list{$modelName}s",
            'tags' => [$modelName],
            'parameters' => [
                ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'data' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => "#/components/schemas/{$modelName}"],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function buildCreateEndpoint(string $modelName, array $properties, array $required): array
    {
        return [
            'summary' => "Create a new {$modelName}",
            'operationId' => "create{$modelName}",
            'tags' => [$modelName],
            'requestBody' => [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => collect($properties)->except(['id', 'created_at', 'updated_at'])->all(),
                            'required' => array_values(array_diff($required, ['id', 'created_at', 'updated_at'])),
                        ],
                    ],
                ],
            ],
            'responses' => [
                '201' => [
                    'description' => 'Created successfully',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => "#/components/schemas/{$modelName}"],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function buildShowEndpoint(string $modelName, array $properties): array
    {
        return [
            'summary' => "Get a {$modelName} by ID",
            'operationId' => "get{$modelName}",
            'tags' => [$modelName],
            'parameters' => [
                ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => "#/components/schemas/{$modelName}"],
                        ],
                    ],
                ],
                '404' => ['description' => 'Not found'],
            ],
        ];
    }

    protected function buildUpdateEndpoint(string $modelName, array $properties): array
    {
        return [
            'summary' => "Update a {$modelName}",
            'operationId' => "update{$modelName}",
            'tags' => [$modelName],
            'parameters' => [
                ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
            ],
            'requestBody' => [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => collect($properties)->except(['id', 'created_at', 'updated_at'])->all(),
                        ],
                    ],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Updated successfully',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => "#/components/schemas/{$modelName}"],
                        ],
                    ],
                ],
                '404' => ['description' => 'Not found'],
            ],
        ];
    }

    protected function buildDeleteEndpoint(string $modelName): array
    {
        return [
            'summary' => "Delete a {$modelName}",
            'operationId' => "delete{$modelName}",
            'tags' => [$modelName],
            'parameters' => [
                ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
            ],
            'responses' => [
                '204' => ['description' => 'Deleted successfully'],
                '404' => ['description' => 'Not found'],
            ],
        ];
    }
}
