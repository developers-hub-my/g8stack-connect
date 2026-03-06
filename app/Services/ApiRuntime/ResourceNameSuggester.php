<?php

declare(strict_types=1);

namespace App\Services\ApiRuntime;

use Illuminate\Support\Str;

class ResourceNameSuggester
{
    protected array $prefixes = [
        'tbl_', 'tb_', 't_',
        'vw_', 'v_',
        'sys_',
        'app_',
        'ref_',
        'lkp_', 'lookup_',
        'dim_', 'fact_',
    ];

    public function suggest(string $tableName): string
    {
        $name = $this->stripPrefixes($tableName);
        $name = $this->stripVersionSuffix($name);
        $name = $this->cleanName($name);
        $name = Str::plural($name);
        $name = Str::kebab($name);

        return $name;
    }

    public function suggestMany(array $tableNames): array
    {
        $suggestions = [];
        $usedNames = [];

        foreach ($tableNames as $tableName) {
            $suggested = $this->suggest($tableName);

            if (in_array($suggested, $usedNames)) {
                $suggested = $suggested.'-'.Str::kebab($tableName);
            }

            $suggestions[$tableName] = $suggested;
            $usedNames[] = $suggested;
        }

        return $suggestions;
    }

    public function suggestFieldName(string $columnName): string
    {
        $name = $this->stripColumnPrefixes($columnName);

        return Str::snake($name);
    }

    protected function stripPrefixes(string $name): string
    {
        foreach ($this->prefixes as $prefix) {
            if (str_starts_with(strtolower($name), $prefix)) {
                return substr($name, strlen($prefix));
            }
        }

        return $name;
    }

    protected function stripVersionSuffix(string $name): string
    {
        return preg_replace('/_v\d+$/', '', $name);
    }

    protected function stripColumnPrefixes(string $name): string
    {
        $columnPrefixes = [
            'fld_', 'col_', 'f_', 'c_',
        ];

        foreach ($columnPrefixes as $prefix) {
            if (str_starts_with(strtolower($name), $prefix)) {
                return substr($name, strlen($prefix));
            }
        }

        $parts = explode('_', $name);
        if (count($parts) >= 3 && strlen($parts[0]) <= 3) {
            array_shift($parts);

            return implode('_', $parts);
        }

        return $name;
    }

    protected function cleanName(string $name): string
    {
        $name = str_replace(['__', '---'], ['_', '-'], $name);
        $name = trim($name, '_-');

        return Str::snake($name);
    }
}
