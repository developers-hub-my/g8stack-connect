<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class PiiScanResult
{
    public function __construct(
        public array $flagged = [],
        public array $safe = [],
        public array $patterns = [],
    ) {}
}
