<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produit;
use App\Models\MouvementStock;
use App\Models\Boutique;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer les produits avec leurs mouvements
        $baseQuery = Produit::with(['boutique', 'mouvementsStock' => function($q) {
            $q->latest()->take(5);
        }]);

        // Filtrer par boutique
        // Les employés voient uniquement les produits de leur boutique
        // Les propriétaires voient les produits de la boutique active (session)
        if ($user->isEmploye()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $baseQuery->where('boutique_id', $boutiqueId);
        }

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('categorie', 'like', "%{$search}%")
                  ->orWhere('code_produit', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'faible':
                    $baseQuery->whereRaw('quantite_stock <= stock_minimum');
                    break;
                case 'rupture':
                    $baseQuery->where('quantite_stock', 0);
                    break;
                case 'disponible':
                    $baseQuery->where('quantite_stock', '>', 0);
                    break;
            }
        }

        $produits = (clone $baseQuery)->orderBy('nom')->paginate(20);

        // Statistiques (utiliser la même requête filtrée)
        $statsQuery = clone $baseQuery;
        $stats = [
            'total_produits' => (clone $statsQuery)->count(),
            'stock_faible' => (clone $statsQuery)->whereRaw('quantite_stock <= stock_minimum')->count(),
            'rupture' => (clone $statsQuery)->where('quantite_stock', 0)->count(),
            'valeur_stock' => (clone $statsQuery)->sum(DB::raw('quantite_stock * prix_achat'))
        ];

        return view('stock.index', compact('produits', 'stats'));
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

        // Les propriétaires et employés ne voient que les mouvements de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        $produits = $query->orderBy('nom')->get();
        $categories = \App\Models\Category::active()->orderBy('nom')->get();
        // Récupérer les boutiques selon le rôle
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = \App\Models\Boutique::where('actif', true)->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = \App\Models\Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->get();
            }
        } else {
            $boutiques = collect();
        }
        // Récupérer les fournisseurs actifs de la boutique
        $fournisseursQuery = \App\Models\Fournisseur::where('actif', true);
        if ($user->isEmploye() || $user->isOwner()) {
            $fournisseursQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $fournisseursQuery->where('boutique_id', $boutiqueId);
        }
        $fournisseurs = $fournisseursQuery->orderBy('nom')->get();

        return view('stock.create', compact('produits', 'categories', 'boutiques', 'fournisseurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'type' => 'required|in:entree,sortie,ajustement',
            'quantite' => 'required|integer|min:1',
            'motif' => 'required|string|max:255'
        ]);

        $produit = Produit::findOrFail($request->produit_id);
        $user = auth()->user();
        // Les employés utilisent toujours leur boutique
        // Les propriétaires utilisent la boutique active de la session
        $boutiqueId = $user->isEmploye() ? $user->boutique_id : session('boutique_active');

        // Vérifier que le produit appartient à la bonne boutique
        if ($produit->boutique_id != $boutiqueId) {
            return back()->with('error', 'Ce produit n\'appartient pas à votre boutique.');
        }

        DB::beginTransaction();
        try {
            // Calculer la nouvelle quantité
            $quantiteModification = $request->type === 'sortie' ? -$request->quantite : $request->quantite;

            // Vérifier le stock pour les sorties
            if ($request->type === 'sortie' && $produit->quantite_stock < $request->quantite) {
                throw new \Exception("Stock insuffisant. Stock disponible : {$produit->quantite_stock}");
            }

            // Mettre à jour le stock
            $produit->increment('quantite_stock', $quantiteModification);

            // Créer le mouvement de stock
            $mouvement = MouvementStock::create([
                'produit_id' => $produit->id,
                'type' => $request->type,
                'quantite' => $request->quantite,
                'motif' => $request->motif,
                'user_id' => $user->id,
                'boutique_id' => $boutiqueId
            ]);

            // Déclencher la notification
            NotificationService::notifierMouvementStock($mouvement, $produit, $user);

            DB::commit();

            return redirect()->route('stock.index')
                ->with('success', 'Mouvement de stock enregistré avec succès !');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Vérifier l'accès au produit
        // Les employés ne peuvent accéder qu'à leur boutique
        if ($user->isEmploye() && $produit->boutique_id != $user->boutique_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isOwner() && !$user->ownsBoutique($produit->boutique_id)) {
            abort(403, 'Accès non autorisé à ce produit.');
        }
        if ($user->isAdmin() && $boutiqueId && $produit->boutique_id != $boutiqueId) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        // Récupérer l'historique des mouvements
        $mouvements = $produit->mouvementsStock()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiques du produit
        $stats = [
            'total_entrees' => $produit->mouvementsStock()->where('type', 'entree')->sum('quantite'),
            'total_sorties' => $produit->mouvementsStock()->where('type', 'sortie')->sum('quantite'),
            'total_ajustements' => $produit->mouvementsStock()->where('type', 'ajustement')->sum('quantite'),
            'dernier_mouvement' => $produit->mouvementsStock()->latest()->first()
        ];

        return view('stock.show', compact('produit', 'mouvements', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Les mouvements de stock ne peuvent pas être modifiés
        return redirect()->route('stock.index')
            ->with('error', 'Les mouvements de stock ne peuvent pas être modifiés pour des raisons de traçabilité.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Les mouvements de stock ne peuvent pas être modifiés
        return redirect()->route('stock.index')
            ->with('error', 'Les mouvements de stock ne peuvent pas être modifiés pour des raisons de traçabilité.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Les mouvements de stock ne peuvent pas être supprimés
        return redirect()->route('stock.index')
            ->with('error', 'Les mouvements de stock ne peuvent pas être supprimés pour des raisons de traçabilité.');
    }

    /**
     * Historique des mouvements de stock
     */
    public function historique(Request $request)
    {
        $this->validateListingFilters($request, [
            'type' => 'nullable|in:entree,sortie,ajustement,transfert',
        ]);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        $query = MouvementStock::with(['produit', 'user', 'boutique']);

        // Filtrer par boutique
        // Les propriétaires et employés ne voient que les mouvements de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        $mouvements = $query->orderBy('created_at', 'desc')->paginate(20);

        // Produits pour le filtre
        $produits = Produit::when($user->isEmploye() || $user->isOwner(), function($q) use ($user) {
            $q->where('boutique_id', $user->boutique_id);
        })
        ->when($boutiqueId, function($q) use ($boutiqueId) {
            $q->where('boutique_id', $boutiqueId);
        })
        ->orderBy('nom')
        ->get();

        return view('stock.historique', compact('mouvements', 'produits'));
    }

    /**
     * Alertes de stock
     */
    public function alertes()
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        $baseQuery = Produit::with('boutique');

        // Filtrer par boutique
        if ($user->isEmploye()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $baseQuery->where('boutique_id', $boutiqueId);
        }

        // Produits en rupture
        $rupture = (clone $baseQuery)->where('quantite_stock', 0)->get();

        // Produits en stock faible
        $stockFaible = (clone $baseQuery)->whereRaw('quantite_stock <= stock_minimum')
            ->where('quantite_stock', '>', 0)
            ->get();

        return view('stock.alertes', compact('rupture', 'stockFaible'));
    }
}
