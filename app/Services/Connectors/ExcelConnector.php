<?php

declare(strict_types=1);

namespace App\Services\Connectors;

use App\DataTransferObjects\SchemaResult;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelConnector extends AbstractFileConnector
{
    protected array $sheets = [];

    protected ?string $activeSheet = null;

    protected function getFileType(): string
    {
        return 'excel';
    }

    protected function parseFile(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);

        // Parse all sheets
        $this->sheets = [];
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $data = $worksheet->toArray(null, true, true, false);

            if (empty($data) || count($data) < 2) {
                continue;
            }

            // First row = headers, rest = data
            $headers = array_map(fn ($h) => (string) ($h ?? ''), array_shift($data));
            $headers = array_filter($headers, fn ($h) => $h !== '');

            $rows = [];
            foreach ($data as $row) {
                $mapped = [];
                foreach ($headers as $i => $header) {
                    $mapped[$header] = $row[$i] ?? null;
                }
                $rows[] = $mapped;
            }

            $this->sheets[$sheetName] = [
                'headers' => array_values($headers),
                'rows' => $rows,
            ];
        }

        // Default to first sheet
        $firstSheet = array_key_first($this->sheets);
        if ($firstSheet !== null) {
            $this->activeSheet = $firstSheet;
            $this->parsedHeaders = $this->sheets[$firstSheet]['headers'];
            $this->parsedRows = $this->sheets[$firstSheet]['rows'];
        }
    }

    public function introspect(): SchemaResult
    {
        try {
            if (! $this->filePath) {
                return new SchemaResult(success: false, message: 'No file loaded.');
            }

            $tableNames = [];
            foreach (array_keys($this->sheets) as $sheetName) {
                $tableNames[] = str($sheetName)->snake()->toString();
            }

            $this->logAudit('introspect', 'success', 'Introspected '.count($tableNames).' sheets.');

            return new SchemaResult(
                success: true,
                tables: $tableNames,
                message: 'Introspected '.count($tableNames).' sheets.',
            );
        } catch (\Throwable $e) {
            $this->logAudit('introspect', 'failed', $e->getMessage());

            return new SchemaResult(
                success: false,
                message: 'Introspection failed: '.$e->getMessage(),
            );
        }
    }

    public function selectSheet(string $sheetName): void
    {
        $snakeName = str($sheetName)->snake()->toString();

        foreach ($this->sheets as $name => $data) {
            if (str($name)->snake()->toString() === $snakeName || $name === $sheetName) {
                $this->activeSheet = $name;
                $this->parsedHeaders = $data['headers'];
                $this->parsedRows = $data['rows'];

                return;
            }
        }
    }

    public function getSheetNames(): array
    {
        return array_keys($this->sheets);
    }

    public function getColumnsForTable(string $table): array
    {
        $this->selectSheet($table);

        return parent::getColumnsForTable($table);
    }

    protected function deriveTableName(): string
    {
        if ($this->activeSheet) {
            return str($this->activeSheet)->snake()->toString();
        }

        return parent::deriveTableName();
    }
}
