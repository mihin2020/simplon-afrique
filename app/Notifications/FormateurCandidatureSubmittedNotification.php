<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class FormateurCandidatureSubmittedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Candidature $candidature
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $formateur = $this->candidature->user;
        $formateurName = trim(($formateur->name ?? '').' '.($formateur->first_name ?? ''));

        // URL pour consulter la candidature
        $candidatureUrl = URL::route('admin.candidature.show', $this->candidature->id, absolute: true);

        // URL du logo Simplon (absolue pour les emails)
        $baseUrl = config('app.url') ?: URL::to('/');
        $logoUrl = rtrim($baseUrl, '/').'/images/simplon-logo.jpg';

        return (new MailMessage)
            ->subject('Nouvelle candidature de formateur - Simplon Africa')
            ->view('emails.formateur-candidature-submitted', [
                'admin' => $notifiable,
                'formateur' => $formateur,
                'formateurName' => $formateurName,
                'candidature' => $this->candidature,
                'candidatureUrl' => $candidatureUrl,
                'logoUrl' => $logoUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}


