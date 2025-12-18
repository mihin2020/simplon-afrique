<?php

namespace App\Mail;

use App\Models\JobOffer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class JobOfferPublishedMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public JobOffer $jobOffer,
        public string $applyUrl
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle offre d\'emploi : '.$this->jobOffer->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // URL du logo Simplon (absolue pour les emails)
        $baseUrl = config('app.url') ?: \Illuminate\Support\Facades\URL::to('/');
        $logoUrl = rtrim($baseUrl, '/').'/images/simplon-logo.jpg';

        return new Content(
            view: 'emails.job-offer-published',
            with: [
                'logoUrl' => $logoUrl,
            ],
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
