<?php

namespace AWF\Extension\Helpers\Mailer\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use  Illuminate\Support\Collection;

class NotAllPillarAvailable extends Mailable
{
    use Queueable, SerializesModels;

    protected Collection $sequences;

    public function __construct(Collection $sequences)
    {
        $this->sequences = $sequences;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hiányzó oszlop adatok!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'awf-extension::emails.checkers.not-all-pillar-available',
            with: [
                'sequences' => $this->sequences,
            ],
        );
    }
}