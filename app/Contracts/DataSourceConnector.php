<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataTransferObjects\ConnectionResult;
use App\DataTransferObjects\PreviewResult;
use App\DataTransferObjects\SchemaResult;

interface DataSourceConnector
{
    public function connect(array $credentials): ConnectionResult;

    public function introspect(): SchemaResult;

    public function preview(string $table, int $limit = 5): PreviewResult;

    public function isReadOnly(): bool;

    public function disconnect(): void;
}
