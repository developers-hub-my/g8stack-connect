<?php

declare(strict_types=1);

namespace App\Services\Connectors;

class JsonConnector extends AbstractFileConnector
{
    protected function getFileType(): string
    {
        return 'json';
    }

    protected function parseFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: '.json_last_error_msg());
        }

        // Support both top-level array and { "data": [...] } wrapper
        $rows = $this->extractRows($data);

        if (empty($rows)) {
            throw new \RuntimeException('No tabular data found in JSON file.');
        }

        // Derive headers from keys of the first row
        $this->parsedHeaders = array_keys($rows[0]);

        // Flatten nested values to strings for tabular display
        $this->parsedRows = collect($rows)->map(function (array $row) {
            return collect($row)->map(function ($value) {
                if (is_array($value)) {
                    return json_encode($value);
                }

                return $value;
            })->all();
        })->all();
    }

    protected function extractRows(mixed $data): array
    {
        // Top-level array of objects
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        // Wrapper object with a "data" key (or first array-valued key)
        if (is_array($data) && ! isset($data[0])) {
            foreach ($data as $value) {
                if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                    return $value;
                }
            }
        }

        return [];
    }
}
