<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\PaiementAbonnement;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaiementAbonnementController extends Controller
{
    public function index(Request $request)
    {
        $query = PaiementAbonnement::with(['abonnement.user', 'user', 'confirmePar']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $paiements = $query->latest('date_paiement')->paginate(20);

        $stats = [
            'total' => PaiementAbonnement::count(),
            'confirmes' => PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME)->count(),
            'en_attente' => PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_EN_ATTENTE)->count(),
            'revenus_totaux' => PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME)->sum('montant'),
        ];

        $boutiques = Boutique::where('actif', true)
            ->with(['owner' => function ($query) {
                $query->with(['abonnements' => function ($aboQuery) {
                    $aboQuery->orderByDesc('date_expiration');
                }]);
            }])
            ->orderBy('nom')
            ->get();

        $boutiquesData = $boutiques->mapWithKeys(function ($boutique) {
            $owner = $boutique->owner;
            $abonnement = $owner?->abonnements->first();

            return [
                $boutique->id => [
                    'owner_id' => $owner?->id,
                    'owner_name' => $owner?->name,
                    'owner_email' => $owner?->email,
                    'current_type' => $abonnement?->type_abonnement,
                    'current_expiration' => $abonnement?->date_expiration?->format('Y-m-d'),
                    'current_status' => $abonnement?->statut,
                    'current_amount' => $abonnement?->montant,
                ],
            ];
        });

        return view('admin.paiements.index', compact('paiements', 'stats', 'boutiques', 'boutiquesData'));
    }

    /**
     * Nouveau flux de paiement : sélection par boutique, abonnement calculé automatiquement.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
            'type_abonnement' => 'required|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive',
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,mobile_money,carte_bancaire,virement,cheque',
            'date_paiement' => 'required|date',
            'date_echeance' => 'nullable|date|after:date_paiement',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $boutique = Boutique::with('owner')->findOrFail($data['boutique_id']);

        if (!$boutique->owner) {
            return back()->with('error', "La boutique sélectionnée n'a pas d'administrateur associé.");
        }

        $user = $boutique->owner;
        $datePaiement = Carbon::parse($data['date_paiement']);
        $dateExpiration = $data['date_echeance']
            ? Carbon::parse($data['date_echeance'])
            : $this->calculateExpiration($datePaiement->copy(), $data['type_abonnement']);

        DB::beginTransaction();
        try {
            // Mettre à jour ou créer l'abonnement
            $abonnement = $user->abonnements()
                ->where('statut', Abonnement::STATUT_ACTIF)
                ->latest('date_expiration')
                ->first();

            if ($abonnement) {
                $abonnement->update([
                    'type_abonnement' => $data['type_abonnement'],
                    'date_debut' => $datePaiement,
                    'date_expiration' => $dateExpiration,
                    'montant' => $data['montant'],
                    'notes' => $data['notes'],
                ]);
            } else {
                $abonnement = $user->abonnements()->create([
                    'type_abonnement' => $data['type_abonnement'],
                    'date_debut' => $datePaiement,
                    'date_expiration' => $dateExpiration,
                    'montant' => $data['montant'],
                    'statut' => Abonnement::STATUT_ACTIF,
                    'notes' => $data['notes'],
                ]);
            }

            // Mise à jour de l'utilisateur
            $user->forceFill([
                'subscription_status' => Abonnement::STATUT_ACTIF,
                'subscription_expires_at' => $dateExpiration,
            ])->save();

            $reference = $data['reference'] ?: $this->generateReference();

            $paiement = PaiementAbonnement::create([
                'abonnement_id' => $abonnement->id,
                'user_id' => $user->id,
                'montant' => $data['montant'],
                'mode_paiement' => $data['mode_paiement'],
                'statut' => PaiementAbonnement::STATUT_CONFIRME,
                'date_paiement' => $datePaiement,
                'reference' => $reference,
                'notes' => $data['notes'],
                'confirme_par' => auth()->id(),
                'confirme_le' => now(),
            ]);

            DB::commit();

            NotificationService::notifierPaiement($paiement, 'confirme');

            return back()->with('success', "Paiement enregistré et abonnement mis à jour pour {$boutique->nom}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'enregistrement du paiement : ' . $e->getMessage());
        }
    }

    public function confirmer(PaiementAbonnement $paiement)
    {
        DB::beginTransaction();
        try {
            $paiement->update([
                'statut' => PaiementAbonnement::STATUT_CONFIRME,
                'confirme_par' => auth()->id(),
                'confirme_le' => now(),
            ]);

            $abonnement = $paiement->abonnement;
            $user = $abonnement->user;

            // Si l'abonnement est expiré, créer un nouveau cycle d'abonnement
            if ($abonnement->estExpire()) {
                // Calculer les nouvelles dates basées sur le type d'abonnement
                $dateDebut = now();
                $dateExpiration = match($abonnement->type_abonnement) {
                    Abonnement::TYPE_MENSUEL => $dateDebut->clone()->addMonth(),
                    Abonnement::TYPE_TRIMESTRIEL => $dateDebut->clone()->addMonths(3),
                    Abonnement::TYPE_SEMESTRIEL => $dateDebut->clone()->addMonths(6),
                    Abonnement::TYPE_ANNUEL => $dateDebut->clone()->addYear(),
                    default => $dateDebut->clone()->addMonth(),
                };

                // Créer un nouvel abonnement
                $nouvelAbonnement = $user->abonnements()->create([
                    'type_abonnement' => $abonnement->type_abonnement,
                    'date_debut' => $dateDebut,
                    'date_expiration' => $dateExpiration,
                    'statut' => Abonnement::STATUT_ACTIF,
                    'montant' => $abonnement->montant,
                    'notes' => "Renouvellement automatique - Paiement #{$paiement->id}",
                ]);

                // Lier le paiement au nouvel abonnement
                $paiement->update(['abonnement_id' => $nouvelAbonnement->id]);

                // Mettre à jour l'utilisateur
                $user->forceFill([
                    'subscription_status' => Abonnement::STATUT_ACTIF,
                    'subscription_expires_at' => $dateExpiration,
                ])->save();

                DB::commit();
                NotificationService::notifierPaiement($paiement, 'confirme');
                return back()->with('success', 'Paiement confirmé et abonnement renouvelé avec succès.');
            }

            // Si l'abonnement n'est pas expiré, vérifier si le paiement complet active l'abonnement
            if ($abonnement->estEntierementPaye()) {
                $abonnement->update(['statut' => Abonnement::STATUT_ACTIF]);

                // Mettre à jour l'utilisateur
                $user->forceFill([
                    'subscription_status' => Abonnement::STATUT_ACTIF,
                    'subscription_expires_at' => $abonnement->date_expiration,
                ])->save();
            } elseif ($abonnement->statut !== Abonnement::STATUT_ACTIF) {
                // Si l'abonnement n'est pas encore entièrement payé mais qu'il était suspendu/expiré
                // On peut l'activer partiellement si c'est le premier paiement
                $paiementsConfirmes = $abonnement->paiements()
                    ->where('statut', PaiementAbonnement::STATUT_CONFIRME)
                    ->count();

                if ($paiementsConfirmes === 1) {
                    // Premier paiement confirmé, activer l'abonnement
                    $abonnement->update(['statut' => Abonnement::STATUT_ACTIF]);
                }
            }

            DB::commit();
            NotificationService::notifierPaiement($paiement, 'confirme');
            return back()->with('success', 'Paiement confirmé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la confirmation du paiement: ' . $e->getMessage());
        }
    }

    public function show(PaiementAbonnement $paiement)
    {
        $paiement->load(['abonnement.user', 'user', 'confirmePar']);
        $abonnement = $paiement->abonnement;
        $montantPaye = $abonnement->montant_paye;
        $montantRestant = $abonnement->montant_restant;

        return view('admin.paiements.show', compact('paiement', 'abonnement', 'montantPaye', 'montantRestant'));
    }

    public function edit(PaiementAbonnement $paiement)
    {
        $paiement->load(['abonnement.user', 'user']);
        $abonnement = $paiement->abonnement;
        $boutique = $abonnement->user->boutique;

        return view('admin.paiements.edit', compact('paiement', 'abonnement', 'boutique'));
    }

    public function update(Request $request, PaiementAbonnement $paiement)
    {
        $rules = [
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,mobile_money,carte_bancaire,virement,cheque',
            'date_paiement' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'prolonger_abonnement' => 'nullable|boolean',
        ];

        if ($request->has('prolonger_abonnement') && $request->prolonger_abonnement) {
            $rules['type_abonnement'] = 'required|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive';
            $rules['date_echeance'] = 'nullable|date|after:date_paiement';
        }

        $data = $request->validate($rules);

        $abonnement = $paiement->abonnement;
        $user = $abonnement->user;
        $datePaiement = Carbon::parse($data['date_paiement']);

        DB::beginTransaction();
        try {
            // Mettre à jour le paiement
            $paiement->update([
                'montant' => $data['montant'],
                'mode_paiement' => $data['mode_paiement'],
                'date_paiement' => $datePaiement,
                'reference' => $data['reference'] ?? $paiement->reference,
                'notes' => $data['notes'] ?? $paiement->notes,
            ]);

            // Si on prolonge l'abonnement
            if ($request->has('prolonger_abonnement') && $request->prolonger_abonnement) {
                $typeAbonnement = $data['type_abonnement'];
                $dateExpiration = $data['date_echeance']
                    ? Carbon::parse($data['date_echeance'])
                    : $this->calculateExpiration($datePaiement->copy(), $typeAbonnement);

                // Mettre à jour l'abonnement
                $abonnement->update([
                    'type_abonnement' => $typeAbonnement,
                    'date_debut' => $datePaiement,
                    'date_expiration' => $dateExpiration,
                    'montant' => $data['montant'],
                    'statut' => Abonnement::STATUT_ACTIF,
                    'notes' => ($data['notes'] ?? '') . ' - Abonnement prolongé',
                ]);

                // Mettre à jour l'utilisateur
                $user->forceFill([
                    'subscription_status' => Abonnement::STATUT_ACTIF,
                    'subscription_expires_at' => $dateExpiration,
                ])->save();
            }

            DB::commit();

            return redirect()->route('admin.paiements.index')
                ->with('success', 'Paiement modifié avec succès' . ($request->has('prolonger_abonnement') && $request->prolonger_abonnement ? ' et abonnement prolongé' : '') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la modification du paiement : ' . $e->getMessage())->withInput();
        }
    }

    public function refuser(PaiementAbonnement $paiement)
    {
        $paiement->update([
            'statut' => PaiementAbonnement::STATUT_REFUSE,
            'confirme_par' => auth()->id(),
            'confirme_le' => now(),
        ]);

        // Notifier le Super Admin
        NotificationService::notifierPaiement($paiement, 'refuse');

        return back()->with('success', 'Paiement refusé.');
    }

    public function storeForAbonnement(Request $request, Abonnement $abonnement)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,mobile_money,carte_bancaire,virement,cheque',
            'date_paiement' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Vérifier que l'abonnement peut recevoir un paiement
        if (!$abonnement->peutRecevoirPaiement() && !$abonnement->estExpire()) {
            return back()->with('error', 'Cet abonnement est déjà entièrement payé et actif.');
        }

        // Vérifier le montant si l'abonnement n'est pas expiré (pour éviter les surpaiements)
        if (!$abonnement->estExpire()) {
            $montantRestant = $abonnement->montant_restant;
            if ($request->montant > $montantRestant + 0.01) { // Tolérance de 0.01
                return back()->with('error',
                    "Le montant saisi ({$request->montant} FCFA) dépasse le montant restant ({$montantRestant} FCFA)."
                )->withInput();
            }
        }

        PaiementAbonnement::create([
            'abonnement_id' => $abonnement->id,
            'user_id' => $abonnement->user_id,
            'montant' => $request->montant,
            'mode_paiement' => $request->mode_paiement,
            'statut' => PaiementAbonnement::STATUT_EN_ATTENTE,
            'date_paiement' => $request->date_paiement,
            'reference' => $request->reference,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Paiement enregistré. En attente de confirmation.');
    }

    private function calculateExpiration(Carbon $dateDebut, string $type): Carbon
    {
        return match ($type) {
            Abonnement::TYPE_TRIMESTRIEL => $dateDebut->clone()->addMonths(3),
            Abonnement::TYPE_SEMESTRIEL => $dateDebut->clone()->addMonths(6),
            Abonnement::TYPE_ANNUEL => $dateDebut->clone()->addYear(),
            Abonnement::TYPE_MENSUEL => $dateDebut->clone()->addMonth(),
            default => $dateDebut->clone()->addMonth(),
        };
    }

    private function generateReference(): string
    {
        return 'ABO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
    }
}
