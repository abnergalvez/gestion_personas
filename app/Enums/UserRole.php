<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum UserRole: string
{
    use HasOptions;

    case SuperAdmin = 'super_admin';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrador',
            self::Admin => 'Administrador',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::SuperAdmin => 'blue',
            self::Admin => 'slate',
        };
    }
}
