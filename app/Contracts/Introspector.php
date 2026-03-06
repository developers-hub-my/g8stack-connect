<?php

declare(strict_types=1);

namespace App\Contracts;

interface Introspector
{
    public function getTables(): array;

    public function getColumns(string $table): array;

    public function getColumnTypes(string $table): array;
}
