<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserActivationNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $role
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
        $roleLabel = $this->role === 'formateur' ? 'formateur' : 'administrateur';

        // Générer un lien signé temporaire (valide 7 jours)
        // Utiliser absolute: true pour générer une URL absolue complète
        $activationUrl = URL::temporarySignedRoute(
            'activation.create-password',
            now()->addDays(7),
            ['user' => $notifiable->id],
            absolute: true
        );

        // URL du logo Simplon (absolue pour les emails)
        // Construire l'URL absolue en utilisant l'URL de base de l'application
        // Si URL::forceRootUrl() a été appelé, URL::to() l'utilisera automatiquement
        $baseUrl = \Illuminate\Support\Facades\URL::to('/');
        // S'assurer que c'est une URL absolue
        if (! str_starts_with($baseUrl, 'http')) {
            $baseUrl = config('app.url');
        }
        $logoUrl = rtrim($baseUrl, '/').'/images/simplon-logo.jpg';

        return (new MailMessage)
            ->subject('Activation de votre compte - Simplon Africa')
            ->view('emails.user-activation', [
                'user' => $notifiable,
                'roleLabel' => $roleLabel,
                'activationUrl' => $activationUrl,
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
