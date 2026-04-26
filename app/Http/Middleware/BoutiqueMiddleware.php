<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BoutiqueMiddleware
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

        // Si c'est un utilisateur normal (USER/employe), vérifier qu'il a une boutique assignée
        if ($user->isEmploye() && !$user->boutique_id) {
            abort(403, 'Aucune boutique assignée. Contactez l\'administrateur.');
        }

        // Si c'est un utilisateur normal avec une boutique, définir automatiquement la session
        if ($user->isEmploye() && $user->boutique_id && !session('boutique_active')) {
            session(['boutique_active' => $user->boutique_id]);
        }

        // Si c'est un propriétaire de boutique, gérer la sélection de boutique
        if ($user->isOwner()) {
            // Optimisation : Utiliser le cache pour éviter les requêtes répétées
            $cacheKey = 'user_owned_boutiques_' . $user->id;
            $ownedBoutiques = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user) {
                $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
                // Si aucune boutique via many-to-many, utiliser l'ancien système (rétrocompatibilité)
                if ($boutiques->isEmpty() && $user->boutique_id) {
                    $boutiques = collect([$user->boutique]);
                }
                return $boutiques;
            });

            // PRIORITÉ 1 : Si une boutique est déjà en session, vérifier qu'elle appartient au propriétaire
            // Si elle est valide, la CONSERVER et ne rien faire d'autre
            if (session('boutique_active')) {
                $activeBoutiqueId = session('boutique_active');
                if ($user->ownsBoutique($activeBoutiqueId)) {
                    // La boutique en session est valide, la conserver - NE RIEN FAIRE
                    return $next($request);
                } else {
                    // La boutique active n'appartient pas au propriétaire, la supprimer de la session
                    session()->forget('boutique_active');
                    // Rediriger vers la sélection si ce n'est pas déjà la page de sélection
                    if (!$request->routeIs('boutiques.*')) {
                        return redirect()->route('boutiques.index');
                    }
                }
            }

            // PRIORITÉ 2 : Si aucune boutique en session, gérer la sélection
            if (!session('boutique_active')) {
                if ($ownedBoutiques->count() > 1) {
                    // Si plusieurs boutiques et aucune en session, rediriger vers la sélection
                    if (!$request->routeIs('boutiques.*')) {
                        return redirect()->route('boutiques.index');
                    }
                } elseif ($ownedBoutiques->count() == 1) {
                    // Si une seule boutique et aucune en session, la définir automatiquement
                    session(['boutique_active' => $ownedBoutiques->first()->id]);
                }
            }
        }

        // Si c'est le super admin unique (email super admin)
        // Seul cet utilisateur peut accéder au dashboard admin
        if ($user->isSuperAdmin()) {
            // Si le super admin accède à une route admin, laisser passer sans vérifier la boutique
            if ($request->routeIs('admin.*')) {
                return $next($request);
            }

            // Pour les autres routes, vérifier qu'il a sélectionné une boutique
            if (!session('boutique_active')) {
                // Si le super admin n'a pas de boutique active, rediriger vers la sélection
                return redirect()->route('boutiques.index');
            }
        }

        return $next($request);
    }
}
