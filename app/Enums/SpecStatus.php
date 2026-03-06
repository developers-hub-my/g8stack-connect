<?php

declare(strict_types=1);

namespace App\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum SpecStatus: string implements Contract
{
    use InteractsWithEnum;

    case PENDING = 'pending';
    case PUSHED = 'pushed';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DEPLOYED = 'deployed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PUSHED => 'Pushed',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::DEPLOYED => 'Deployed',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Spec generated, not yet pushed to G8Stack.',
            self::PUSHED => 'Spec sent to G8Stack for governance review.',
            self::APPROVED => 'Spec approved in G8Stack governance workflow.',
            self::REJECTED => 'Spec rejected in G8Stack governance workflow.',
            self::DEPLOYED => 'Spec deployed live on Kong API Gateway.',
        };
    }
}
