<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoriqueModificationVente;
use App\Models\Vente;

class HistoriqueVenteController extends Controller
{
    /**
     * Afficher l'historique des modifications et suppressions de ventes (Admin ou propriétaire de boutique)
     */
    public function index(Request $request)
    {
        // Vérifier que seul un admin ou le propriétaire de la boutique peut voir l'historique
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isOwner()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et les propriétaires de boutique peuvent voir l\'historique.');
        }

        $query = HistoriqueModificationVente::with(['user', 'boutique', 'vente'])
            ->orderBy('created_at', 'desc');

        // Si c'est un propriétaire, filtrer par sa boutique
        if ($user->isOwner() && !$user->isAdmin()) {
            $query->where('boutique_id', $user->boutique_id);
        }

        // Filtres
        if ($request->filled('type_action')) {
            $query->where('type_action', $request->type_action);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('numero_vente')) {
            $query->where('numero_vente', 'like', '%' . $request->numero_vente . '%');
        }

        if ($request->filled('recherche')) {
            $search = trim($request->recherche);
            $query->where(function($subQuery) use ($search) {
                $subQuery->where('numero_vente', 'like', "%{$search}%")
                    ->orWhere('changements', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('boutique', function($boutiqueQuery) use ($search) {
                        $boutiqueQuery->where('nom', 'like', "%{$search}%");
                    });
            });
        }

        $historiques = $query->paginate(20);

        // Statistiques (filtrées par boutique si propriétaire)
        $statsQuery = HistoriqueModificationVente::query();
        if ($user->isOwner() && !$user->isAdmin()) {
            $statsQuery->where('boutique_id', $user->boutique_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'modifications' => (clone $statsQuery)->where('type_action', 'modification')->count(),
            'suppressions' => (clone $statsQuery)->where('type_action', 'suppression')->count(),
            'aujourd_hui' => (clone $statsQuery)->whereDate('created_at', today())->count(),
        ];

        // Liste des utilisateurs pour le filtre (filtrée par boutique si propriétaire)
        $usersQuery = \App\Models\User::whereIn('id', $query->distinct()->pluck('user_id'));
        if ($user->isOwner() && !$user->isAdmin()) {
            $usersQuery->where('boutique_id', $user->boutique_id);
        }
        $users = $usersQuery->orderBy('name')->get();

        return view('historique.ventes.index', compact('historiques', 'stats', 'users'));
    }

    /**
     * Afficher les détails d'une entrée d'historique
     */
    public function show(HistoriqueModificationVente $historique)
    {
        // Vérifier que seul un admin ou le propriétaire de la boutique peut voir l'historique
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isOwner()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et les propriétaires de boutique peuvent voir l\'historique.');
        }

        // Si c'est un propriétaire, vérifier que l'historique appartient à une de ses boutiques
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($historique->boutique_id)) {
            abort(403, 'Vous ne pouvez voir que l\'historique de vos boutiques.');
        }

        $historique->load(['user', 'boutique', 'vente']);

        return view('historique.ventes.show', compact('historique'));
    }
}
