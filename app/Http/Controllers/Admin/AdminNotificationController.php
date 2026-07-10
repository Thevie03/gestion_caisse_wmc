<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Afficher toutes les notifications pour le Super Admin
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();

        // Pour le Super Admin, afficher toutes les notifications (globales + personnelles)
        $query = Notification::query()->pourAdmin($user->id);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        if ($request->filled('lue')) {
            $query->where('lue', $request->lue === 'true');
        }

        $notifications = $query->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiques
        $baseQuery = Notification::query()->pourAdmin($user->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'non_lues' => (clone $baseQuery)->where('lue', false)->count(),
            'par_type' => (clone $baseQuery)->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'par_module' => (clone $baseQuery)->selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module')
                ->toArray(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Afficher une notification spécifique
     */
    public function show(Notification $notification)
    {
        // Vérifier que l'utilisateur a accès à cette notification
        $user = auth()->user();
        if ($notification->user_id && $notification->user_id !== $user->id) {
            // Si la notification n'est pas pour cet utilisateur, vérifier si c'est une notification globale
            if ($notification->user_id !== null) {
                abort(403, 'Accès non autorisé à cette notification.');
            }
        }

        // Marquer comme lue si ce n'est pas déjà fait
        if (!$notification->lue) {
            $notification->marquerCommeLue();
        }

        // Si c'est une notification de support, rediriger directement vers le ticket
        if ($notification->module === 'support' && isset($notification->data['ticket_id'])) {
            if ($user->isAdmin() && !$user->isOwner()) {
                return redirect()->route('admin.support.show', $notification->data['ticket_id']);
            }
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification)
    {
        $user = auth()->user();
        // Pour le Super Admin, autoriser les notifications globales (user_id = null) ou personnelles
        if ($notification->user_id && $notification->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $notification->marquerCommeLue();

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = auth()->user();

        Notification::where('lue', false)
            ->pourAdmin($user->id)
            ->update([
                'lue' => true,
                'lue_at' => now()
            ]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification)
    {
        $user = auth()->user();
        // Pour le Super Admin, autoriser les notifications globales (user_id = null) ou personnelles
        if ($notification->user_id && $notification->user_id !== $user->id) {
            abort(403, 'Accès non autorisé à cette notification.');
        }

        $notification->delete();

        return back()->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function deleteAllRead()
    {
        $user = auth()->user();
        Notification::where('lue', true)
            ->pourAdmin($user->id)
            ->delete();

        return back()->with('success', 'Toutes les notifications lues ont été supprimées.');
    }

    /**
     * Compter les notifications non lues (API)
     */
    public function count()
    {
        $user = auth()->user();
        // Pour le Super Admin, compter uniquement les notifications globales (user_id = null)
        $count = Notification::whereNull('user_id')
            ->where('lue', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
