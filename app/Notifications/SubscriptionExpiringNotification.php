<?php

namespace App\Notifications;

use App\Models\Abonnement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Abonnement $abonnement, private int $joursRestants)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url('/dashboard');

        return (new MailMessage)
            ->subject('Votre abonnement expire bientôt')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre abonnement {$this->abonnement->type_abonnement} arrive à expiration dans {$this->joursRestants} jour(s).")
            ->line('Date d’expiration : ' . $this->abonnement->date_expiration->format('d/m/Y'))
            ->line('Merci de renouveler votre abonnement pour continuer à utiliser l’application sans interruption.')
            ->action('Accéder à mon tableau de bord', $url)
            ->line('Merci de nous faire confiance.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->abonnement->type_abonnement,
            'date_expiration' => $this->abonnement->date_expiration?->toDateString(),
            'jours_restants' => $this->joursRestants,
        ];
    }
}
