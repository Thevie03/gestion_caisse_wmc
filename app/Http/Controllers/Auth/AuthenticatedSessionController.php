<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // Enregistrer la dernière connexion
        $user->update(['last_login_at' => now()]);

        // Redirection selon le rôle
        // IMPORTANT : Vérifier d'abord si c'est un propriétaire de boutique
        // Un propriétaire de boutique (même avec rôle 'admin') doit TOUJOURS voir son dashboard de boutique
        // Seul le super admin unique doit voir le dashboard admin

        // Recharger l'utilisateur avec la relation boutique pour s'assurer que tout est à jour
        $user->refresh();

        // Charger la relation boutique avec owner_id pour vérifier isOwner()
        if ($user->boutique_id) {
            if (!$user->relationLoaded('boutique')) {
                $user->load(['boutique' => function($query) {
                    $query->select('id', 'nom', 'owner_id', 'actif', 'devise', 'theme_color', 'logo');
                }]);
            }
        }

        // PRIORITÉ 1 : Vérifier si c'est un propriétaire de boutique
        // Un propriétaire de boutique (même avec rôle 'admin') doit TOUJOURS voir son dashboard de boutique
        // Cette vérification doit être faite EN PREMIER pour éviter qu'un propriétaire soit traité comme super admin
        if ($user->isOwner()) {
            // Ne pas écraser la session si elle existe déjà (pour préserver la boutique sélectionnée)
            // Seulement définir si aucune boutique n'est en session
            if (!session('boutique_active')) {
                $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
                if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                    $ownedBoutiques = collect([$user->boutique]);
                }

                if ($ownedBoutiques->count() == 1) {
                    // Si une seule boutique, la définir automatiquement
                    session(['boutique_active' => $ownedBoutiques->first()->id]);
                } elseif ($ownedBoutiques->count() > 1) {
                    // Si plusieurs boutiques, rediriger vers la sélection
                    return redirect()->route('boutiques.index');
                } elseif ($user->boutique_id) {
                    // Fallback : utiliser la boutique_id de l'utilisateur
                    session(['boutique_active' => $user->boutique_id]);
                }
            }
            return redirect()->route('dashboard');
        }

        // PRIORITÉ 2 : Vérifier si c'est le super admin unique
        // Dans ce projet, on utilise le dashboard standard pour éviter
        // l'interface admin legacy (sidebar différente).
        if ($user->isSuperAdmin()) {
            return redirect()->route('dashboard');
        }

        // PRIORITÉ 3 : Pour tous les autres (employés ou autres admins avec boutique), rediriger vers le dashboard de boutique
        // Ne pas écraser la session si elle existe déjà
        if ($user->boutique_id && !session('boutique_active')) {
            session(['boutique_active' => $user->boutique_id]);
        }
        return redirect()->route('dashboard');

        // Si aucun cas ne correspond, rediriger vers le dashboard par défaut
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
