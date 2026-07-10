<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Validation centralisée des filtres de listes (recherche, dates, statuts…).
 */
class ListingFilterValidator
{
    public static function rules(array $extra = []): array
    {
        return array_merge([
            'search' => 'nullable|string|max:255',
            'recherche' => 'nullable|string|max:255',
            'statut' => 'nullable|in:actif,inactif',
            'actif' => 'nullable|in:0,1',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'categorie' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'module' => 'nullable|string|max:100',
            'lue' => 'nullable|in:true,false,0,1',
            'priorite' => 'nullable|in:basse,normale,haute,urgente',
            'mode_paiement' => 'nullable|in:especes,wave,orange_money,mtn_money,mobile_money,carte',
            'statut_paiement' => 'nullable|in:paye,partiel,impaye,en_attente,annule',
            'boutique_id' => 'nullable|integer|exists:boutiques,id',
            'produit_id' => 'nullable|integer|exists:produits,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'role' => 'nullable|in:admin,employe,super_admin',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'numero_vente' => 'nullable|string|max:100',
            'client' => 'nullable|string|max:255',
            'type_action' => 'nullable|string|max:100',
            'type_abonnement' => 'nullable|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive',
            'page' => 'nullable|integer|min:1',
        ], $extra);
    }

    public static function validate(Request $request, array $extra = []): array
    {
        return $request->validate(self::rules($extra));
    }
}
