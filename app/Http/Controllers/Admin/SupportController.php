<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class SupportController extends Controller
{
    /**
     * Afficher la liste de tous les tickets (super admin uniquement)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Seuls les super admins peuvent accéder (pas les propriétaires de boutique)
        if (!$user->isAdmin() || $user->isOwner()) {
            abort(403, 'Accès non autorisé.');
        }

        $query = Ticket::with(['user', 'boutique', 'repondPar']);

        // Filtres
        if ($request->filled('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sujet', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => Ticket::count(),
            'ouverts' => Ticket::where('statut', 'ouvert')->count(),
            'en_cours' => Ticket::where('statut', 'en_cours')->count(),
            'resolus' => Ticket::where('statut', 'resolu')->count(),
            'urgents' => Ticket::where('priorite', 'urgente')->whereIn('statut', ['ouvert', 'en_cours'])->count(),
        ];

        // Liste des boutiques pour le filtre
        $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();

        return view('admin.support.index', compact('tickets', 'stats', 'boutiques'));
    }

    /**
     * Afficher les détails d'un ticket
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        // Seuls les super admins peuvent accéder (pas les propriétaires de boutique)
        if (!$user->isAdmin() || $user->isOwner()) {
            abort(403, 'Accès non autorisé.');
        }

        $ticket->load(['user', 'boutique', 'repondPar']);

        return view('admin.support.show', compact('ticket'));
    }

    /**
     * Répondre à un ticket
     */
    public function repondre(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // Seuls les super admins peuvent répondre
        if (!$user->isAdmin() || $user->isOwner()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'reponse' => 'required|string|min:10',
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
        ]);

        $ticket->update([
            'reponse' => $request->reponse,
            'statut' => $request->statut,
            'reponse_at' => now(),
            'reponse_par' => $user->id,
        ]);

        // Notifier le propriétaire de la boutique qu'une réponse a été donnée
        NotificationService::notifierReponseTicket($ticket);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Réponse envoyée avec succès.');
    }

    /**
     * Changer le statut d'un ticket
     */
    public function updateStatut(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // Seuls les super admins peuvent modifier le statut
        if (!$user->isAdmin() || $user->isOwner()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
        ]);

        $ticket->update([
            'statut' => $request->statut,
        ]);

        return redirect()->back()
            ->with('success', 'Statut mis à jour avec succès.');
    }
}
