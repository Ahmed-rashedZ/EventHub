<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssistantInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public User $assistant;
    public Event $event;
    public User $manager;
    public ?string $invitationMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(User $assistant, Event $event, User $manager, ?string $invitationMessage = null)
    {
        $this->assistant = $assistant;
        $this->event = $event;
        $this->manager = $manager;
        $this->invitationMessage = $invitationMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'دعوة للانضمام إلى فريق تنظيم حدث: ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.assistant-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
