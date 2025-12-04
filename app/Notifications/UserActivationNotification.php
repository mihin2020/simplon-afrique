<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserActivationNotification extends Notification
{
    use Queueable;

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

        return (new MailMessage)
            ->subject('Activation de votre compte - Simplon Africa')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre compte '.$roleLabel.' a été créé sur la plateforme Simplon Africa.')
            ->line('Pour activer votre compte et créer votre mot de passe, cliquez sur le bouton ci-dessous :')
            ->action('Créer mon mot de passe', $activationUrl)
            ->line('Ce lien est valide pendant 7 jours.')
            ->line('Si vous n\'avez pas demandé ce compte, vous pouvez ignorer cet email.');
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
