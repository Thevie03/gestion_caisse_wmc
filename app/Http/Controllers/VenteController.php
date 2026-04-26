<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Facture;
use App\Models\HistoriqueModificationVente;
use App\Models\PaiementVente;
use App\Models\Category;
use App\Models\Boutique;
use App\Models\Fournisseur;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Pour les employés, utiliser leur boutique_id si aucune session n'est définie
        // Pour les propriétaires, utiliser la session boutique_active (ils peuvent avoir plusieurs boutiques)
        if (!$boutiqueId && $user->isEmploye()) {
            $boutiqueId = $user->boutique_id;
        } elseif (!$boutiqueId && $user->isOwner()) {
            // Si un propriétaire n'a pas de session, utiliser sa boutique_id par défaut
            $boutiqueId = $user->boutique_id;
        }

        $applyCommonFilters = function ($query) use ($user, $boutiqueId, $request) {
            // Utiliser la boutique active de la session pour tous (propriétaires peuvent switcher)
            // Pour les employés, utiliser leur boutique_id
            if ($user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($boutiqueId) {
                $query->where('boutique_id', $boutiqueId);
            }

            if ($request->filled('date_debut')) {
                $query->whereDate('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->whereDate('created_at', '<=', $request->date_fin);
            }

            if ($request->filled('recherche')) {
                $search = trim($request->recherche);
                $query->where(function($subQuery) use ($search) {
                    $subQuery->where('numero_vente', 'like', "%{$search}%")
                        ->orWhereHas('client', function($clientQuery) use ($search) {
                            $clientQuery->where(function($q) use ($search) {
                                $q->where('nom', 'like', "%{$search}%")
                                  ->orWhere('prenom', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%")
                                  ->orWhere('telephone', 'like', "%{$search}%");
                            });
                        })
                        ->orWhereHas('user', function($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('boutique', function($boutiqueQuery) use ($search) {
                            $boutiqueQuery->where('nom', 'like', "%{$search}%");
                        });
                });
            }
        };

        // Construire la requête
        $baseQuery = Vente::with(['user', 'boutique', 'client', 'venteDetails.produit', 'facture', 'paiements']);
        $applyCommonFilters($baseQuery);

        if ($request->filled('mode_paiement')) {
            $mode = $request->mode_paiement;
            $baseQuery->where(function ($query) use ($mode) {
                $query->whereHas('paiements', function ($paiementQuery) use ($mode) {
                    $paiementQuery->where('mode_paiement', $mode);
                })->orWhere(function ($subQuery) use ($mode) {
                    $subQuery->doesntHave('paiements')->where('mode_paiement', $mode);
                });
            });
        }

        // Optimisation : Select uniquement les colonnes nécessaires pour la liste
        $ventes = (clone $baseQuery)
            ->select('ventes.id', 'ventes.numero_vente', 'ventes.total_final', 'ventes.mode_paiement',
                     'ventes.statut_paiement', 'ventes.created_at', 'ventes.boutique_id', 'ventes.user_id',
                     'ventes.client_id', 'ventes.montant_paye', 'ventes.solde_restant')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Optimisation : Calculer toutes les statistiques en une seule requête au lieu de 5 clones
        $statsQuery = clone $baseQuery;
        $statsData = $statsQuery
            ->selectRaw('
                COUNT(*) as total_ventes,
                COALESCE(SUM(total_final), 0) as chiffre_affaires
            ')
            ->first();

        $paiementsStats = PaiementVente::select('mode_paiement', DB::raw('COALESCE(SUM(montant), 0) as total'))
            ->whereHas('vente', function ($query) use ($applyCommonFilters, $request) {
                $applyCommonFilters($query);

                if ($request->filled('mode_paiement')) {
                    $mode = $request->mode_paiement;
                    $query->where(function ($venteQuery) use ($mode) {
                        $venteQuery->whereHas('paiements', function ($paiementQuery) use ($mode) {
                            $paiementQuery->where('mode_paiement', $mode);
                        })->orWhere(function ($subQuery) use ($mode) {
                            $subQuery->doesntHave('paiements')->where('mode_paiement', $mode);
                        });
                    });
                }
            })
            ->groupBy('mode_paiement')
            ->pluck('total', 'mode_paiement');

        $stats = [
            'total_ventes' => $statsData->total_ventes ?? 0,
            'chiffre_affaires' => $statsData->chiffre_affaires ?? 0,
            'ventes_especes' => $paiementsStats['especes'] ?? 0,
            'ventes_mobile' => ($paiementsStats['mobile_money'] ?? 0)
                + ($paiementsStats['wave'] ?? 0)
                + ($paiementsStats['orange_money'] ?? 0)
                + ($paiementsStats['mtn_money'] ?? 0),
            'ventes_carte' => $paiementsStats['carte'] ?? 0,
        ];

        return view('ventes.index', compact('ventes', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer les produits de la boutique active
        $query = Produit::where('actif', true);

        // Les propriétaires et employés ne voient que les produits de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Optimisation: limiter les colonnes et le volume initial pour accélérer le rendu POS
        $produits = $query
            ->select('id', 'nom', 'categorie', 'prix_vente', 'quantite_stock', 'image')
            ->orderBy('nom')
            ->limit(300)
            ->get();

        return view('ventes.create', compact('produits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produits' => 'required|array|min:1',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            'remise' => 'nullable|numeric|min:0',
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string|max:500',
            'paiements' => 'nullable|array|min:1',
            'paiements.*.mode' => 'required_with:paiements.*.montant|in:especes,wave,orange_money,mtn_money,carte,mobile_money',
            'paiements.*.montant' => 'nullable|numeric|min:0',
            'paiements.*.notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        DB::beginTransaction();
        try {
            // Calculer le total
            $total = 0;
            $produitsVendus = [];

            foreach ($request->produits as $produitData) {
                $produit = Produit::findOrFail($produitData['id']);

                // Vérifier le stock uniquement si le stock est géré (quantite_stock > 0)
                // Permettre la vente même si stock = 0 pour les boutiques qui ne gèrent pas le stock
                if ($produit->quantite_stock > 0 && $produit->quantite_stock < $produitData['quantite']) {
                    throw new \Exception("Stock insuffisant pour {$produit->nom}. Stock disponible: {$produit->quantite_stock}");
                }

                // Arrondir les calculs pour éviter les erreurs de précision float (FCFA = entiers)
                $sousTotal = round($produit->prix_vente * $produitData['quantite']);
                $total = round($total + $sousTotal);

                $produitsVendus[] = [
                    'produit' => $produit,
                    'quantite' => $produitData['quantite'],
                    'prix_unitaire' => round($produit->prix_vente), // Arrondir le prix unitaire
                    'sous_total' => $sousTotal
                ];
            }

            // Arrondir la remise et le total final
            $remise = round($request->remise ?? 0);
            $totalFinal = round($total - $remise);

            $paiementsCollecte = collect($request->paiements ?? [])
                ->map(function ($paiement) {
                    return [
                        'mode' => $paiement['mode'] ?? null,
                        'montant' => isset($paiement['montant']) ? round((float) $paiement['montant']) : 0,
                        'notes' => $paiement['notes'] ?? null,
                    ];
                })
                ->filter(function ($paiement) {
                    return $paiement['mode'] !== null && $paiement['montant'] > 0;
                });

            $modeParDefaut = collect($request->paiements ?? [])->first()['mode'] ?? 'especes';

            if ($paiementsCollecte->isEmpty()) {
                $paiementsCollecte = collect([[
                    'mode' => $modeParDefaut,
                    'montant' => $totalFinal,
                    'notes' => 'Paiement initial',
                ]]);
            }

            $montantPaye = $paiementsCollecte->sum('montant');

            if ($montantPaye > $totalFinal) {
                return back()->with('error', 'Le total des paiements ne peut pas dépasser le montant de la facture (' . number_format($totalFinal, 0, ',', ' ') . ' FCFA).')->withInput();
            }

            $soldeRestant = max($totalFinal - $montantPaye, 0);
            $statutPaiement = $soldeRestant > 0 ? 'partiel' : 'complet';
            $modePrincipal = $paiementsCollecte->first()['mode'] ?? $modeParDefaut ?? 'especes';

            // Créer la vente
            // Utiliser la boutique active de la session pour les propriétaires
            $venteBoutiqueId = $user->isEmploye() ? $user->boutique_id : ($boutiqueId ?? $user->boutique_id);
            $vente = Vente::create([
                'user_id' => $user->id,
                'boutique_id' => $venteBoutiqueId,
                'client_id' => $request->client_id,
                'total' => $total,
                'remise' => $remise,
                'total_final' => $totalFinal,
                'montant_paye' => $montantPaye,
                'solde_restant' => $soldeRestant,
                'statut_paiement' => $statutPaiement,
                'mode_paiement' => $modePrincipal,
                'numero_vente' => '', // Sera généré automatiquement par le modèle
                'notes' => $request->notes,
            ]);

            // Enregistrer la traçabilité de tous les paiements saisis
            foreach ($paiementsCollecte as $index => $paiement) {
                if ($paiement['montant'] <= 0) {
                    continue;
                }

                PaiementVente::create([
                    'vente_id' => $vente->id,
                    'montant' => $paiement['montant'],
                    'mode_paiement' => $paiement['mode'],
                    'notes' => $paiement['notes'] ?? 'Paiement POS #' . ($index + 1),
                    'user_id' => $user->id,
                ]);
            }

            // Créer les détails de vente et mettre à jour le stock
            foreach ($produitsVendus as $produitVendu) {
                // Créer le détail de vente
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produitVendu['produit']->id,
                    'quantite' => $produitVendu['quantite'],
                    'prix_unitaire' => $produitVendu['prix_unitaire'],
                    'sous_total' => $produitVendu['sous_total'],
                ]);

                // Mettre à jour le stock
                $produitVendu['produit']->decrement('quantite_stock', $produitVendu['quantite']);

                // Créer un mouvement de stock
                $mouvement = $produitVendu['produit']->mouvementsStock()->create([
                    'type' => 'sortie',
                    'quantite' => $produitVendu['quantite'],
                    'motif' => 'Vente #' . $vente->numero_vente,
                    'user_id' => $user->id,
                    'boutique_id' => $vente->boutique_id
                ]);

                // Déclencher la notification
                NotificationService::notifierMouvementStock($mouvement, $produitVendu['produit'], $user);
            }

            // Créer automatiquement une facture pour la vente
            if (!$vente->facture) {
                $numeroFacture = $this->genererNumeroFacture($vente->boutique_id);
                Facture::create([
                    'vente_id' => $vente->id,
                    'numero_facture' => $numeroFacture,
                    'lien_pdf' => null, // Sera généré à la demande
                ]);
            }

            DB::commit();

            // Invalider le cache du dashboard pour cette boutique
            $this->invaliderCacheDashboard($vente->boutique_id, $user->id);

            return redirect()->route('ventes.show', $vente)
                ->with('success', 'Vente enregistrée avec succès !');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Invalider le cache du dashboard
     */
    private function invaliderCacheDashboard($boutiqueId, $userId)
    {
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_totals_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_totals_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_ventes_chart_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_ventes_chart_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_produits_chart_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_produits_chart_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_dernieres_ventes_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_dernieres_ventes_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_produits_rupture_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_produits_rupture_' . $userId . '_all');
        \Illuminate\Support\Facades\Cache::forget('dashboard_top_produits_' . $userId . '_' . $boutiqueId);
        \Illuminate\Support\Facades\Cache::forget('dashboard_top_produits_' . $userId . '_all');
    }

    /**
     * Générer le numéro de facture
     */
    private function genererNumeroFacture($boutiqueId = null)
    {
        $prefixe = 'FAC';
        $annee = date('Y');
        $mois = date('m');

        // Compter les factures existantes de ce mois (pas les ventes)
        $query = \App\Models\Facture::whereYear('created_at', $annee)
                     ->whereMonth('created_at', $mois);

        // Filtrer par boutique si nécessaire
        if ($boutiqueId) {
            $query->whereHas('vente', function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            });
        }

        // Obtenir le dernier numéro de facture pour ce mois
        $dernierNumero = $query->orderBy('numero_facture', 'desc')
            ->value('numero_facture');

        // Extraire le numéro séquentiel du dernier numéro
        if ($dernierNumero) {
            // Format: FAC2025120001 -> extraire 0001
            $numeroSequence = (int) substr($dernierNumero, -4);
            $nombre = $numeroSequence + 1;
        } else {
            // Première facture du mois
            $nombre = 1;
        }

        // Vérifier que le numéro n'existe pas déjà (protection contre les race conditions)
        $numeroFacture = $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
        $tentatives = 0;
        while (\App\Models\Facture::where('numero_facture', $numeroFacture)->exists() && $tentatives < 100) {
            $nombre++;
            $numeroFacture = $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
            $tentatives++;
        }

        return $numeroFacture;
    }

    /**
     * Display the specified resource.
     */
    public function show(Vente $vente)
    {
        $vente->load(['user', 'boutique', 'venteDetails.produit', 'paiements.user', 'facture', 'client']);

        // Initialiser les soldes s'ils n'existent pas encore
        if ($vente->montant_paye === null) {
            $vente->montant_paye = 0;
            $vente->solde_restant = $vente->total_final;
            $vente->statut_paiement = 'impaye';
            $vente->save();
        }

        // Créer automatiquement une facture si elle n'existe pas
        if (!$vente->facture) {
            $numeroFacture = $this->genererNumeroFacture($vente->boutique_id);
            $facture = Facture::create([
                'vente_id' => $vente->id,
                'numero_facture' => $numeroFacture,
                'lien_pdf' => null,
            ]);
            // Recharger la relation
            $vente->load('facture');
        }

        return view('ventes.show', compact('vente'));
    }

    /**
     * Show the form for editing the specified resource (Admin seulement)
     */
    public function edit(Vente $vente)
    {
        // Vérifier que seul un admin ou le propriétaire de la boutique peut modifier une vente
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isOwner()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et les propriétaires de boutique peuvent modifier une vente.');
        }

        // Si c'est un propriétaire, vérifier que la vente appartient à une de ses boutiques
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($vente->boutique_id)) {
            abort(403, 'Vous ne pouvez modifier que les ventes de vos boutiques.');
        }

        $vente->load(['venteDetails.produit', 'client', 'paiements']);

        // Récupérer les clients actifs de la boutique
        $boutiqueId = session('boutique_active') ?? $vente->boutique_id;
        $clientsQuery = \App\Models\Client::where('actif', true);
        if ($user->isEmploye()) {
            $clientsQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $clientsQuery->where('boutique_id', $boutiqueId);
        }
        $clients = $clientsQuery->orderBy('nom')->get();

        $produits = Produit::where('actif', true)
            ->where('boutique_id', $vente->boutique_id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'categorie', 'prix_vente', 'quantite_stock']);

        return view('ventes.edit', compact('vente', 'clients', 'produits'));
    }

    /**
     * Update the specified resource in storage (Admin ou propriétaire de boutique)
     */
    public function update(Request $request, Vente $vente)
    {
        // Vérifier que seul un admin ou le propriétaire de la boutique peut modifier une vente
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isOwner()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et les propriétaires de boutique peuvent modifier une vente.');
        }

        // Si c'est un propriétaire, vérifier que la vente appartient à une de ses boutiques
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($vente->boutique_id)) {
            abort(403, 'Vous ne pouvez modifier que les ventes de vos boutiques.');
        }

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'remise' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,carte,mobile_money',
            'statut_paiement' => 'required|in:complet,partiel,impaye',
            'details' => 'required|array|min:1',
            'details.*.id' => 'nullable|exists:vente_details,id',
            'details.*.produit_id' => 'required|exists:produits,id',
            'details.*.quantite' => 'required|integer|min:1',
            'details.*.prix_unitaire' => 'required|numeric|min:0',
            'details.*.sous_total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Sauvegarder les données avant modification pour l'historique
            $vente->load(['venteDetails.produit', 'client']);
            $donneesAvant = [
                'client_id' => $vente->client_id,
                'client_nom' => $vente->client ? $vente->client->nom_complet : 'Anonyme',
                'remise' => $vente->remise,
                'total' => $vente->total,
                'total_final' => $vente->total_final,
                'mode_paiement' => $vente->mode_paiement,
                'statut_paiement' => $vente->statut_paiement,
                'notes' => $vente->notes,
                'produits' => $vente->venteDetails->map(function($detail) {
                    return [
                        'produit_id' => $detail->produit_id,
                        'produit_nom' => $detail->produit->nom,
                        'quantite' => $detail->quantite,
                        'prix_unitaire' => $detail->prix_unitaire,
                        'sous_total' => $detail->sous_total,
                    ];
                })->toArray(),
            ];

            $totalSansRemise = 0;
            $produitsModifies = [];

            $detailsExistants = $vente->venteDetails()->with('produit')->get()->keyBy('id');
            $idsExistants = $detailsExistants->keys()->map(fn($id) => (int) $id)->all();
            $idsSoumis = collect($request->details)
                ->pluck('id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->all();

            // Supprimer les lignes retirées dans le formulaire et restaurer leur stock
            $idsSupprimes = array_diff($idsExistants, $idsSoumis);
            foreach ($idsSupprimes as $idSupprime) {
                $detailSupprime = $detailsExistants->get($idSupprime);
                if (!$detailSupprime) {
                    continue;
                }

                $produitSupprime = $detailSupprime->produit;
                if ($produitSupprime) {
                    $produitSupprime->increment('quantite_stock', $detailSupprime->quantite);
                    $produitSupprime->mouvementsStock()->create([
                        'type' => 'ajustement',
                        'quantite' => $detailSupprime->quantite,
                        'motif' => 'Modification vente #' . $vente->numero_vente . ' - Retrait produit',
                        'user_id' => $user->id,
                        'boutique_id' => $vente->boutique_id
                    ]);
                }

                $produitsModifies[] = [
                    'produit_id' => $detailSupprime->produit_id,
                    'produit_nom' => $produitSupprime?->nom ?? 'Produit supprimé',
                    'ancienne_quantite' => $detailSupprime->quantite,
                    'nouvelle_quantite' => 0,
                    'ancien_prix' => $detailSupprime->prix_unitaire,
                    'nouveau_prix' => 0,
                    'ancien_sous_total' => $detailSupprime->sous_total,
                    'nouveau_sous_total' => 0,
                ];

                $detailSupprime->delete();
            }

            // Mettre à jour les lignes existantes et ajouter les nouvelles
            foreach ($request->details as $detailData) {
                $produit = Produit::findOrFail($detailData['produit_id']);
                $detailId = $detailData['id'] ?? null;
                $detailExistant = $detailId ? $detailsExistants->get((int) $detailId) : null;

                if ($detailId && (!$detailExistant || $detailExistant->vente_id !== $vente->id)) {
                    throw new \Exception('Détail de vente non autorisé.');
                }

                $ancienneQuantite = $detailExistant ? $detailExistant->quantite : 0;
                $ancienPrix = $detailExistant ? $detailExistant->prix_unitaire : 0;
                $ancienSousTotal = $detailExistant ? $detailExistant->sous_total : 0;

                $nouvelleQuantite = (int) $detailData['quantite'];
                $nouveauPrix = round((float) $detailData['prix_unitaire']);
                $nouveauSousTotal = round($nouvelleQuantite * $nouveauPrix);

                $stockDisponible = ($detailExistant && $detailExistant->produit_id === $produit->id)
                    ? $produit->quantite_stock + $ancienneQuantite
                    : $produit->quantite_stock;
                if ($stockDisponible < $nouvelleQuantite) {
                    throw new \Exception("Stock insuffisant pour {$produit->nom}. Stock disponible: {$stockDisponible}, Quantité demandée: {$nouvelleQuantite}");
                }

                if ($detailExistant && $detailExistant->produit_id !== $produit->id) {
                    $ancienProduit = Produit::find($detailExistant->produit_id);
                    if ($ancienProduit) {
                        $ancienProduit->increment('quantite_stock', $ancienneQuantite);
                    }
                    $produit->decrement('quantite_stock', $nouvelleQuantite);
                } elseif ($detailExistant) {
                    $produit->increment('quantite_stock', $ancienneQuantite);
                    $produit->decrement('quantite_stock', $nouvelleQuantite);
                } else {
                    $produit->decrement('quantite_stock', $nouvelleQuantite);
                }

                $differenceQuantite = $nouvelleQuantite - $ancienneQuantite;
                if ($differenceQuantite != 0) {
                    $produit->mouvementsStock()->create([
                        'type' => $differenceQuantite > 0 ? 'sortie' : 'ajustement',
                        'quantite' => abs($differenceQuantite),
                        'motif' => $detailExistant
                            ? ($differenceQuantite > 0
                                ? 'Modification vente #' . $vente->numero_vente . ' - Augmentation quantité'
                                : 'Modification vente #' . $vente->numero_vente . ' - Réduction quantité')
                            : 'Modification vente #' . $vente->numero_vente . ' - Ajout produit',
                        'user_id' => $user->id,
                        'boutique_id' => $vente->boutique_id
                    ]);
                }

                if ($detailExistant) {
                    $detailExistant->update([
                        'produit_id' => $produit->id,
                        'quantite' => $nouvelleQuantite,
                        'prix_unitaire' => $nouveauPrix,
                        'sous_total' => $nouveauSousTotal,
                    ]);
                } else {
                    VenteDetail::create([
                        'vente_id' => $vente->id,
                        'produit_id' => $produit->id,
                        'quantite' => $nouvelleQuantite,
                        'prix_unitaire' => $nouveauPrix,
                        'sous_total' => $nouveauSousTotal,
                    ]);
                }

                if (!$detailExistant || $ancienneQuantite != $nouvelleQuantite || $ancienPrix != $nouveauPrix) {
                    $produitsModifies[] = [
                        'produit_id' => $produit->id,
                        'produit_nom' => $produit->nom,
                        'ancienne_quantite' => $ancienneQuantite,
                        'nouvelle_quantite' => $nouvelleQuantite,
                        'ancien_prix' => $ancienPrix,
                        'nouveau_prix' => $nouveauPrix,
                        'ancien_sous_total' => $ancienSousTotal,
                        'nouveau_sous_total' => $nouveauSousTotal,
                    ];
                }

                $totalSansRemise = round($totalSansRemise + $nouveauSousTotal);
            }

            // Calculer le nouveau total final (arrondir la remise et le total final)
            $remise = round($request->remise ?? 0);
            $nouveauTotalFinal = round($totalSansRemise - $remise);

            // Sauvegarder les données après modification pour l'historique
            $donneesApres = [
                'client_id' => $request->client_id,
                'client_nom' => $request->client_id ? \App\Models\Client::find($request->client_id)?->nom_complet : 'Anonyme',
                'remise' => $remise,
                'total' => $totalSansRemise,
                'total_final' => $nouveauTotalFinal,
                'mode_paiement' => $request->mode_paiement,
                'statut_paiement' => $request->statut_paiement,
                'notes' => $request->notes,
            ];

            // Construire la description des changements
            $changements = [];
            if ($donneesAvant['client_id'] != $request->client_id) {
                $changements[] = "Client modifié";
            }
            if ($donneesAvant['remise'] != $remise) {
                $changements[] = "Remise modifiée : " . number_format($donneesAvant['remise'], 0, ',', ' ') . " → " . number_format($remise, 0, ',', ' ') . " FCFA";
            }
            if ($donneesAvant['mode_paiement'] != $request->mode_paiement) {
                $changements[] = "Mode de paiement modifié : " . $donneesAvant['mode_paiement'] . " → " . $request->mode_paiement;
            }
            if ($donneesAvant['statut_paiement'] != $request->statut_paiement) {
                $changements[] = "Statut de paiement modifié : " . $donneesAvant['statut_paiement'] . " → " . $request->statut_paiement;
            }
            if ($donneesAvant['total_final'] != $nouveauTotalFinal) {
                $changements[] = "Total modifié : " . number_format($donneesAvant['total_final'], 0, ',', ' ') . " → " . number_format($nouveauTotalFinal, 0, ',', ' ') . " FCFA";
            }
            if (count($produitsModifies) > 0) {
                $changements[] = count($produitsModifies) . " produit(s) modifié(s)";
            }

            // Mettre à jour la vente
            $vente->update([
                'client_id' => $request->client_id,
                'remise' => $remise,
                'total' => $totalSansRemise,
                'total_final' => $nouveauTotalFinal,
                'mode_paiement' => $request->mode_paiement,
                'statut_paiement' => $request->statut_paiement,
                'notes' => $request->notes,
            ]);

            // Recalculer le solde restant selon le statut de paiement
            $montantTotalPaye = $vente->paiements()->sum('montant');

            if ($request->statut_paiement === 'complet') {
                $vente->montant_paye = $nouveauTotalFinal;
                $vente->solde_restant = 0;
            } elseif ($request->statut_paiement === 'partiel') {
                // Conserver les paiements existants ou utiliser le montant payé actuel
                $vente->montant_paye = $montantTotalPaye > 0 ? $montantTotalPaye : ($nouveauTotalFinal / 2);
                $vente->solde_restant = $nouveauTotalFinal - $vente->montant_paye;
            } else {
                // impaye
                $vente->montant_paye = 0;
                $vente->solde_restant = $nouveauTotalFinal;
            }

            $vente->save();

            // Enregistrer dans l'historique
            HistoriqueModificationVente::create([
                'type_action' => 'modification',
                'vente_id' => $vente->id,
                'numero_vente' => $vente->numero_vente,
                'user_id' => $user->id,
                'boutique_id' => $vente->boutique_id,
                'donnees_avant' => $donneesAvant,
                'donnees_apres' => $donneesApres,
                'changements' => implode(' | ', $changements),
                'produits_modifies' => $produitsModifies,
            ]);

            DB::commit();

            return redirect()->route('ventes.show', $vente)
                ->with('success', 'Vente modifiée avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la modification : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (Admin ou propriétaire de boutique)
     */
    public function destroy($id)
    {
        // Récupérer la vente avec vérification d'existence
        $vente = Vente::findOrFail($id);

        // Vérifier que seul un admin ou le propriétaire de la boutique peut supprimer une vente
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isOwner()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et les propriétaires de boutique peuvent supprimer une vente.');
        }

        // Si c'est un propriétaire, vérifier que la vente appartient à une de ses boutiques
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($vente->boutique_id)) {
            abort(403, 'Vous ne pouvez supprimer que les ventes de vos boutiques.');
        }

        DB::beginTransaction();
        try {
            // Sauvegarder les données avant suppression pour l'historique
            $vente->load(['venteDetails.produit', 'client', 'boutique']);
            $donneesAvant = [
                'client_id' => $vente->client_id,
                'client_nom' => $vente->client ? $vente->client->nom_complet : 'Anonyme',
                'remise' => $vente->remise,
                'total' => $vente->total,
                'total_final' => $vente->total_final,
                'mode_paiement' => $vente->mode_paiement,
                'statut_paiement' => $vente->statut_paiement,
                'notes' => $vente->notes,
                'montant_paye' => $vente->montant_paye,
                'solde_restant' => $vente->solde_restant,
                'produits' => $vente->venteDetails->map(function($detail) {
                    return [
                        'produit_id' => $detail->produit_id,
                        'produit_nom' => $detail->produit->nom,
                        'quantite' => $detail->quantite,
                        'prix_unitaire' => $detail->prix_unitaire,
                        'sous_total' => $detail->sous_total,
                    ];
                })->toArray(),
            ];

            $numeroVente = $vente->numero_vente;
            $boutiqueId = $vente->boutique_id;

            // Restaurer le stock des produits vendus
            foreach ($vente->venteDetails as $detail) {
                $produit = $detail->produit;
                if ($produit) {
                    // Restaurer le stock
                    $produit->increment('quantite_stock', $detail->quantite);

                    // Créer un mouvement de stock de type ajustement pour la restauration
                    $produit->mouvementsStock()->create([
                        'type' => 'ajustement',
                        'quantite' => $detail->quantite,
                        'motif' => 'Restauration stock - Suppression vente #' . $vente->numero_vente,
                        'user_id' => $user->id,
                        'boutique_id' => $vente->boutique_id
                    ]);
                }
            }

            // Supprimer la facture associée si elle existe
            if ($vente->facture) {
                $vente->facture->delete();
            }

            // Supprimer les paiements
            $vente->paiements()->delete();

            // Supprimer les détails de vente
            $vente->details()->delete();

            // Supprimer la vente
            $vente->delete();

            // Enregistrer dans l'historique AVANT le commit
            HistoriqueModificationVente::create([
                'type_action' => 'suppression',
                'vente_id' => null, // La vente n'existe plus
                'numero_vente' => $numeroVente,
                'user_id' => $user->id,
                'boutique_id' => $boutiqueId,
                'donnees_avant' => $donneesAvant,
                'donnees_apres' => null,
                'changements' => 'Vente supprimée. Stock des produits restauré.',
                'produits_modifies' => $donneesAvant['produits'],
            ]);

            DB::commit();

            return redirect()->route('ventes.index')
                ->with('success', 'Vente supprimée avec succès. Le stock des produits a été restauré.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('ventes.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Interface POS (Point of Sale)
     */
    public function pos()
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer les produits de la boutique active
        // Ne pas filtrer par stock car le stock est maintenant optionnel
        // pour permettre la flexibilité selon le type de boutique
        $query = Produit::where('actif', true);

        // Les employés voient uniquement les produits de leur boutique
        // Les propriétaires voient les produits de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Optimisation: limiter les colonnes et le volume initial pour accélérer le rendu POS
        $produits = $query
            ->select('id', 'nom', 'categorie', 'prix_vente', 'quantite_stock', 'image')
            ->orderBy('nom')
            ->limit(300)
            ->get();

        // Récupérer les clients actifs de la boutique
        $clientsQuery = \App\Models\Client::where('actif', true);
        if ($user->isEmploye()) {
            $clientsQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $clientsQuery->where('boutique_id', $boutiqueId);
        }
        $clients = $clientsQuery->orderBy('prenom')->orderBy('nom')->get();

        $activeBoutiqueId = $user->isEmploye() ? $user->boutique_id : ($boutiqueId ?? $user->boutique_id);

        // Récupérer les catégories de la boutique active pour le formulaire modal
        $categoriesParBoutique = Category::withoutGlobalScopes()
            ->active()
            ->orderBy('nom')
            ->get()
            ->groupBy('boutique_id')
            ->map(function($categories) {
                return $categories->map(fn($categorie) => [
                    'id' => $categorie->id,
                    'nom' => $categorie->nom,
                ]);
            })
            ->mapWithKeys(function($categories, $key) {
                return [(string) ($key ?? '') => $categories];
            });

        $categories = $categoriesParBoutique->get((string) $activeBoutiqueId, collect());

        // Récupérer les boutiques disponibles pour le formulaire modal
        $boutiquesQuery = Boutique::where('actif', true)->orderBy('nom');
        if ($user->isEmploye()) {
            // Les employés voient uniquement leur boutique
            $boutiques = $boutiquesQuery->where('id', $user->boutique_id)->get();
        } elseif ($user->isOwner()) {
            // Les propriétaires voient toutes leurs boutiques
            $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
            $boutiques = $ownedBoutiques->isEmpty() && $user->boutique_id
                ? collect([$user->boutique])
                : $ownedBoutiques;
        } elseif ($user->isSuperAdmin()) {
            // Seul le super admin voit toutes les boutiques
            $boutiques = $boutiquesQuery->get();
        } elseif ($user->isAdmin()) {
            // Les autres admins voient uniquement leurs boutiques assignées via la table pivot boutique_user
            // Ne pas utiliser boutique_id car il peut pointer vers une boutique non assignée
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
        } else {
            // Pour les autres utilisateurs, aucune boutique
            $boutiques = collect();
        }

        $boutiqueId = $activeBoutiqueId;

        // Récupérer les fournisseurs actifs de la boutique pour le formulaire modal
        $fournisseursQuery = Fournisseur::where('actif', true);
        if ($user->isEmploye()) {
            $fournisseursQuery->where('boutique_id', $user->boutique_id);
        } elseif ($activeBoutiqueId) {
            $fournisseursQuery->where('boutique_id', $activeBoutiqueId);
        }
        $fournisseurs = $fournisseursQuery->orderBy('nom')->get();

        $activeBoutique = $activeBoutiqueId
            ? Boutique::select('id', 'pos_banner_image', 'pos_stock_image', 'pos_payment_image')->find($activeBoutiqueId)
            : null;

        return view('ventes.pos', compact(
            'produits',
            'clients',
            'categories',
            'categoriesParBoutique',
            'boutiques',
            'boutiqueId',
            'fournisseurs',
            'activeBoutique'
        ));
    }

    /**
     * Imprimer une facture
     */
    public function imprimer(Vente $vente, Request $request)
    {
        // Charger la boutique avec theme_color et ticket_width
        $vente->load(['user', 'boutique:id,nom,theme_color,logo,adresse,telephone,email,ticket_width', 'client', 'venteDetails.produit']);

        // S'assurer que theme_color et ticket_width sont chargés
        if ($vente->boutique && (!isset($vente->boutique->theme_color) || !isset($vente->boutique->ticket_width))) {
            $vente->boutique = \App\Models\Boutique::select('id', 'nom', 'theme_color', 'logo', 'adresse', 'telephone', 'email', 'ticket_width')->find($vente->boutique_id);
        }

        // Déterminer le type de facture selon le paramètre
        $type = $request->get('type', 'simple');

        if ($type === 'professionnelle') {
            return view('ventes.facture-professionnelle', compact('vente'));
        }

        // Récupérer la largeur du ticket (58mm pour POS, 80mm par défaut)
        $ticketWidth = $vente->boutique->ticket_width ?? 80;
        // Permettre de forcer le format via paramètre URL
        if ($request->has('width')) {
            $ticketWidth = in_array($request->width, [58, 80]) ? (int)$request->width : $ticketWidth;
        }

        return view('ventes.facture', compact('vente', 'ticketWidth'));
    }
}
