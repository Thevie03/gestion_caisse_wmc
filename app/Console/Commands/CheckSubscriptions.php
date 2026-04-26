<?php

namespace App\Console\Commands;

use App\Models\Abonnement;
use App\Notifications\SubscriptionExpiringNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check {--notify : Envoyer une notification aux comptes concernés}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour les statuts des abonnements (expiration, suspension) et notifie les utilisateurs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Vérification des abonnements en cours...');

        $expiredCount = $this->expireOverdueSubscriptions();
        $this->info("{$expiredCount} abonnement(s) expiré(s) mis à jour.");

        $soonCount = $this->flagUpcomingExpirations();
        $this->info("{$soonCount} abonnement(s) proches de l'expiration détecté(s).");

        return Command::SUCCESS;
    }

    protected function expireOverdueSubscriptions(): int
    {
        $count = 0;

        Abonnement::where('statut', Abonnement::STATUT_ACTIF)
            ->whereDate('date_expiration', '<', now()->startOfDay())
            ->chunkById(200, function ($abonnements) use (&$count) {
                foreach ($abonnements as $abonnement) {
                    $abonnement->update(['statut' => Abonnement::STATUT_EXPIRE]);

                    if ($abonnement->user) {
                        $abonnement->user->forceFill([
                            'subscription_status' => Abonnement::STATUT_EXPIRE,
                            'subscription_expires_at' => $abonnement->date_expiration,
                        ])->save();

                        // Notifier le Super Admin
                        NotificationService::notifierAbonnementExpire($abonnement, null);
                    }

                    $count++;
                }
            });

        return $count;
    }

    protected function flagUpcomingExpirations(): int
    {
        $notifyThreshold = now()->addDays(3)->endOfDay();
        $count = 0;

        Abonnement::where('statut', Abonnement::STATUT_ACTIF)
            ->whereBetween('date_expiration', [now()->startOfDay(), $notifyThreshold])
            ->chunkById(200, function ($abonnements) use (&$count) {
                foreach ($abonnements as $abonnement) {
                    $joursRestants = now()->startOfDay()->diffInDays($abonnement->date_expiration->startOfDay(), false);
                    
                    // Notifier le Super Admin
                    if ($joursRestants >= 0 && $joursRestants <= 7) {
                        $dejaNotifie = $abonnement->derniere_notification && $abonnement->derniere_notification->greaterThan(now()->subDay());
                        if (!$dejaNotifie) {
                            NotificationService::notifierAbonnementExpire($abonnement, $joursRestants);
                            $abonnement->update(['derniere_notification' => now()]);
                        }
                    }

                    // Notifier l'utilisateur si demandé
                    if ($this->option('notify') && $abonnement->user && $abonnement->user->wantsSubscriptionNotifications()) {
                        $dejaNotifie = $abonnement->derniere_notification && $abonnement->derniere_notification->greaterThan(now()->subDay());

                        if (!$dejaNotifie && $joursRestants >= 0) {
                            $abonnement->user->notify(new SubscriptionExpiringNotification($abonnement, $joursRestants));
                            $abonnement->update(['derniere_notification' => now()]);
                        }
                    }
                    $count++;
                }
            });

        return $count;
    }
}
