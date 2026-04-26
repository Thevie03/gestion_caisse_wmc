<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des code-barres
 *
 * Ce contrôleur gère la recherche de produits par code-barres
 * pour l'interface de caisse (POS - Point of Sale)
 */
class BarcodeController extends Controller
{
    /**
     * Rechercher un produit par son code-barres
     *
     * Cette méthode est utilisée par l'interface de caisse pour
     * trouver automatiquement un produit lorsqu'un code-barres est scanné.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request)
    {
        // Valider que le paramètre barcode est présent
        $request->validate([
            'barcode' => 'required|string|max:50'
        ]);

        $user = Auth::user();
        $boutiqueId = session('boutique_active');

        // Construire la requête de recherche
        // Ne pas filtrer par stock car le stock est maintenant optionnel
        // pour permettre la flexibilité selon le type de boutique
        $query = Produit::where('barcode', $request->barcode)
            ->where('actif', true);

        // Filtrer par boutique selon les permissions de l'utilisateur
        // Les employés voient uniquement les produits de leur boutique
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        }
        // Les propriétaires voient les produits de la boutique active de la session
        elseif ($user->isOwner() && $boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }
        // Pour les autres utilisateurs (admins), utiliser la boutique active de la session
        elseif ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        // Rechercher le produit
        $produit = $query->first();

        // Si le produit est trouvé, retourner les informations nécessaires
        if ($produit) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'prix_vente' => $produit->prix_vente,
                    'quantite_stock' => $produit->quantite_stock,
                    'categorie' => $produit->categorie,
                    'barcode' => $produit->barcode
                ]
            ]);
        }

        // Si le produit n'est pas trouvé
        return response()->json([
            'success' => false,
            'message' => 'Produit introuvable avec ce code-barres'
        ], 404);
    }
}
