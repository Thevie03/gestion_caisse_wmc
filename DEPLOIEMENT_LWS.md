# Guide de déploiement sur LWS

## Étapes pour résoudre l'erreur 500

### 1. Vérifier les permissions des dossiers

Sur LWS, les dossiers suivants doivent avoir les permissions 755 ou 775 :

-   `storage/`
-   `storage/app/`
-   `storage/framework/`
-   `storage/framework/cache/`
-   `storage/framework/sessions/`
-   `storage/framework/views/`
-   `storage/logs/`
-   `bootstrap/cache/`

**Commandes via FTP/cPanel :**

-   Faites un clic droit sur chaque dossier → Propriétés/Permissions
-   Mettez `755` ou `775` pour les dossiers
-   `644` pour les fichiers

### 2. Créer le fichier .htaccess à la racine

Sur LWS, il faut généralement rediriger toutes les requêtes vers le dossier `public`.
Créez un fichier `.htaccess` à la racine du projet avec le contenu fourni.

### 3. Configuration du fichier .env

Assurez-vous que votre fichier `.env` sur le serveur contient :

-   `APP_ENV=production`
-   `APP_DEBUG=false`
-   `APP_URL=https://votre-domaine.com` (sans slash final)
-   Configuration de la base de données MySQL de LWS

### 4. Optimisation Laravel

Après avoir uploadé les fichiers :

1. Connectez-vous en SSH (si disponible) ou utilisez le terminal cPanel
2. Naviguez vers le dossier de votre application
3. Exécutez :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Vérifier la structure sur le serveur

Assurez-vous que :

-   Le dossier `public/` contient `index.php`
-   Le dossier `storage/` est accessible en écriture
-   Le fichier `.env` existe à la racine
-   Les dépendances Composer sont installées (`vendor/` doit exister)

### 6. Configuration de la base de données

1. Créez votre base de données MySQL via cPanel
2. Configurez les identifiants dans le `.env` :
    ```
    DB_CONNECTION=mysql
    DB_HOST=localhost (ou l'hôte fourni par LWS)
    DB_PORT=3306
    DB_DATABASE=votre_base
    DB_USERNAME=votre_utilisateur
    DB_PASSWORD=votre_mot_de_passe
    ```
3. Importez votre base de données ou exécutez les migrations :
    ```bash
    php artisan migrate --force
    ```

### 7. Vérifier les logs d'erreur

Si l'erreur persiste :

1. Activez temporairement le mode debug dans `.env` : `APP_DEBUG=true`
2. Consultez les logs dans `storage/logs/laravel.log`
3. Ou consultez les logs d'erreur Apache/PHP dans cPanel

### 8. Points spécifiques à LWS

-   **Document Root** :

    -   **Option 1 (Recommandée)** : Configurez le document root pour pointer vers le dossier `public/` dans cPanel

        -   Allez dans "Domaines" → "Redirection" ou "Document Root"
        -   Changez le document root vers `votre_dossier/public/`
        -   Dans ce cas, le fichier `.htaccess` à la racine n'est pas nécessaire

    -   **Option 2** : Si le document root pointe vers la racine, utilisez le fichier `.htaccess` fourni qui redirige vers `public/`

-   **Version PHP** :

    -   Vérifiez que PHP 8.0+ est activé (compatibilité Laravel 9)
    -   Dans cPanel : "Select PHP Version" → Choisissez PHP 8.0 ou supérieur (PHP 8.1 recommandé)
    -   Vérifiez les extensions activées ci-dessous

-   **Extensions PHP requises** :
    -   Vérifiez dans cPanel → "Select PHP Version" → "Extensions" que ces extensions sont activées :
        -   `openssl`
        -   `pdo`
        -   `pdo_mysql` (pour MySQL)
        -   `mbstring`
        -   `tokenizer`
        -   `xml`
        -   `ctype`
        -   `json`
        -   `fileinfo`
        -   `gd` (pour les images)
        -   `zip` (pour les archives)

### 9. Checklist avant la mise en ligne

-   [ ] Fichier `.env` configuré avec les bonnes valeurs
-   [ ] Permissions des dossiers `storage/` et `bootstrap/cache/` à 755
-   [ ] Dossier `vendor/` uploadé (ou `composer install` exécuté sur le serveur)
-   [ ] Base de données MySQL créée et configurée dans `.env`
-   [ ] Migrations exécutées ou base de données importée
-   [ ] Document root configuré (vers `public/` de préférence)
-   [ ] Version PHP 8.0+ activée
-   [ ] Extensions PHP requises activées
-   [ ] Caches Laravel générés (`php artisan config:cache`)

### 10. Résolution des problèmes courants

#### Erreur 500 persiste

1. Activez temporairement `APP_DEBUG=true` dans `.env`
2. Consultez `storage/logs/laravel.log` pour voir l'erreur exacte
3. Vérifiez les logs d'erreur PHP dans cPanel → "Erreurs PHP"

#### Erreur "No application encryption key"

```bash
php artisan key:generate
```

#### Erreur de permissions

```bash
# Via SSH (si disponible) ou via cPanel File Manager
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/app storage/framework
```

#### Erreur de connexion à la base de données

-   Vérifiez que `DB_HOST` est correct (souvent `localhost` sur LWS, parfois différent)
-   Vérifiez les identifiants dans cPanel → "Bases de données MySQL"
-   Certains hébergeurs LWS utilisent `127.0.0.1` au lieu de `localhost`

#### Erreur "Class not found" ou autoload

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
