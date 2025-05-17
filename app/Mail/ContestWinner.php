<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContestWinner extends Mailable
{
    use Queueable, SerializesModels;

    private $winner;
    private $contest;
    /**
     * Create a new message instance.
     */
    public function __construct($winner,$contest)
    {
        $this->winner = $winner;
        $this->contest = $contest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config("mail.from.address"),
            subject: 'Contest Winner',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contest-winner',
            with:[
                'user' => $this->winner->firstname." ".$this->winner->lastname,
                'contest' => $this->contest
            ]
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
