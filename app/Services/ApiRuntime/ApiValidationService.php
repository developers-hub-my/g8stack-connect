<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApiValidationService
{
    public function validate(ApiSpec $spec, array $data, ?ApiSpecTable $table = null, bool $isUpdate = false): array
    {
        $rules = $this->buildRules($spec, $table, $isUpdate);

        if (empty($rules)) {
            return $data;
        }

        $validator = Validator::make($data, $rules, [], $this->buildFieldNames($spec, $table));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function buildRules(ApiSpec $spec, ?ApiSpecTable $table, bool $isUpdate): array
    {
        $fields = $table
            ? $table->fields()->where('is_exposed', true)->get()
            : $spec->fields()->where('is_exposed', true)->get();

        if ($fields->isEmpty()) {
            return [];
        }

        $rules = [];

        foreach ($fields as $field) {
            $displayName = $field->display_name ?? $field->column_name;
            $fieldRules = [];

            if ($field->is_required && ! $isUpdate) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = $isUpdate ? 'sometimes' : 'nullable';
            }

            $typeRule = $this->mapDataTypeToRule($field->data_type);
            if ($typeRule) {
                $fieldRules[] = $typeRule;
            }

            $customRules = $field->validation_rules ?? [];
            if (! empty($customRules)) {
                $fieldRules = array_merge($fieldRules, $customRules);
            }

            $rules[$displayName] = $fieldRules;
        }

        return $rules;
    }

    protected function buildFieldNames(ApiSpec $spec, ?ApiSpecTable $table): array
    {
        $fields = $table
            ? $table->fields()->where('is_exposed', true)->get()
            : $spec->fields()->where('is_exposed', true)->get();

        $names = [];

        foreach ($fields as $field) {
            $displayName = $field->display_name ?? $field->column_name;
            $names[$displayName] = $displayName;
        }

        return $names;
    }

    protected function mapDataTypeToRule(?string $dataType): ?string
    {
        if (! $dataType) {
            return null;
        }

        $dataType = strtolower($dataType);

        return match (true) {
            str_contains($dataType, 'int'), str_contains($dataType, 'serial') => 'integer',
            str_contains($dataType, 'float'), str_contains($dataType, 'double'),
            str_contains($dataType, 'decimal'), str_contains($dataType, 'numeric') => 'numeric',
            str_contains($dataType, 'bool') => 'boolean',
            str_contains($dataType, 'date') && ! str_contains($dataType, 'datetime') => 'date',
            str_contains($dataType, 'datetime'), str_contains($dataType, 'timestamp') => 'date',
            str_contains($dataType, 'json') => 'json',
            str_contains($dataType, 'uuid') => 'uuid',
            default => 'string',
        };
    }
}
