<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        $query = Fournisseur::query();

        // Filtrer par boutique
        // Les employés voient uniquement les fournisseurs de leur boutique
        // Les propriétaires voient les fournisseurs de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Filtres
        if ($request->filled('search')) {
            $query->recherche($request->search);
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $fournisseurs = $query->orderBy('nom')->paginate(20);

        // Statistiques (filtrées par boutique)
        $statsQuery = Fournisseur::query();
        if ($user->isEmploye()) {
            $statsQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $statsQuery->where('boutique_id', $boutiqueId);
        }

        $stats = [
            'total_fournisseurs' => (clone $statsQuery)->count(),
            'fournisseurs_actifs' => (clone $statsQuery)->where('actif', true)->count(),
            'fournisseurs_inactifs' => (clone $statsQuery)->where('actif', false)->count(),
        ];

        return view('fournisseurs.index', compact('fournisseurs', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fournisseurs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:fournisseurs,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'pays' => 'nullable|string|max:100',
            'site_web' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        Fournisseur::create($request->only([
            'nom', 'contact_nom', 'email', 'telephone', 'adresse', 'ville',
            'code_postal', 'pays', 'site_web', 'notes',
        ]));

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load(['produits' => function($query) {
            $query->with(['category'])->latest()->limit(10);
        }]);

        // Statistiques du fournisseur
        $stats = [
            'total_produits' => $fournisseur->produits()->count(),
            'produits_actifs' => $fournisseur->produits()->where('actif', true)->count(),
            'stock_total' => $fournisseur->produits()->sum('quantite_stock'),
            'valeur_stock' => $fournisseur->produits()->sum(DB::raw('quantite_stock * prix_achat')),
        ];

        return view('fournisseurs.show', compact('fournisseur', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'pays' => 'nullable|string|max:100',
            'site_web' => 'nullable|url',
            'notes' => 'nullable|string',
            'actif' => 'boolean',
        ]);

        $fournisseur->update($request->only([
            'nom', 'contact_nom', 'email', 'telephone', 'adresse', 'ville',
            'code_postal', 'pays', 'site_web', 'notes', 'actif',
        ]));

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Récupérer le fournisseur sans le scope global pour éviter les problèmes de 404
        $fournisseur = Fournisseur::withoutGlobalScopes()->findOrFail($id);

        // Vérifier si le fournisseur a des produits
        if ($fournisseur->produits()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce fournisseur car il a des produits associés.');
        }

        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur supprimé avec succès !');
    }

    /**
     * Toggle le statut actif/inactif
     */
    public function toggle(Fournisseur $fournisseur)
    {
        $fournisseur->update(['actif' => !$fournisseur->actif]);

        $status = $fournisseur->actif ? 'activé' : 'désactivé';
        return back()->with('success', "Fournisseur {$status} avec succès !");
    }
}
