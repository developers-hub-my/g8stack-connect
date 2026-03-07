<?php

declare(strict_types=1);

namespace App\Services\SpecGenerator;

use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\ApiSpecTable;
use App\Models\DataSourceSchema;

class SpecRegenerationService
{
    public function __construct(
        protected GuidedSpecGenerator $generator = new GuidedSpecGenerator,
    ) {}

    /**
     * Regenerate the OpenAPI spec for an ApiSpec from its tables and fields.
     *
     * @return array The generated spec, or empty array if no schemas found.
     */
    public function regenerate(ApiSpec $apiSpec, array $overrides = []): array
    {
        $allTables = $apiSpec->tables()->with('fields')->get();

        if ($allTables->isEmpty()) {
            return [];
        }

        $schemas = [];
        $configs = [];

        foreach ($allTables as $table) {
            $schema = DataSourceSchema::where('data_source_id', $apiSpec->data_source_id)
                ->where('table_name', $table->table_name)
                ->first();

            if (! $schema) {
                continue;
            }

            $schemas[] = $schema;
            $configs[] = $this->buildTableConfig($table, $schema, $overrides);
        }

        if (empty($schemas)) {
            return [];
        }

        $spec = $this->generator->generateForTables($schemas, $configs);

        $apiSpec->update(['openapi_spec' => $spec]);

        return $spec;
    }

    /**
     * Build the config array for a single table's spec generation.
     */
    protected function buildTableConfig(ApiSpecTable $table, DataSourceSchema $schema, array $overrides = []): array
    {
        $fields = $this->resolveFields($table, $schema);

        return [
            'resource_name' => $table->resource_name,
            'operations' => $table->operations ?? $table->getDefaultOperations(),
            'fields' => $fields,
            'pagination' => $overrides['pagination'] ?? true,
            'per_page' => $overrides['per_page'] ?? 15,
        ];
    }

    /**
     * Resolve fields from ApiSpecField records, falling back to schema columns.
     */
    protected function resolveFields(ApiSpecTable $table, DataSourceSchema $schema): array
    {
        $existingFields = $table->fields;

        if ($existingFields->isNotEmpty()) {
            return $existingFields->map(fn (ApiSpecField $f) => [
                'column_name' => $f->column_name,
                'display_name' => $f->display_name ?? $f->column_name,
                'data_type' => $f->data_type ?? 'varchar',
                'is_exposed' => $f->is_exposed ?? true,
                'is_required' => $f->is_required ?? false,
                'is_filterable' => $f->is_filterable ?? false,
                'is_sortable' => $f->is_sortable ?? false,
            ])->all();
        }

        // Fallback: derive fields from schema columns
        return collect($schema->columns ?? [])->map(fn ($col) => [
            'column_name' => $col['name'],
            'display_name' => $col['name'],
            'data_type' => $col['type'] ?? $col['type_name'] ?? 'varchar',
            'is_exposed' => true,
            'is_required' => ! ($col['nullable'] ?? true),
            'is_filterable' => false,
            'is_sortable' => false,
        ])->all();
    }
}
