<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function store(Request $request, User $user)
    {
        $this->ensureMerchant($user);

        $data = $request->validate([
            'type_abonnement' => 'required|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive',
            'date_debut' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'nullable|in:actif,expire,suspendu',
            'montant' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $abonnement = $this->subscriptionService->renew($user, $data);

        if (isset($data['statut'])) {
            $abonnement->update(['statut' => $data['statut']]);
            $user->forceFill(['subscription_status' => $data['statut']])->save();
        }

        return back()->with('success', 'Abonnement créé ou renouvelé avec succès.');
    }

    public function update(Request $request, Abonnement $abonnement)
    {
        $this->ensureMerchant($abonnement->user);

        $data = $request->validate([
            'statut' => 'required|in:actif,expire,suspendu',
            'date_expiration' => 'nullable|date',
            'montant' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $abonnement->update($data);

        $abonnement->user->forceFill([
            'subscription_status' => $abonnement->statut,
            'subscription_expires_at' => $abonnement->date_expiration,
        ])->save();

        return back()->with('success', 'Abonnement mis à jour.');
    }

    protected function ensureMerchant(User $user): void
    {
        if (!$user->isEmploye()) {
            abort(403, 'Action réservée aux comptes commerçants.');
        }
    }
}



