<?php

declare(strict_types=1);

namespace App\Services\SpecGenerator;

use App\Contracts\SpecGenerator;
use App\Models\DataSourceSchema;

class GuidedSpecGenerator implements SpecGenerator
{
    public function generate(DataSourceSchema $schema, array $config = []): array
    {
        $tableName = $schema->table_name;
        $resourceName = str($tableName)->singular()->kebab()->toString();
        $modelName = str($tableName)->singular()->studly()->toString();

        $fields = $config['fields'] ?? [];
        $methods = $config['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE'];
        $pagination = $config['pagination'] ?? true;
        $perPage = $config['per_page'] ?? 15;

        $exposedFields = collect($fields)->filter(fn ($f) => $f['is_exposed'] ?? true)->values()->all();

        $properties = [];
        foreach ($exposedFields as $field) {
            $name = $field['display_name'] ?? $field['column_name'];
            $properties[$name] = OpenApiSchemaMapper::mapDbTypeToOpenApi($field['data_type'] ?? 'varchar');
        }

        $requiredFields = collect($exposedFields)
            ->filter(fn ($f) => $f['is_required'] ?? false)
            ->map(fn ($f) => $f['display_name'] ?? $f['column_name'])
            ->values()
            ->all();

        $basePath = "/api/{$resourceName}s";
        $paths = [];

        if (in_array('GET', $methods) || in_array('POST', $methods)) {
            $paths[$basePath] = [];

            if (in_array('GET', $methods)) {
                $parameters = [];
                if ($pagination) {
                    $parameters[] = ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]];
                    $parameters[] = ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => $perPage]];
                }

                $filterableFields = collect($exposedFields)->filter(fn ($f) => $f['is_filterable'] ?? false);
                foreach ($filterableFields as $f) {
                    $name = $f['display_name'] ?? $f['column_name'];
                    $parameters[] = ['name' => "filter[{$name}]", 'in' => 'query', 'schema' => ['type' => 'string']];
                }

                $sortableFields = collect($exposedFields)->filter(fn ($f) => $f['is_sortable'] ?? false)->pluck('display_name')->implode(', ');

                $paths[$basePath]['get'] = [
                    'summary' => "List all {$modelName} records",
                    'operationId' => "list{$modelName}s",
                    'tags' => [$modelName],
                    'parameters' => $parameters,
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

                if ($sortableFields) {
                    $paths[$basePath]['get']['parameters'][] = [
                        'name' => 'sort',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => "Sortable fields: {$sortableFields}",
                    ];
                }
            }

            if (in_array('POST', $methods)) {
                $paths[$basePath]['post'] = [
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
                                    'required' => $requiredFields,
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
        }

        $itemMethods = array_intersect($methods, ['GET', 'PUT', 'DELETE']);
        if (! empty($itemMethods)) {
            $itemPath = "{$basePath}/{id}";
            $paths[$itemPath] = [];

            if (in_array('GET', $methods)) {
                $paths[$itemPath]['get'] = [
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

            if (in_array('PUT', $methods)) {
                $paths[$itemPath]['put'] = [
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

            if (in_array('DELETE', $methods)) {
                $paths[$itemPath]['delete'] = [
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

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => "{$modelName} API",
                'version' => '1.0.0',
                'description' => "Guided-mode API for {$tableName}.",
                'x-generator' => 'G8Connect',
                'x-mode' => 'guided',
            ],
            'paths' => $paths,
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
}
