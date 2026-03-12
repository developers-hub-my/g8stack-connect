<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use Illuminate\Support\Collection;

class ApiResponseTransformer
{
    public function transformCollection(ApiSpec $spec, array $result, ?ApiSpecTable $table = null): array
    {
        $result['data'] = array_map(
            fn ($row) => $this->transformItem($spec, $row, $table),
            $result['data'],
        );

        return $result;
    }

    public function transformItem(ApiSpec $spec, array $row, ?ApiSpecTable $table = null): array
    {
        $fieldMap = $this->getFieldMap($spec, $table);

        if (empty($fieldMap)) {
            return $row;
        }

        $transformed = [];

        foreach ($row as $column => $value) {
            $displayName = $fieldMap[$column] ?? $column;
            $transformed[$displayName] = $value;
        }

        return $transformed;
    }

    public function filterInputFields(ApiSpec $spec, array $input, ?ApiSpecTable $table = null): array
    {
        $exposedColumns = $this->getExposedColumns($spec, $table);

        if (empty($exposedColumns)) {
            return $input;
        }

        $reverseMap = $this->getReverseFieldMap($spec, $table);

        $filtered = [];

        foreach ($input as $key => $value) {
            $columnName = $reverseMap[$key] ?? $key;

            if (in_array($columnName, $exposedColumns)) {
                $filtered[$columnName] = $value;
            }
        }

        return $filtered;
    }

    protected function getFieldMap(ApiSpec $spec, ?ApiSpecTable $table): array
    {
        $fields = $this->resolveFields($spec, $table);

        if ($fields->isEmpty()) {
            return [];
        }

        return $fields
            ->filter(fn ($f) => $f->is_exposed)
            ->mapWithKeys(fn ($f) => [
                $f->column_name => $f->display_name ?? $f->column_name,
            ])
            ->all();
    }

    protected function getReverseFieldMap(ApiSpec $spec, ?ApiSpecTable $table): array
    {
        $fields = $this->resolveFields($spec, $table);

        if ($fields->isEmpty()) {
            return [];
        }

        return $fields
            ->filter(fn ($f) => $f->is_exposed)
            ->mapWithKeys(fn ($f) => [
                ($f->display_name ?? $f->column_name) => $f->column_name,
            ])
            ->all();
    }

    protected function getExposedColumns(ApiSpec $spec, ?ApiSpecTable $table): array
    {
        $fields = $this->resolveFields($spec, $table);

        if ($fields->isEmpty()) {
            return [];
        }

        return $fields
            ->filter(fn ($f) => $f->is_exposed)
            ->pluck('column_name')
            ->values()
            ->all();
    }

    protected function resolveFields(ApiSpec $spec, ?ApiSpecTable $table): Collection
    {
        if ($table && $table->exists) {
            $tableFields = $table->fields;
            if ($tableFields->isNotEmpty()) {
                return $tableFields;
            }
        }

        return $spec->fields;
    }
}
