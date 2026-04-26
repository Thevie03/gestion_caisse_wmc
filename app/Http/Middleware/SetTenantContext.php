<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;

class SetTenantContext
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $this->tenantContext->setFromUser($user);

        if ($user) {
            // Pour tous les utilisateurs (y compris les admins propriétaires),
            // définir la boutique active à partir de la session
            // Cela permet aux admins avec plusieurs boutiques de switcher entre elles
            // et de voir uniquement les données de la boutique sélectionnée

            $activeBoutique = session('boutique_active');

            // Si aucune boutique active en session :
            // - Pour les employés : utiliser leur boutique_id
            // - Pour les propriétaires/admins : laisser null (sera géré par BoutiqueMiddleware)
            if (!$activeBoutique && !$user->isAdmin() && $user->boutique_id) {
                $activeBoutique = $user->boutique_id;
            }

            // Définir la boutique dans le contexte si elle existe
            // Même pour les admins, cela permet de filtrer les données par boutique
            if ($activeBoutique) {
                $this->tenantContext->setBoutiqueId($activeBoutique);
            }
        }

        return $next($request);
    }
}









