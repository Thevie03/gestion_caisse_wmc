<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class TicketController extends Controller
{
    /**
     * Afficher la liste des tickets de la boutique
     */
    public function index()
    {
        $user = Auth::user();

        // Seuls les propriétaires de boutique (pas les super admins) peuvent accéder
        if (!$user->isOwner() || $user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $tickets = Ticket::where('boutique_id', $user->boutique_id)
            ->with(['user', 'repondPar'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => Ticket::where('boutique_id', $user->boutique_id)->count(),
            'ouverts' => Ticket::where('boutique_id', $user->boutique_id)->where('statut', 'ouvert')->count(),
            'en_cours' => Ticket::where('boutique_id', $user->boutique_id)->where('statut', 'en_cours')->count(),
            'resolus' => Ticket::where('boutique_id', $user->boutique_id)->where('statut', 'resolu')->count(),
        ];

        return view('tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Afficher le formulaire de création d'un ticket
     */
    public function create()
    {
        $user = Auth::user();

        // Seuls les propriétaires de boutique (pas les super admins) peuvent accéder
        if (!$user->isOwner() || $user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('tickets.create');
    }

    /**
     * Enregistrer un nouveau ticket
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Seuls les propriétaires de boutique (pas les super admins) peuvent créer un ticket
        if (!$user->isOwner() || $user->isSuperAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'sujet' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'priorite' => 'required|in:faible,normale,elevee,urgente',
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'boutique_id' => $user->boutique_id,
            'sujet' => $request->sujet,
            'message' => $request->message,
            'priorite' => $request->priorite,
            'statut' => Ticket::STATUT_OUVERT,
        ]);

        // Notifier le super admin qu'un nouveau ticket a été créé
        NotificationService::notifierNouveauTicket($ticket);

        return redirect()->route('tickets.index')
            ->with('success', 'Votre demande d\'assistance a été envoyée avec succès. Nous vous répondrons dans les plus brefs délais.');
    }

    /**
     * Afficher les détails d'un ticket
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        // Vérifier que le ticket appartient à une des boutiques de l'utilisateur
        // Seuls les propriétaires de boutique (pas les super admins) peuvent accéder
        if (!$user->isOwner() || $user->isSuperAdmin() || !$user->ownsBoutique($ticket->boutique_id)) {
            abort(403, 'Accès non autorisé.');
        }

        $ticket->load(['user', 'boutique', 'repondPar']);

        return view('tickets.show', compact('ticket'));
    }
}
