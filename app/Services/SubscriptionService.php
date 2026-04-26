<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    public function renew(User $user, array $payload): Abonnement
    {
        $dateDebut = Carbon::parse($payload['date_debut'] ?? now());
        $dateExpiration = Carbon::parse(
            $payload['date_expiration'] ?? $this->calculateExpiration($dateDebut, $payload['type_abonnement'])
        );

        $abonnement = $user->abonnements()->create([
            'type_abonnement' => $payload['type_abonnement'],
            'date_debut' => $dateDebut,
            'date_expiration' => $dateExpiration,
            'statut' => $payload['statut'] ?? Abonnement::STATUT_ACTIF,
            'montant' => $payload['montant'] ?? 0,
            'notes' => $payload['notes'] ?? null,
        ]);

        $user->forceFill([
            'subscription_status' => $abonnement->statut,
            'subscription_expires_at' => $abonnement->date_expiration,
        ])->save();

        return $abonnement;
    }

    protected function calculateExpiration(Carbon $dateDebut, string $type): Carbon
    {
        return match ($type) {
            Abonnement::TYPE_MENSUEL => $dateDebut->clone()->addMonth(),
            Abonnement::TYPE_TRIMESTRIEL => $dateDebut->clone()->addMonths(3),
            Abonnement::TYPE_SEMESTRIEL => $dateDebut->clone()->addMonths(6),
            Abonnement::TYPE_ANNUEL => $dateDebut->clone()->addYear(),
            Abonnement::TYPE_ACQUISITION_DEFINITIVE => $dateDebut->clone()->addYears(100), // Acquisition définitive = 100 ans
            default => $dateDebut->clone()->addMonth(), // Par défaut, mensuel
        };
    }
}

