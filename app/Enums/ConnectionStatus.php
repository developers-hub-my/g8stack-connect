<?php

declare(strict_types=1);

namespace App\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum ConnectionStatus: string implements Contract
{
    use InteractsWithEnum;

    case CONNECTED = 'connected';
    case FAILED = 'failed';
    case INTROSPECTED = 'introspected';
    case DISCONNECTED = 'disconnected';

    public function label(): string
    {
        return match ($this) {
            self::CONNECTED => 'Connected',
            self::FAILED => 'Failed',
            self::INTROSPECTED => 'Introspected',
            self::DISCONNECTED => 'Disconnected',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CONNECTED => 'Successfully connected to the data source.',
            self::FAILED => 'Connection to the data source failed.',
            self::INTROSPECTED => 'Schema has been introspected from the data source.',
            self::DISCONNECTED => 'Disconnected from the data source.',
        };
    }
}
