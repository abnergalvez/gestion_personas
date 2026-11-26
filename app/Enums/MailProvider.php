<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MailProvider: string
{
    use HasOptions;

    case Log = 'log';
    case Brevo = 'brevo';
    case Gmail = 'gmail';
    case Outlook = 'outlook';
    case Smtp = 'smtp';

    public function label(): string
    {
        return match ($this) {
            self::Log => 'Registro, sin envío',
            self::Brevo => 'Brevo',
            self::Gmail => 'Gmail',
            self::Outlook => 'Outlook',
            self::Smtp => 'Otro SMTP',
        };
    }
}
