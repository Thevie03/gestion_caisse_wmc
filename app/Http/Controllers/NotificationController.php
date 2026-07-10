<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Afficher les notifications
     * Pour les boutiques/clients : uniquement leurs notifications personnelles
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Pour les boutiques/clients, afficher UNIQUEMENT leurs notifications personnelles
        // Filtrées par la boutique active pour les propriétaires
        // (pas les notifications globales du Super Admin)
        $query = Notification::where('user_id', $user->id);

        // Filtrer par boutique active pour les propriétaires
        // Les employés voient uniquement les notifications de leur boutique
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            // Pour les propriétaires, filtrer par la boutique active
            $query->where('boutique_id', $boutiqueId);
        }

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('lue')) {
            $query->where('lue', $request->lue === 'true');
        }

        $notifications = $query->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiques (avec le même filtre de boutique)
        $baseQuery = Notification::where('user_id', $user->id);
        if ($user->isEmploye()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $baseQuery->where('boutique_id', $boutiqueId);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'non_lues' => (clone $baseQuery)->where('lue', false)->count(),
            'par_type' => (clone $baseQuery)->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Afficher une notification spécifique
     */
    public function show(Notification $notification)
    {
        // Marquer comme lue si ce n'est pas déjà fait
        if (!$notification->lue) {
            $notification->marquerCommeLue();
        }

        // Si c'est une notification de support, rediriger directement vers le ticket
        if ($notification->module === 'support' && isset($notification->data['ticket_id'])) {
            $user = auth()->user();
            if ($user->isOwner()) {
                return redirect()->route('tickets.show', $notification->data['ticket_id']);
            }
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerLue(Request $request, $id)
    {
        // Autoriser seulement le propriétaire ou les notifications globales
        $user = $request->user();

        // Récupérer la notification sans le scope global pour éviter les problèmes de filtrage
        // Le model binding avec le scope global peut bloquer la résolution
        $notification = Notification::withoutGlobalScopes()->findOrFail($id);

        if ($notification->user_id && $notification->user_id !== $user->id) {
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé à cette notification.');
        }

        // Vérifier que la notification appartient à la bonne boutique pour les propriétaires
        $boutiqueId = session('boutique_active');
        if ($user->isEmploye() && $notification->boutique_id != $user->boutique_id) {
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé à cette notification.');
        } elseif ($user->isOwner() && $boutiqueId && $notification->boutique_id != $boutiqueId) {
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé à cette notification.');
        }

        try {
            // Mettre à jour directement en base de données pour éviter les problèmes de scope
            $updated = DB::table('notifications')
                ->where('id', $notification->id)
                ->update([
                    'lue' => true,
                    'lue_at' => now()
                ]);

            if ($updated === 0) {
                // Aucune ligne mise à jour
                if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Notification introuvable'], 404);
                }
                return back()->with('error', 'Notification introuvable.');
            }

            // Toujours retourner du JSON pour les requêtes AJAX
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Notification marquée comme lue.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la mise à jour de la notification', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
            }

            return back()->with('error', 'Erreur lors de la mise à jour de la notification.');
        }
    }

    /**
     * Compter les notifications non lues (API)
     */
    public function count()
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active', 'all');
        $cacheKey = "notifications_count_{$user->id}_{$boutiqueId}";
        $count = Cache::remember($cacheKey, now()->addSeconds(15), function () use ($user) {
            return NotificationService::compterNonLues($user->id);
        });

        return response()->json(['count' => $count]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesLues()
    {
        $user = auth()->user();
        NotificationService::marquerToutesCommeLues($user->id);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification)
    {
        return back()->with('warning', 'La suppression des notifications est désactivée.');
    }

    /**
     * API pour obtenir les notifications (AJAX)
     */
    public function api(Request $request)
    {
        $user = auth()->user();
        $limite = $request->get('limite', 10);

        $notifications = NotificationService::getNotifications($user->id, $limite);
        $nonLues = NotificationService::compterNonLues($user->id);

        return response()->json([
            'notifications' => $notifications,
            'non_lues' => $nonLues
        ]);
    }

    /**
     * Vérifier les alertes de stock
     */
    public function verifierStock()
    {
        NotificationService::verifierAlertesStock();

        return back()->with('success', 'Vérification des alertes de stock effectuée.');
    }
}
