<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ContractModality: string
{
    use HasOptions;

    case Direct = 'direct';
    case Contest = 'contest';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Contrato directo',
            self::Contest => 'Concurso',
        };
    }
}
