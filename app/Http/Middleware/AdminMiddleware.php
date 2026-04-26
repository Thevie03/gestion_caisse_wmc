<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Charger la relation boutique si nécessaire pour vérifier isOwner()
        // S'assurer de charger owner_id pour la vérification
        if ($user->boutique_id && !$user->relationLoaded('boutique')) {
            $user->load(['boutique' => function($query) {
                $query->select('id', 'nom', 'owner_id', 'actif', 'devise', 'theme_color', 'logo');
            }]);
        }

        // IMPORTANT : Seul le super admin unique peut accéder aux routes admin.*
        // Les propriétaires de boutique (même avec rôle 'admin') peuvent accéder aux autres routes
        // mais pas aux routes admin.*

        // Si la route est une route admin.*, seul le super admin peut y accéder
        if ($request->routeIs('admin.*')) {
            if (!$user->isSuperAdmin()) {
                // Si c'est un propriétaire de boutique, rediriger vers son dashboard de boutique
                if ($user->isOwner()) {
                    // Ne pas écraser la session si elle existe déjà
                    if ($user->boutique_id && !session('boutique_active')) {
                        session(['boutique_active' => $user->boutique_id]);
                    }
                    return redirect()->route('dashboard')->with('error', 'Accès non autorisé. Vous devez accéder à votre dashboard de boutique.');
                }
                // Pour tous les autres (employés ou admins non super admin), refuser l'accès
                abort(403, 'Accès non autorisé. Seul le super administrateur peut accéder à cette page.');
            }
        } else {
            // Pour les routes non admin.*, autoriser les propriétaires de boutique et les employés
            // (ces routes sont gérées par d'autres middlewares comme 'boutique')
            if (!$user->isOwner() && !$user->isEmploye() && !$user->isSuperAdmin()) {
                abort(403, 'Accès non autorisé.');
            }
        }

        return $next($request);
    }
}
