<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Depense;
use App\Models\Boutique;
use Illuminate\Support\Facades\DB;

class DepenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        $baseQuery = Depense::with(['boutique', 'user']);

        // Filtrer par boutique
        // Les employés voient uniquement les dépenses de leur boutique
        // Les propriétaires voient les dépenses de la boutique active (session)
        if ($user->isEmploye()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $baseQuery->where('boutique_id', $boutiqueId);
        }

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('categorie', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie')) {
            $baseQuery->where('categorie', $request->categorie);
        }

        if ($request->filled('date_debut')) {
            $baseQuery->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $baseQuery->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->filled('montant_min')) {
            $baseQuery->where('montant', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $baseQuery->where('montant', '<=', $request->montant_max);
        }

        $depenses = (clone $baseQuery)->orderBy('date_depense', 'desc')->paginate(20);

        // Statistiques
        $statsQuery = clone $baseQuery;
        $totalDepenses = (clone $statsQuery)->count();
        $montantTotal = (clone $statsQuery)->sum('montant');
        $moyenneMensuelle = (clone $statsQuery)->whereMonth('date_depense', now()->month)->sum('montant');
        $stats = [
            'total_depenses' => $totalDepenses,
            'montant_total' => $montantTotal,
            'moyenne_mensuelle' => $moyenneMensuelle,
            'categorie_principale' => (clone $statsQuery)->select('categorie', DB::raw('SUM(montant) as total'))
                ->groupBy('categorie')
                ->orderBy('total', 'desc')
                ->first()
        ];

        // Catégories pour le filtre
        $categories = Depense::select('categorie')
            ->when($user->isEmploye(), function($q) use ($user) {
                $q->where('boutique_id', $user->boutique_id);
            })
            ->when($boutiqueId && !$user->isEmploye(), function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            })
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return view('depenses.index', compact('depenses', 'stats', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer les boutiques disponibles
        // Les employés ne voient que leur boutique
        // Les propriétaires voient toutes leurs boutiques
        if ($user->isEmploye()) {
            $boutiques = collect([$user->boutique]);
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = collect([$user->boutique]);
            }
        } else {
            $boutiques = Boutique::where('actif', true)->get();
        }

        return view('depenses.create', compact('boutiques'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'categorie' => 'required|string|max:100',
            'montant' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'boutique_id' => 'required|exists:boutiques,id',
            'justificatif' => 'nullable|string|max:500'
        ]);

        $user = auth()->user();
        // Les employés utilisent toujours leur boutique
        // Les propriétaires peuvent utiliser la boutique de la session ou celle de la requête
        $boutiqueId = $user->isEmploye() ? $user->boutique_id : ($request->boutique_id ?? session('boutique_active'));

        // Vérifier l'accès à la boutique
        if ($user->isEmploye() && $boutiqueId != $user->boutique_id) {
            return back()->with('error', 'Vous ne pouvez pas enregistrer de dépense pour cette boutique.');
        }
        if ($user->isOwner() && $boutiqueId && !$user->ownsBoutique($boutiqueId)) {
            return back()->with('error', 'Vous ne pouvez pas enregistrer de dépense pour cette boutique.');
        }

        $depense = Depense::create([
            'description' => $request->description,
            'categorie' => $request->categorie,
            'montant' => $request->montant,
            'date_depense' => $request->date,
            'boutique_id' => $boutiqueId,
            'user_id' => $user->id,
            'notes' => $request->justificatif,
        ]);

        // Invalider le cache du dashboard pour afficher les nouvelles statistiques
        $this->invaliderCacheDashboard($boutiqueId, $user->id);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense enregistrée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Depense $depense)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Vérifier l'accès à la dépense
        // Les employés ne peuvent accéder qu'à leur boutique
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isEmploye() && $depense->boutique_id != $user->boutique_id) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isOwner() && !$user->ownsBoutique($depense->boutique_id)) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isAdmin() && $boutiqueId && $depense->boutique_id != $boutiqueId) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }

        return view('depenses.show', compact('depense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Depense $depense)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Vérifier l'accès à la dépense
        // Les employés ne peuvent accéder qu'à leur boutique
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isEmploye() && $depense->boutique_id != $user->boutique_id) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isOwner() && !$user->ownsBoutique($depense->boutique_id)) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isAdmin() && $boutiqueId && $depense->boutique_id != $boutiqueId) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }

        // Récupérer les boutiques disponibles
        // Les employés ne voient que leur boutique
        // Les propriétaires voient toutes leurs boutiques
        if ($user->isEmploye()) {
            $boutiques = collect([$user->boutique]);
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = collect([$user->boutique]);
            }
        } else {
            $boutiques = Boutique::where('actif', true)->get();
        }

        return view('depenses.edit', compact('depense', 'boutiques'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Depense $depense)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Vérifier l'accès à la dépense
        // Les employés ne peuvent accéder qu'à leur boutique
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isEmploye() && $depense->boutique_id != $user->boutique_id) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isOwner() && !$user->ownsBoutique($depense->boutique_id)) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isAdmin() && $boutiqueId && $depense->boutique_id != $boutiqueId) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'categorie' => 'required|string|max:100',
            'montant' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'boutique_id' => 'required|exists:boutiques,id',
            'justificatif' => 'nullable|string|max:500'
        ]);

        // Les employés utilisent toujours leur boutique
        // Les propriétaires peuvent utiliser la boutique de la session ou celle de la requête
        $newBoutiqueId = $user->isEmploye() ? $user->boutique_id : ($request->boutique_id ?? $boutiqueId);

        // Vérifier l'accès à la nouvelle boutique
        if ($user->isEmploye() && $newBoutiqueId != $user->boutique_id) {
            return back()->with('error', 'Vous ne pouvez pas modifier la boutique de cette dépense.');
        }
        if ($user->isOwner() && !$user->ownsBoutique($newBoutiqueId)) {
            return back()->with('error', 'Vous ne pouvez pas utiliser cette boutique.');
        }

        $oldBoutiqueId = $depense->boutique_id;

        $depense->update([
            'description' => $request->description,
            'categorie' => $request->categorie,
            'montant' => $request->montant,
            'date_depense' => $request->date,
            'boutique_id' => $newBoutiqueId,
            'notes' => $request->justificatif,
        ]);

        // Invalider le cache du dashboard pour les deux boutiques (ancienne et nouvelle)
        $this->invaliderCacheDashboard($oldBoutiqueId, $user->id);
        if ($newBoutiqueId != $oldBoutiqueId) {
            $this->invaliderCacheDashboard($newBoutiqueId, $user->id);
        }

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense modifiée avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer la dépense sans le scope global pour éviter les problèmes de 404
        $depense = Depense::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès à la dépense
        // Les employés ne peuvent accéder qu'à leur boutique
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isEmploye() && $depense->boutique_id != $user->boutique_id) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isOwner() && !$user->ownsBoutique($depense->boutique_id)) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }
        if ($user->isAdmin() && $boutiqueId && $depense->boutique_id != $boutiqueId) {
            abort(403, 'Accès non autorisé à cette dépense.');
        }

        $boutiqueIdDepense = $depense->boutique_id;
        $depense->delete();

        // Invalider le cache du dashboard pour afficher les nouvelles statistiques
        $this->invaliderCacheDashboard($boutiqueIdDepense, $user->id);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense supprimée avec succès !');
    }

    /**
     * Statistiques des dépenses
     */
    public function statistiques(Request $request)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        $query = Depense::query();

        // Filtrer par boutique
        // Les employés voient uniquement les dépenses de leur boutique
        // Les propriétaires voient les dépenses de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Période par défaut : dernier mois
        $dateDebut = $request->get('date_debut', now()->subMonth()->startOfMonth());
        $dateFin = $request->get('date_fin', now()->endOfMonth());

        $query->whereBetween('date_depense', [$dateDebut, $dateFin]);

        // Statistiques générales
        $statsQuery = clone $query;
        $totalDepenses = (clone $statsQuery)->count();
        $montantTotal = (clone $statsQuery)->sum('montant');
        $stats = [
            'total_depenses' => $totalDepenses,
            'montant_total' => $montantTotal,
            'moyenne_journaliere' => $montantTotal / max(1, $dateDebut->diffInDays($dateFin)),
            'depense_max' => (clone $statsQuery)->max('montant'),
            'depense_min' => (clone $statsQuery)->min('montant')
        ];

        // Dépenses par catégorie
        $depensesParCategorie = (clone $query)->select('categorie', DB::raw('SUM(montant) as total'), DB::raw('COUNT(*) as nombre'))
            ->groupBy('categorie')
            ->orderBy('total', 'desc')
            ->get();

        // Dépenses par jour
        $depensesParJour = (clone $query)->select(DB::raw('DATE(date_depense) as jour'), DB::raw('SUM(montant) as total'))
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();

        // Dépenses par boutique (pour les admins)
        $depensesParBoutique = null;
        if ($user->isAdmin()) {
            $depensesParBoutique = Depense::select('boutiques.nom', DB::raw('SUM(depenses.montant) as total'))
                ->join('boutiques', 'depenses.boutique_id', '=', 'boutiques.id')
                ->whereBetween('depenses.date_depense', [$dateDebut, $dateFin])
                ->groupBy('boutiques.id', 'boutiques.nom')
                ->orderBy('total', 'desc')
                ->get();
        }

        return view('depenses.statistiques', compact('stats', 'depensesParCategorie', 'depensesParJour', 'depensesParBoutique', 'dateDebut', 'dateFin'));
    }

    /**
     * Invalider le cache du dashboard après modification des dépenses
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
}
