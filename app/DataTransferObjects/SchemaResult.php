<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class SchemaResult
{
    public function __construct(
        public bool $success,
        public array $tables = [],
        public string $message = '',
    ) {}
}
