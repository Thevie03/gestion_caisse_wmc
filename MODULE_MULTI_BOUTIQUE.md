# Module Multi-Boutique - Documentation

## Vue d'ensemble

Le module multi-boutique permet à un propriétaire de gérer plusieurs boutiques simultanément. Un propriétaire peut maintenant avoir 1 ou plusieurs boutiques qu'il peut sélectionner et gérer.

## Fonctionnalités implémentées

### 1. Base de données

#### Migration `create_boutique_user_table`

-   Table pivot `boutique_user` pour la relation many-to-many entre User et Boutique
-   Colonnes :
    -   `boutique_id` : ID de la boutique
    -   `user_id` : ID du propriétaire
    -   `is_primary` : Indique si c'est la boutique principale du propriétaire
    -   Index unique pour éviter les doublons

#### Migration `migrate_existing_owners_to_boutique_user_table`

-   Migre automatiquement les données existantes
-   Les boutiques avec `owner_id` sont automatiquement ajoutées à la table pivot
-   La première boutique d'un propriétaire est marquée comme principale

### 2. Modèles

#### User Model

-   **Nouvelle relation** : `ownedBoutiques()` - Relation many-to-many avec Boutique
-   **Nouvelle méthode** : `accessibleBoutiques()` - Retourne toutes les boutiques accessibles
-   **Nouvelle méthode** : `primaryBoutique()` - Retourne la boutique principale
-   **Nouvelle méthode** : `ownsBoutique($boutiqueId)` - Vérifie si l'utilisateur possède une boutique
-   **Mise à jour** : `isOwner()` - Vérifie maintenant via la relation many-to-many ET owner_id (rétrocompatibilité)

#### Boutique Model

-   **Nouvelle relation** : `owners()` - Relation many-to-many avec User
-   **Nouvelle méthode** : `primaryOwner()` - Retourne le propriétaire principal

### 3. Contrôleurs

#### BoutiqueController

-   **Mise à jour** : `index()` - Affiche maintenant toutes les boutiques d'un propriétaire
-   **Mise à jour** : `select()` - Vérifie que l'utilisateur a accès à la boutique sélectionnée
-   **Mise à jour** : `edit()` et `update()` - Utilisent `ownsBoutique()` pour la vérification

#### BoutiqueManagementController (Admin)

-   **Mise à jour** : `store()` - Assigne automatiquement la boutique via la table pivot lors de la création

### 4. Middleware

#### BoutiqueMiddleware

-   **Mise à jour** : Gère maintenant les propriétaires avec plusieurs boutiques
-   Si un propriétaire a plusieurs boutiques et qu'aucune n'est sélectionnée, redirection vers la sélection
-   Vérifie que la boutique active appartient bien au propriétaire

### 5. Vues

#### `boutiques/index.blade.php`

-   Affiche toutes les boutiques d'un propriétaire
-   Indique quelle boutique est actuellement active
-   Bouton pour sélectionner une boutique

#### `layouts/topbar.blade.php`

-   **Nouveau sélecteur de boutique** : Affiche un dropdown pour les propriétaires avec plusieurs boutiques
-   Visible uniquement si le propriétaire a plus d'une boutique
-   Affiche la boutique active avec une coche

## Utilisation

### Pour un propriétaire

1. **Sélectionner une boutique** :

    - Via le dropdown dans la barre de navigation (si plusieurs boutiques)
    - Via la page de sélection de boutique (`/boutiques`)

2. **Gérer ses boutiques** :
    - Un propriétaire peut éditer toutes ses boutiques
    - Accès à toutes les fonctionnalités pour chaque boutique sélectionnée

### Pour un administrateur (Super Admin)

1. **Créer une boutique** :

    - Lors de la création, la boutique est automatiquement assignée au propriétaire via la table pivot
    - La première boutique d'un propriétaire est marquée comme principale

2. **Assigner une boutique supplémentaire** :
    - Utiliser la méthode `ownedBoutiques()->attach()` pour assigner une boutique à un propriétaire existant

## Migration des données

Pour migrer les données existantes, exécutez :

```bash
php artisan migrate
```

Les migrations vont :

1. Créer la table pivot `boutique_user`
2. Migrer automatiquement les données existantes (boutiques avec `owner_id`)

## Rétrocompatibilité

Le système est entièrement rétrocompatible :

-   Les boutiques existantes avec `owner_id` continuent de fonctionner
-   La colonne `boutique_id` dans la table `users` est toujours utilisée pour la boutique principale
-   Les méthodes existantes fonctionnent toujours

## Sécurité

-   Vérification que l'utilisateur possède bien la boutique avant de la sélectionner
-   Vérification dans les middlewares que la boutique active appartient au propriétaire
-   Les employés ne peuvent toujours accéder qu'à leur boutique assignée

## Prochaines étapes possibles

1. Interface admin pour assigner des boutiques supplémentaires à un propriétaire
2. Statistiques consolidées pour les propriétaires avec plusieurs boutiques
3. Export de données multi-boutiques
4. Gestion des permissions par boutique



