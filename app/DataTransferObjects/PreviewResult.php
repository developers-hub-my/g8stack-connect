<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class PreviewResult
{
    public function __construct(
        public bool $success,
        public array $rows = [],
        public array $columns = [],
        public int $count = 0,
        public string $message = '',
    ) {}
}
