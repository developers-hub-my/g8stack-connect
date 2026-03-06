<?php

declare(strict_types=1);

namespace App\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum WizardMode: string implements Contract
{
    use InteractsWithEnum;

    case SIMPLE = 'simple';
    case GUIDED = 'guided';
    case ADVANCED = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::GUIDED => 'Guided',
            self::ADVANCED => 'Advanced',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SIMPLE => 'Pick a table and auto-generate CRUD endpoints with zero configuration.',
            self::GUIDED => 'Choose fields, HTTP methods, filters, and pagination options.',
            self::ADVANCED => 'Write a SELECT SQL query to create a named GET endpoint.',
        };
    }
}
