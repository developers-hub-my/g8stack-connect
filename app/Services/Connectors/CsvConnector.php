<?php

declare(strict_types=1);

namespace App\Services\Connectors;

use League\Csv\Reader;

class CsvConnector extends AbstractFileConnector
{
    protected function getFileType(): string
    {
        return 'csv';
    }

    protected function parseFile(string $filePath): void
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $this->parsedHeaders = $csv->getHeader();

        $this->parsedRows = [];
        foreach ($csv->getRecords() as $record) {
            $this->parsedRows[] = $record;
        }
    }
}
