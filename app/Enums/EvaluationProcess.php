<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EvaluationProcess: string
{
    use HasOptions;

    case Initial = 'initial';
    case InEvaluation = 'in_evaluation';
    case PreQualification = 'pre_qualification';
    case Appeal = 'appeal';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Inicial',
            self::InEvaluation => 'En evaluación',
            self::PreQualification => 'Precalificación',
            self::Appeal => 'Apelación',
            self::Final => 'Final',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Initial => 'slate',
            self::InEvaluation => 'amber',
            self::PreQualification => 'blue',
            self::Appeal => 'red',
            self::Final => 'green',
        };
    }
}
