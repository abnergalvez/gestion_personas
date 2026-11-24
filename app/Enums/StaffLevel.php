<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum StaffLevel: string
{
    use HasOptions;

    case Professional = 'professional';
    case Technical = 'technical';
    case Directive = 'directive';
    case Administrative = 'administrative';
    case Auxiliary = 'auxiliary';

    public function label(): string
    {
        return match ($this) {
            self::Professional => 'Profesional',
            self::Technical => 'Técnico',
            self::Directive => 'Directivo',
            self::Administrative => 'Administrativo',
            self::Auxiliary => 'Auxiliar',
        };
    }
}
