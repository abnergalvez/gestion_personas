<?php

namespace App\Support;

use App\Notifications\PersonnelNotice;
use Illuminate\Support\Facades\Notification;

final class MailNotice
{
    /**
     * @param  string|list<string>  $recipients
     */
    public static function send(string|array $recipients, string $subject, string $message): void
    {
        $recipients = array_values(array_filter(
            array_map(fn (string $email): string => trim($email), (array) $recipients),
            fn (string $email): bool => $email !== '',
        ));

        if ($recipients === []) {
            return;
        }

        Notification::route('mail', $recipients)->notify(new PersonnelNotice($subject, $message));
    }
}
