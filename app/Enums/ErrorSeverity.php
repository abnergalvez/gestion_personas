<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ErrorSeverity: string
{
    use HasOptions;

    case Grave = 'grave';
    case Notice = 'notice';

    public function label(): string
    {
        return match ($this) {
            self::Grave => 'Grave',
            self::Notice => 'Aviso',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Grave => 'red',
            self::Notice => 'amber',
        };
    }
}
