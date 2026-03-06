<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\DataSourceSchema;

interface SpecGenerator
{
    public function generate(DataSourceSchema $schema, array $config = []): array;
}
