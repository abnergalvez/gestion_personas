<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonnelNotice extends Notification
{
    public function __construct(public string $subjectLine, public string $message) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectLine)
            ->view('mail.notice', [
                'subjectLine' => $this->subjectLine,
                'messageText' => $this->message,
            ]);
    }
}
