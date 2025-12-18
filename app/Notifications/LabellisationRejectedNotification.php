<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class LabellisationRejectedNotification extends Notification
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
        $formateurName = trim(($formateur->first_name ?? '').' '.($formateur->name ?? '')) ?: $formateur->name;

        // URL de connexion pour resoumettre
        $loginUrl = URL::route('login', [], absolute: true);

        // URL du logo Simplon (absolue pour les emails)
        $baseUrl = config('app.url') ?: URL::to('/');
        $logoUrl = rtrim($baseUrl, '/').'/images/simplon-logo.jpg';

        return (new MailMessage)
            ->subject('Résultat de votre candidature de labellisation - Simplon Africa')
            ->view('emails.labellisation-rejected', [
                'formateur' => $formateur,
                'formateurName' => $formateurName,
                'candidature' => $this->candidature,
                'loginUrl' => $loginUrl,
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


