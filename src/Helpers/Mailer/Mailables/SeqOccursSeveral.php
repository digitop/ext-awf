<?php

namespace AWF\Extension\Helpers\Mailer\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use  Illuminate\Support\Collection;

class SeqOccursSeveral extends Mailable
{
    use Queueable, SerializesModels;

    protected Collection $sequencesPillar;
    protected Collection $sequencesSide;

    public function __construct(Collection $sequencesPillar, Collection $sequencesSide)
    {
        $this->sequencesPillar = $sequencesPillar;
        $this->sequencesSide = $sequencesSide;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hiányzó oldal adatok!',
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
            view: 'awf-extension::emails.checkers.sequence-occurs-several-time',
            with: [
                'sequencesPillar' => $this->sequencesPillar,
                'sequencesSide' => $this->sequencesSide,
            ],
        );
    }
}
