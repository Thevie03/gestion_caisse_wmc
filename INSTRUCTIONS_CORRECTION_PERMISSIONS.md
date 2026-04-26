# Instructions pour corriger les permissions sur le serveur

## Problème

Vos dossiers sont en **777** et vos fichiers en **666** au lieu de **755** et **644**. C'est un problème de sécurité.

## Solution 1 : Via cPanel File Manager (Recommandé)

1. **Connectez-vous à cPanel** et ouvrez le **File Manager**
2. **Naviguez** vers le répertoire de votre application (`public_html/thevie`)
3. Pour chaque **dossier** :
    - Clic droit → **Modifier les autorisations**
    - Décochez **Exécuter** pour **Tout le monde (World)**
    - Les valeurs doivent être : **User: 7, Group: 5, World: 5** (755)
    - Cliquez sur **Modifier les autorisations**
4. Pour chaque **fichier** :
    - Clic droit → **Modifier les autorisations**
    - Décochez **Écriture** et **Exécuter** pour **Tout le monde (World)**
    - Les valeurs doivent être : **User: 6, Group: 4, World: 4** (644)
    - Cliquez sur **Modifier les autorisations**

⚠️ **Note** : Les dossiers `storage/` et `bootstrap/cache/` doivent rester en **775** (User: 7, Group: 7, World: 5)

## Solution 2 : Via script PHP (Plus rapide)

1. **Téléversez** le fichier `fix_permissions.php` à la racine de votre application
2. **Accédez** via votre navigateur :
    ```
    https://votre-domaine.com/fix_permissions.php?token=CHANGEZ_MOI_AVANT_UTILISATION_20250119
    ```
    ⚠️ **Important** : Modifiez le token dans le fichier PHP avant de l'utiliser !
3. Le script corrigera automatiquement toutes les permissions
4. **Supprimez** le fichier `fix_permissions.php` après utilisation

## Solution 3 : Via SSH (Si disponible)

1. **Connectez-vous en SSH** à votre serveur
2. **Naviguez** vers le répertoire de votre application :
    ```bash
    cd ~/public_html/thevie
    ```
3. **Téléversez** le fichier `fix_permissions_simple.sh`
4. **Rendez-le exécutable** :
    ```bash
    chmod +x fix_permissions_simple.sh
    ```
5. **Exécutez-le** :
    ```bash
    bash fix_permissions_simple.sh
    ```
6. **Supprimez** le fichier après utilisation

## Permissions correctes

| Type             | Permissions | Description                                                        |
| ---------------- | ----------- | ------------------------------------------------------------------ |
| Dossiers         | **755**     | Lecture/Exécution pour tous, Écriture pour propriétaire uniquement |
| Fichiers         | **644**     | Lecture pour tous, Écriture pour propriétaire uniquement           |
| storage/         | **775**     | Écriture requise pour Laravel                                      |
| bootstrap/cache/ | **775**     | Écriture requise pour Laravel                                      |
| artisan          | **755**     | Exécutable                                                         |

## Vérification

Après correction, vérifiez que :

-   Les dossiers affichent **755** dans cPanel
-   Les fichiers affichent **644** dans cPanel
-   Les dossiers `storage/` et `bootstrap/cache/` affichent **775**

## Sécurité

⚠️ **IMPORTANT** :

-   Ne laissez jamais les permissions en **777** ou **666** en production
-   Supprimez les scripts de correction après utilisation
-   Les permissions **755/644** sont la norme pour les applications web










