# Guide pour créer un Super Administrateur

## Option 1 : Utiliser la commande Artisan (Recommandé)

Après avoir exécuté la migration, vous pouvez créer un super administrateur avec la commande :

```bash
php artisan super-admin:create --email=votre-email@example.com --password=votre-mot-de-passe --name="Nom du Super Admin"
```

**Options disponibles :**

-   `--email` : Email du super admin (défaut: superadmin@test.com)
-   `--password` : Mot de passe (défaut: password)
-   `--name` : Nom complet (défaut: Super Administrateur)

**Exemples :**

```bash
# Avec les valeurs par défaut
php artisan super-admin:create

# Avec des valeurs personnalisées
php artisan super-admin:create --email=admin@monsite.com --password=MonMotDePasse123! --name="John Doe"
```

## Option 2 : Modifier un utilisateur existant

Si vous avez déjà un admin et que vous voulez le convertir en super admin, vous pouvez :

1. **Via SQL direct** :

```sql
UPDATE users SET role = 'super_admin' WHERE email = 'votre-email@example.com';
```

2. **Via Tinker** :

```bash
php artisan tinker
```

Puis dans Tinker :

```php
$user = \App\Models\User::where('email', 'votre-email@example.com')->first();
$user->role = 'super_admin';
$user->save();
```

## Option 3 : Utiliser le Seeder

Le `UserSeeder` crée automatiquement un super admin avec :

-   Email : `superadmin@gestioncaisse.com`
-   Mot de passe : `password`
-   Nom : `Super Administrateur`

```bash
php artisan db:seed --class=UserSeeder
```

## Important

1. **Exécutez d'abord la migration** pour ajouter le rôle 'super_admin' :

    ```bash
    php artisan migrate
    ```

2. **Le super admin a accès à toutes les fonctionnalités**, y compris la création d'administrateurs via `/admins`.

3. **Sécurité** : Changez le mot de passe par défaut après la première connexion !

## Vérifier que tout fonctionne

Après création, vous pouvez vérifier :

-   Connectez-vous avec le compte super admin
-   Allez sur `/admins` pour voir la liste des administrateurs
-   Créez un nouvel administrateur via le bouton "Ajouter un administrateur"





















