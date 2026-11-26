<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ErrorReview: string
{
    use HasOptions;

    case Seen = 'seen';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Seen => 'Visto',
            self::Resolved => 'Solucionado',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Seen => 'slate',
            self::Resolved => 'green',
        };
    }
}
