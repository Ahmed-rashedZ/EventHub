<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssistantAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $password;
    public string $eventName;

    public function __construct(string $name, string $email, string $password, string $eventName)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->eventName = $eventName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في فريق عمل EventHub',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.assistant-account',
        );
    }
}
