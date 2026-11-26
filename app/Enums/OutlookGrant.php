<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum OutlookGrant: string
{
    use HasOptions;

    case RefreshToken = 'refresh_token';
    case ClientCredentials = 'client_credentials';

    public function label(): string
    {
        return match ($this) {
            self::RefreshToken => 'Token de actualización',
            self::ClientCredentials => 'Credencial de aplicación',
        };
    }
}
