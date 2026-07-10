<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Désactiver temporairement le scope global pour voir tous les clients de la boutique
        $query = Client::withoutGlobalScopes();

        // Filtrer par boutique
        // Les employés voient uniquement les clients de leur boutique
        // Les propriétaires voient les clients de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        } else {
            // Si pas de boutique active, ne rien afficher
            $query->whereRaw('1 = 0');
        }

        // Filtres
        if ($request->filled('search')) {
            $query->recherche($request->search);
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $clients = $query->orderBy('prenom')->orderBy('nom')->paginate(20);

        // Statistiques (filtrées par boutique)
        $statsQuery = Client::withoutGlobalScopes();
        if ($user->isEmploye()) {
            $statsQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $statsQuery->where('boutique_id', $boutiqueId);
        } else {
            $statsQuery->whereRaw('1 = 0');
        }

        $stats = [
            'total_clients' => (clone $statsQuery)->count(),
            'clients_actifs' => (clone $statsQuery)->where('actif', true)->count(),
            'clients_inactifs' => (clone $statsQuery)->where('actif', false)->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'pays' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Déterminer la boutique_id
        // Les employés utilisent toujours leur boutique_id
        // Les propriétaires utilisent la boutique active de la session
        if ($user->isEmploye()) {
            $boutiqueId = $user->boutique_id;
        } elseif (!$boutiqueId && $user->isOwner()) {
            // Fallback pour les propriétaires sans session
            $boutiqueId = $user->boutique_id;
        }

        $clientData = $request->only([
            'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville',
            'code_postal', 'pays', 'date_naissance', 'sexe', 'notes'
        ]);

        // S'assurer que actif est défini (true par défaut)
        if (!isset($clientData['actif'])) {
            $clientData['actif'] = true;
        }

        // S'assurer que boutique_id est défini
        if ($boutiqueId && empty($clientData['boutique_id'])) {
            $clientData['boutique_id'] = $boutiqueId;
        }

        // S'assurer que user_id est défini
        if ($user && empty($clientData['user_id'])) {
            $clientData['user_id'] = $user->id;
        }

        Client::create($clientData);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        // Optimisation : Charger uniquement les ventes récentes avec eager loading
        $client->load(['ventes' => function($query) {
            $query->with(['venteDetails.produit', 'boutique'])->latest()->limit(10);
        }]);

        // Optimisation : Calculer les statistiques en une seule requête
        $statsData = $client->ventes()
            ->selectRaw('
                COUNT(*) as total_ventes,
                COALESCE(SUM(total_final), 0) as chiffre_affaires
            ')
            ->first();

        $stats = [
            'total_ventes' => $statsData->total_ventes ?? 0,
            'chiffre_affaires' => $statsData->chiffre_affaires ?? 0,
            'derniere_vente' => $client->ventes()->latest()->first(),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'pays' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'notes' => 'nullable|string',
            'actif' => 'boolean',
        ]);

        $client->update($request->only([
            'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville',
            'code_postal', 'pays', 'date_naissance', 'sexe', 'notes', 'actif'
        ]));

        return redirect()->route('clients.index')
            ->with('success', 'Client modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Récupérer le client sans le scope global pour éviter les problèmes de 404
        $client = Client::withoutGlobalScopes()->findOrFail($id);

        // Vérifier si le client a des ventes
        if ($client->ventes()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce client car il a des ventes associées.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès !');
    }

    /**
     * Toggle le statut actif/inactif
     */
    public function toggle(Client $client)
    {
        $client->update(['actif' => !$client->actif]);

        $status = $client->actif ? 'activé' : 'désactivé';
        return back()->with('success', "Client {$status} avec succès !");
    }
}
