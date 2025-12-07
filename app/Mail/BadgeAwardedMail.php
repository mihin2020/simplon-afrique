<?php

namespace App\Mail;

use App\Models\AttestationSetting;
use App\Models\Badge;
use App\Models\Candidature;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BadgeAwardedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $formateur,
        public Badge $badge,
        public Candidature $candidature,
        public float $score
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Félicitations ! Vous avez obtenu le badge '.$this->badge->label,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $settings = AttestationSetting::getSettings();

        return new Content(
            markdown: 'emails.badge-awarded',
            with: [
                'formateur' => $this->formateur,
                'badge' => $this->badge,
                'candidature' => $this->candidature,
                'score' => $this->score,
                'organizationName' => $settings->organization_name ?? 'Simplon Africa',
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
        $attachments = [];

        // Joindre l'attestation PDF si elle existe
        if ($this->candidature->attestation_path) {
            $fullPath = Storage::disk('public')->path($this->candidature->attestation_path);

            if (file_exists($fullPath)) {
                $attachments[] = Attachment::fromPath($fullPath)
                    ->as('Attestation_'.$this->formateur->name.'.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}

                'score' => $this->score,
                'organizationName' => $settings->organization_name ?? 'Simplon Africa',
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
        $attachments = [];

        // Joindre l'attestation PDF si elle existe
        if ($this->candidature->attestation_path) {
            $fullPath = Storage::disk('public')->path($this->candidature->attestation_path);

            if (file_exists($fullPath)) {
                $attachments[] = Attachment::fromPath($fullPath)
                    ->as('Attestation_'.$this->formateur->name.'.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
