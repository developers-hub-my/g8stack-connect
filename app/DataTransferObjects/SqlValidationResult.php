<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class SqlValidationResult
{
    /**
     * @param  array<string>  $errors
     * @param  array<string>  $parameters  Named parameters found in the query
     * @param  array<string>  $tables  Tables referenced in the query
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
        public array $parameters = [],
        public array $tables = [],
    ) {}
}
