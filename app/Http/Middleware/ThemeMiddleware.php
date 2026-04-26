<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Optimisation : Utiliser le cache pour éviter les requêtes répétées
        $boutiqueActiveId = session('boutique_active');
        $theme = 'default';
        $themeStyle = 'modern';
        $logo = null;
        $boutique = null;

        if ($boutiqueActiveId) {
            // Ne pas utiliser de cache pour le thème - toujours charger depuis la DB pour avoir les dernières valeurs
            $boutique = \App\Models\Boutique::select('id', 'nom', 'theme_color', 'theme_style', 'logo')
                ->find($boutiqueActiveId);
            
            // Si pas trouvé, essayer sans cache
            if (!$boutique) {
                $boutique = \App\Models\Boutique::select('id', 'nom', 'theme_color', 'theme_style', 'logo')
                    ->find($boutiqueActiveId);
            }

            if ($boutique) {
                $theme = $boutique->theme_color ?? 'default';
                $themeStyle = $boutique->theme_style ?? 'modern';
                $logo = $boutique->logo;
            }
        } else {
            // Si pas de boutique en session, utiliser la boutique de l'utilisateur
            $user = $request->user();
            if ($user && ($user->isOwner() || $user->isEmploye()) && $user->boutique_id) {
                // Charger avec eager loading si pas déjà chargé
                if (!$user->relationLoaded('boutique')) {
                    $user->load(['boutique' => function($query) {
                        $query->select('id', 'nom', 'theme_color', 'theme_style', 'logo');
                    }]);
                }

                $boutique = $user->boutique;
                if ($boutique) {
                    $theme = $boutique->theme_color ?? 'default';
                    $themeStyle = $boutique->theme_style ?? 'modern';
                    $logo = $boutique->logo;
                }
            }
        }

        // Partager le thème, le style et le logo avec toutes les vues
        view()->share('theme', $theme);
        view()->share('themeStyle', $themeStyle);
        view()->share('logo', $logo);
        view()->share('boutiqueActive', $boutique);

        return $next($request);
    }
}
