<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ConnectionSettings extends Settings
{
    public int $max_preview_rows;

    public int $connection_timeout;

    public bool $enforce_readonly;

    public static function group(): string
    {
        return 'connection';
    }
}
