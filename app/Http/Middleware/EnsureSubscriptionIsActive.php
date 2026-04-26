<?php

namespace App\Http\Middleware;

use App\Models\Abonnement;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Exclure les routes admin du contrôle d'abonnement
        if ($request->routeIs('admin.*')) {
            return $next($request);
        }

        // Les admins et les propriétaires de boutique peuvent toujours accéder
        if ($user->isAdmin() || $user->isOwner()) {
            return $next($request);
        }

        // Pour les employés simples, vérifier l'abonnement du propriétaire de leur boutique
        if ($user->isEmploye() && $user->boutique_id) {
            $boutique = $user->boutique;
            if ($boutique && $boutique->owner_id) {
                $owner = $boutique->owner;
                if ($owner) {
                    $abonnementActif = $owner->abonnementActif;

                    if (!$abonnementActif) {
                        $this->mettreAJourStatut($owner);
                        abort(402, 'L\'abonnement de votre boutique est expiré. Veuillez contacter le propriétaire pour le renouveler.');
                    }

                    if ($abonnementActif->statut === Abonnement::STATUT_SUSPENDU) {
                        abort(403, 'L\'abonnement de votre boutique est suspendu. Contactez le support.');
                    }

                    // L'acquisition définitive n'expire jamais
                    if ($abonnementActif->type_abonnement !== Abonnement::TYPE_ACQUISITION_DEFINITIVE) {
                        if ($abonnementActif->date_expiration->isPast()) {
                            $abonnementActif->update(['statut' => Abonnement::STATUT_EXPIRE]);
                            abort(402, 'L\'abonnement de votre boutique a expiré. Veuillez contacter le propriétaire pour le renouveler.');
                        }
                    }

                    return $next($request);
                }
            }
        }

        // Pour les autres cas, vérifier l'abonnement de l'utilisateur lui-même
        $abonnementActif = $user->abonnementActif;

        if (!$abonnementActif) {
            $this->mettreAJourStatut($user);
            abort(402, 'Votre abonnement est expiré. Veuillez le renouveler pour continuer.');
        }

        if ($abonnementActif->statut === Abonnement::STATUT_SUSPENDU) {
            abort(403, 'Votre abonnement est suspendu. Contactez le support.');
        }

        // L'acquisition définitive n'expire jamais
        if ($abonnementActif->type_abonnement !== Abonnement::TYPE_ACQUISITION_DEFINITIVE) {
            if ($abonnementActif->date_expiration->isPast()) {
                $abonnementActif->update(['statut' => Abonnement::STATUT_EXPIRE]);
                abort(402, 'Votre abonnement a expiré. Veuillez le renouveler.');
            }
        }

        return $next($request);
    }

    protected function mettreAJourStatut($user): void
    {
        if ($user->subscription_status !== Abonnement::STATUT_SUSPENDU) {
            $user->forceFill([
                'subscription_status' => Abonnement::STATUT_EXPIRE,
                'subscription_expires_at' => now(),
            ])->save();
        }
    }
}


