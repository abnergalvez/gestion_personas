<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EducationLevel: string
{
    use HasOptions;

    case None = 'none';
    case Preschool = 'preschool';
    case Basic = 'basic';
    case Secondary = 'secondary';
    case Technical = 'technical';
    case Higher = 'higher';
    case Postgraduate = 'postgraduate';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sin escolaridad',
            self::Preschool => 'Preescolar',
            self::Basic => 'Básica',
            self::Secondary => 'Media',
            self::Technical => 'Técnica',
            self::Higher => 'Superior',
            self::Postgraduate => 'Postgrado',
        };
    }
}
