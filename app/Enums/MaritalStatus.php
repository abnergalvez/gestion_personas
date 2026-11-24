<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MaritalStatus: string
{
    use HasOptions;

    case Single = 'single';
    case Married = 'married';
    case Divorced = 'divorced';
    case Widowed = 'widowed';
    case CivilUnion = 'civil_union';
    case Separated = 'separated';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Soltero/a',
            self::Married => 'Casado/a',
            self::Divorced => 'Divorciado/a',
            self::Widowed => 'Viudo/a',
            self::CivilUnion => 'Acuerdo de unión civil',
            self::Separated => 'Separado/a',
        };
    }
}
