<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class G8StackSettings extends Settings
{
    public string $endpoint;

    public string $api_token;

    public bool $push_enabled;

    public static function group(): string
    {
        return 'g8stack';
    }
}
