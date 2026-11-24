<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EmploymentQuality: string
{
    use HasOptions;

    case Contract = 'contract';
    case Fee = 'fee';
    case Permanent = 'permanent';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Contrata',
            self::Fee => 'Honorario',
            self::Permanent => 'Planta',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Contract => 'blue',
            self::Fee => 'amber',
            self::Permanent => 'green',
        };
    }
}
