<?php

declare(strict_types=1);

namespace App\Services\SpecGenerator;

use App\Contracts\PiiScanner;
use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use Illuminate\Support\Collection;

class SqlSpecGenerator
{
    public function __construct(
        protected PiiScanner $piiScanner,
    ) {}

    /**
     * Generate an OpenAPI spec from a single SQL query's result columns.
     *
     * @param  array{endpoint_name: string, result_columns: array, parameters: array, description?: string}  $config
     */
    public function generate(ApiSpec $spec, array $config = []): array
    {
        $endpointName = $config['endpoint_name'];
        $resultColumns = $config['result_columns'] ?? [];
        $parameters = $config['parameters'] ?? [];
        $description = $config['description'] ?? "Custom SQL query endpoint: {$endpointName}";

        $modelName = str($endpointName)->studly()->toString();

        // PII scan on result column names
        $columnNames = collect($resultColumns)->pluck('name')->all();
        $piiResult = $this->piiScanner->scan($columnNames);

        $safeColumns = collect($resultColumns)->filter(
            fn ($col) => ! in_array($col['name'], $piiResult->flagged)
        )->values()->all();

        $properties = $this->buildSchemaProperties($safeColumns);
        $basePath = "/api/connect/{$spec->slug}/{$endpointName}";

        $pathItem = [
            'get' => $this->buildGetEndpoint($modelName, $endpointName, $properties, $parameters, $description),
        ];

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => "{$spec->name} — {$endpointName}",
                'version' => '1.0.0',
                'description' => $description,
                'x-generator' => 'G8Connect',
                'x-query-mode' => 'advanced',
            ],
            'paths' => [
                $basePath => $pathItem,
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

    /**
     * Generate a combined OpenAPI spec from multiple SQL query tables.
     *
     * @param  Collection<int, ApiSpecTable>  $tables
     */
    public function generateForTables(ApiSpec $spec, Collection $tables): array
    {
        $paths = [];
        $schemas = [];

        foreach ($tables as $table) {
            if (! $table->isSqlQuery()) {
                continue;
            }

            $endpointName = $table->resource_name;
            $resultColumns = $table->result_columns ?? [];
            $parameters = $table->sql_parameters ?? [];

            $modelName = str($endpointName)->studly()->toString();

            // PII scan on result column names
            $columnNames = collect($resultColumns)->pluck('name')->all();
            $piiResult = $this->piiScanner->scan($columnNames);

            $safeColumns = collect($resultColumns)->filter(
                fn ($col) => ! in_array($col['name'], $piiResult->flagged)
            )->values()->all();

            $properties = $this->buildSchemaProperties($safeColumns);
            $basePath = "/api/connect/{$spec->slug}/{$endpointName}";

            $paths[$basePath] = [
                'get' => $this->buildGetEndpoint(
                    $modelName,
                    $endpointName,
                    $properties,
                    $parameters,
                    "Custom SQL query endpoint: {$endpointName}",
                ),
            ];

            $schemas[$modelName] = [
                'type' => 'object',
                'properties' => $properties,
            ];
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $spec->name,
                'version' => '1.0.0',
                'description' => "Advanced SQL query endpoints for {$spec->name}",
                'x-generator' => 'G8Connect',
                'x-query-mode' => 'advanced',
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => $schemas,
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

    protected function buildGetEndpoint(
        string $modelName,
        string $endpointName,
        array $properties,
        array $parameters,
        string $description,
    ): array {
        $openApiParams = [];

        foreach ($parameters as $param) {
            $openApiParams[] = [
                'name' => $param,
                'in' => 'query',
                'required' => true,
                'schema' => ['type' => 'string'],
                'description' => "Query parameter: {$param}",
            ];
        }

        // Always include pagination params
        $openApiParams[] = [
            'name' => 'page',
            'in' => 'query',
            'required' => false,
            'schema' => ['type' => 'integer', 'default' => 1],
        ];
        $openApiParams[] = [
            'name' => 'per_page',
            'in' => 'query',
            'required' => false,
            'schema' => ['type' => 'integer', 'default' => 15],
        ];

        return [
            'summary' => "Query: {$endpointName}",
            'description' => $description,
            'operationId' => "query{$modelName}",
            'tags' => [$endpointName],
            'parameters' => $openApiParams,
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
                                    'meta' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'total' => ['type' => 'integer'],
                                            'page' => ['type' => 'integer'],
                                            'per_page' => ['type' => 'integer'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '400' => ['description' => 'Missing or invalid query parameters'],
                '408' => ['description' => 'Query timeout (10 second limit)'],
                '422' => ['description' => 'Query execution error'],
            ],
        ];
    }
}
