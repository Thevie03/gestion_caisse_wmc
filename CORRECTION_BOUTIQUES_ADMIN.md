# Correction : Filtrage des Boutiques pour les Admins

## 🔴 Problème Identifié

Dans le formulaire modal d'ajout de produit du POS, **tous les admins voyaient toutes les boutiques** au lieu de voir uniquement leurs boutiques assignées.

**Code problématique :**

```php
} else {
    $boutiques = $boutiquesQuery->get(); // ❌ Récupère TOUTES les boutiques
}
```

## ✅ Solution Appliquée

La logique a été corrigée dans `VenteController::pos()` pour respecter la hiérarchie :

1. **Employé** → Voir uniquement sa boutique assignée (`boutique_id`)
2. **Propriétaire (isOwner)** → Voir toutes ses boutiques possédées (via `ownedBoutiques()`)
3. **Super Admin** → Voir toutes les boutiques (seul cas où toutes sont visibles)
4. **Admin normal (isAdmin mais pas super admin)** → Voir uniquement ses boutiques assignées :
    - Boutiques via `ownedBoutiques()` (relation many-to-many)
    - Boutique assignée via `boutique_id` (si elle existe)
5. **Autres** → Aucune boutique

## 📝 Code Corrigé

```php
// Récupérer les boutiques disponibles pour le formulaire modal
$boutiquesQuery = Boutique::where('actif', true)->orderBy('nom');
if ($user->isEmploye()) {
    // Les employés voient uniquement leur boutique
    $boutiques = $boutiquesQuery->where('id', $user->boutique_id)->get();
} elseif ($user->isOwner()) {
    // Les propriétaires voient toutes leurs boutiques
    $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
    $boutiques = $ownedBoutiques->isEmpty() && $user->boutique_id
        ? collect([$user->boutique])
        : $ownedBoutiques;
} elseif ($user->isSuperAdmin()) {
    // Seul le super admin voit toutes les boutiques
    $boutiques = $boutiquesQuery->get();
} elseif ($user->isAdmin()) {
    // Les autres admins voient uniquement leurs boutiques assignées
    $boutiques = collect();

    // Ajouter les boutiques via la relation many-to-many (ownedBoutiques)
    $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
    if ($ownedBoutiques->isNotEmpty()) {
        $boutiques = $boutiques->merge($ownedBoutiques);
    }

    // Ajouter la boutique assignée via boutique_id si elle existe
    if ($user->boutique_id) {
        $boutiqueAssignee = Boutique::where('id', $user->boutique_id)
            ->where('actif', true)
            ->first();
        if ($boutiqueAssignee && !$boutiques->contains('id', $user->boutique_id)) {
            $boutiques->push($boutiqueAssignee);
        }
    }
} else {
    // Pour les autres utilisateurs, aucune boutique
    $boutiques = collect();
}
```

## 🔍 Vérifications

-   ✅ Les admins normaux ne voient plus que leurs boutiques assignées
-   ✅ Le super admin voit toujours toutes les boutiques
-   ✅ Les propriétaires voient toutes leurs boutiques
-   ✅ Les employés voient uniquement leur boutique

## 📌 Fichier Modifié

-   `app/Http/Controllers/VenteController.php` - Méthode `pos()` (lignes 843-874)

