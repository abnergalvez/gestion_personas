<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ActivityAction: string
{
    use HasOptions;

    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case Viewed = 'viewed';
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Downloaded = 'downloaded';
    case Denied = 'denied';
    case AccessDenied = 'access_denied';
    case Failed = 'failed';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Login => 'Ingreso',
            self::LoginFailed => 'Ingreso rechazado',
            self::Logout => 'Cierre de sesión',
            self::Viewed => 'Consulta',
            self::Created => 'Creación',
            self::Updated => 'Actualización',
            self::Deleted => 'Eliminación',
            self::Downloaded => 'Descarga',
            self::Denied => 'Acceso denegado',
            self::AccessDenied => 'Intento sin sesión',
            self::Failed => 'Operación rechazada',
            self::Error => 'Error',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Login, self::Created => 'green',
            self::Updated, self::AccessDenied, self::Failed => 'amber',
            self::Viewed, self::Downloaded => 'blue',
            self::LoginFailed, self::Deleted, self::Denied, self::Error => 'red',
            self::Logout => 'slate',
        };
    }
}
