<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseNotification
{
    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    protected function verificationUrl($notifiable): string
    {
        return $this->url;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address - Jakarta Dental Exhibition 2026')
            ->view('emails.verify-email', [
                'user' => $notifiable,
                'url' => $this->url,
            ]);
    }
}
