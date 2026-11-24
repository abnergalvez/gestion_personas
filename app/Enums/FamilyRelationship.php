<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum FamilyRelationship: string
{
    use HasOptions;

    case Spouse = 'spouse';
    case CivilPartner = 'civil_partner';
    case Child = 'child';
    case Parent = 'parent';
    case Sibling = 'sibling';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spouse => 'Cónyuge',
            self::CivilPartner => 'Conviviente civil',
            self::Child => 'Hijo/a',
            self::Parent => 'Padre/Madre',
            self::Sibling => 'Hermano/a',
            self::Other => 'Otro',
        };
    }
}
