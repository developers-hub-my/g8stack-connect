<?php

declare(strict_types=1);

namespace App\Services\PiiDetection;

use App\Contracts\PiiScanner;
use App\DataTransferObjects\PiiScanResult;

class PiiDetectionService implements PiiScanner
{
    public function scan(array $columns): PiiScanResult
    {
        $patterns = config('pii.patterns', []);
        $flagged = [];
        $safe = [];
        $matchedPatterns = [];

        foreach ($columns as $column) {
            $columnName = is_array($column) ? ($column['name'] ?? '') : $column;
            $columnLower = strtolower((string) $columnName);

            $isFlagged = false;

            foreach ($patterns as $pattern) {
                if (str_contains($columnLower, strtolower($pattern))) {
                    $flagged[] = $columnName;
                    $matchedPatterns[$columnName] = $pattern;
                    $isFlagged = true;

                    break;
                }
            }

            if (! $isFlagged) {
                $safe[] = $columnName;
            }
        }

        return new PiiScanResult(
            flagged: $flagged,
            safe: $safe,
            patterns: $matchedPatterns,
        );
    }
}
