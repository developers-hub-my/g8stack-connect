<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;

class ApiResponseTransformer
{
    public function transformCollection(ApiSpec $spec, array $result): array
    {
        $result['data'] = array_map(
            fn ($row) => $this->transformItem($spec, $row),
            $result['data'],
        );

        return $result;
    }

    public function transformItem(ApiSpec $spec, array $row): array
    {
        $fieldMap = $this->getFieldMap($spec);

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

    public function filterInputFields(ApiSpec $spec, array $input): array
    {
        $exposedColumns = $this->getExposedColumns($spec);

        if (empty($exposedColumns)) {
            return $input;
        }

        // Reverse map: display_name → column_name
        $reverseMap = $this->getReverseFieldMap($spec);

        $filtered = [];

        foreach ($input as $key => $value) {
            $columnName = $reverseMap[$key] ?? $key;

            if (in_array($columnName, $exposedColumns)) {
                $filtered[$columnName] = $value;
            }
        }

        return $filtered;
    }

    protected function getFieldMap(ApiSpec $spec): array
    {
        if ($spec->fields->isEmpty()) {
            return [];
        }

        return $spec->fields
            ->filter(fn ($f) => $f->is_exposed)
            ->mapWithKeys(fn ($f) => [
                $f->column_name => $f->display_name ?? $f->column_name,
            ])
            ->all();
    }

    protected function getReverseFieldMap(ApiSpec $spec): array
    {
        if ($spec->fields->isEmpty()) {
            return [];
        }

        return $spec->fields
            ->filter(fn ($f) => $f->is_exposed)
            ->mapWithKeys(fn ($f) => [
                ($f->display_name ?? $f->column_name) => $f->column_name,
            ])
            ->all();
    }

    protected function getExposedColumns(ApiSpec $spec): array
    {
        if ($spec->fields->isEmpty()) {
            return [];
        }

        return $spec->fields
            ->filter(fn ($f) => $f->is_exposed)
            ->pluck('column_name')
            ->values()
            ->all();
    }
}
