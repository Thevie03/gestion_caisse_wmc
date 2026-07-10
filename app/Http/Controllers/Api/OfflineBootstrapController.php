<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use App\Models\Category;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\ParametreSysteme;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de bootstrap pour le mode hors connexion (PWA).
 *
 * Télécharge l'ensemble des données nécessaires à la caisse
 * pour les stocker localement dans IndexedDB.
 */
class OfflineBootstrapController extends Controller
{
    /** Paramètres système autorisés en mode hors connexion (aucun secret). */
    private const SAFE_PARAMETRE_KEYS = [
        'app_name',
        'devise_defaut',
        'ticket_footer',
        'ticket_message',
        'pos_message',
    ];

    /**
     * Télécharger toutes les données de la boutique active.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $boutiqueId = $this->resolveBoutiqueId($user);

        if (!$boutiqueId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune boutique active. Sélectionnez une boutique avant la synchronisation.',
            ], 422);
        }

        $boutique = Boutique::find($boutiqueId);

        if (!$boutique) {
            return response()->json([
                'success' => false,
                'message' => 'Boutique introuvable.',
            ], 404);
        }

        $produits = Produit::withoutGlobalScopes()
            ->where('boutique_id', $boutiqueId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get([
                'id',
                'nom',
                'categorie',
                'prix_vente',
                'quantite_stock',
                'stock_minimum',
                'code_produit',
                'barcode',
                'image',
                'boutique_id',
                'fournisseur_id',
                'actif',
                'updated_at',
            ]);

        $categories = Category::withoutGlobalScopes()
            ->where('boutique_id', $boutiqueId)
            ->where('active', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'icone', 'couleur', 'description', 'active', 'boutique_id', 'updated_at']);

        $clients = Client::withoutGlobalScopes()
            ->where('boutique_id', $boutiqueId)
            ->where('actif', true)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get([
                'id',
                'nom',
                'prenom',
                'email',
                'telephone',
                'adresse',
                'ville',
                'solde_points',
                'actif',
                'boutique_id',
                'updated_at',
            ])
            ->map(function (Client $client) {
                return [
                    'id' => $client->id,
                    'nom' => $client->nom,
                    'prenom' => $client->prenom,
                    'nom_complet' => $client->nom_complet,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'adresse' => $client->adresse,
                    'ville' => $client->ville,
                    'solde_points' => $client->solde_points,
                    'actif' => $client->actif,
                    'boutique_id' => $client->boutique_id,
                    'updated_at' => $client->updated_at?->toIso8601String(),
                ];
            });

        $fournisseurs = Fournisseur::withoutGlobalScopes()
            ->where('boutique_id', $boutiqueId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'contact_nom', 'telephone', 'actif', 'boutique_id', 'updated_at']);

        $utilisateurs = User::query()
            ->where('actif', true)
            ->where(function ($query) use ($boutiqueId) {
                $query->where('boutique_id', $boutiqueId)
                    ->orWhereHas('ownedBoutiques', fn ($q) => $q->where('boutiques.id', $boutiqueId));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'boutique_id', 'actif'])
            ->map(function (User $u) use ($user) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->role,
                    'boutique_id' => $u->boutique_id,
                    'actif' => $u->actif,
                    'is_current' => $u->id === $user->id,
                ];
            });

        $parametresSysteme = ParametreSysteme::query()
            ->whereIn('cle', self::SAFE_PARAMETRE_KEYS)
            ->get(['cle', 'valeur', 'type'])
            ->mapWithKeys(function (ParametreSysteme $param) {
                return [$param->cle => $this->castParametreValue($param)];
            });

        $permissions = $user->permissions()
            ->select('nom', 'module', 'action')
            ->get()
            ->map(fn ($p) => [
                'nom' => $p->nom,
                'module' => $p->module,
                'action' => $p->action,
            ]);

        return response()->json([
            'success' => true,
            'meta' => [
                'boutique_id' => $boutiqueId,
                'synced_at' => now()->toIso8601String(),
                'version' => 1,
                'counts' => [
                    'produits' => $produits->count(),
                    'categories' => $categories->count(),
                    'clients' => $clients->count(),
                    'fournisseurs' => $fournisseurs->count(),
                    'utilisateurs' => $utilisateurs->count(),
                ],
            ],
            'boutique' => [
                'id' => $boutique->id,
                'nom' => $boutique->nom,
                'devise' => $boutique->devise,
                'theme_color' => $boutique->theme_color,
                'theme_style' => $boutique->theme_style,
                'ticket_width' => $boutique->ticket_width,
                'logo' => $boutique->logo,
                'pos_banner_image' => $boutique->pos_banner_image,
                'pos_stock_image' => $boutique->pos_stock_image,
                'pos_payment_image' => $boutique->pos_payment_image,
            ],
            'produits' => $produits->map(fn (Produit $p) => [
                'id' => $p->id,
                'nom' => $p->nom,
                'categorie' => $p->categorie,
                'prix_vente' => (float) $p->prix_vente,
                'quantite_stock' => (int) $p->quantite_stock,
                'stock_minimum' => (int) $p->stock_minimum,
                'code_produit' => $p->code_produit,
                'barcode' => $p->barcode,
                'image' => $p->image,
                'boutique_id' => $p->boutique_id,
                'fournisseur_id' => $p->fournisseur_id,
                'actif' => (bool) $p->actif,
                'updated_at' => $p->updated_at?->toIso8601String(),
            ]),
            'categories' => $categories,
            'clients' => $clients,
            'fournisseurs' => $fournisseurs,
            'utilisateurs' => $utilisateurs,
            'parametres' => $parametresSysteme,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'boutique_id' => $user->boutique_id,
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * Vérifier que le serveur est accessible (ping léger).
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'online' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Résoudre l'identifiant de la boutique active (même logique que le POS).
     */
    private function resolveBoutiqueId(User $user): ?int
    {
        if ($user->isEmploye()) {
            return $user->boutique_id ? (int) $user->boutique_id : null;
        }

        $sessionId = session('boutique_active');

        if ($sessionId) {
            return (int) $sessionId;
        }

        return $user->boutique_id ? (int) $user->boutique_id : null;
    }

    /**
     * Caster la valeur d'un paramètre système selon son type.
     */
    private function castParametreValue(ParametreSysteme $parametre): mixed
    {
        return match ($parametre->type) {
            'integer' => (int) $parametre->valeur,
            'boolean' => (bool) $parametre->valeur,
            'json' => json_decode($parametre->valeur, true),
            default => $parametre->valeur,
        };
    }
}
