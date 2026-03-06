<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataTransferObjects\PiiScanResult;

interface PiiScanner
{
    public function scan(array $columns): PiiScanResult;
}
