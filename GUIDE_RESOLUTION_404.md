# Guide de résolution de l'erreur 404

## Problème

Vous obtenez une erreur 404 "Not Found" lors de l'accès à votre application.

## Solutions

### Solution 1 : Configurer le Document Root (RECOMMANDÉ)

Le document root doit pointer vers le dossier `public/` de votre application.

**Dans cPanel :**

1. Allez dans **"Domaines"** ou **"Sous-domaines"**
2. Trouvez votre domaine/sous-domaine (ex: `thevie`)
3. Cliquez sur **"Modifier"** ou **"Gérer"**
4. Cherchez **"Document Root"** ou **"Chemin du document racine"**
5. Changez-le de :
    ```
    public_html/thevie
    ```
    Vers :
    ```
    public_html/thevie/public
    ```
6. Cliquez sur **"Sauvegarder"** ou **"Enregistrer"**

**Important :** Après cette modification, vous n'avez plus besoin du fichier `.htaccess` à la racine (celui qui redirige vers `/public/`).

---

### Solution 2 : Vérifier le fichier .htaccess à la racine

Si vous ne pouvez pas modifier le document root, assurez-vous que le fichier `.htaccess` à la racine existe et contient :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L,QSA]
</IfModule>
```

**Vérifications :**

1. Dans cPanel File Manager, allez dans `public_html/thevie`
2. Vérifiez que le fichier `.htaccess` existe
3. Si le fichier n'existe pas, créez-le avec le contenu ci-dessus
4. Vérifiez les permissions du fichier (doit être 644)

---

### Solution 3 : Vérifier le fichier public/.htaccess

Le fichier `public/.htaccess` doit contenir les règles de réécriture Laravel standard (déjà corrigé).

---

### Solution 4 : Vérifier la structure des fichiers

Assurez-vous que ces fichiers/dossiers existent :

```
public_html/thevie/
├── .env
├── .htaccess (à la racine)
├── artisan
├── composer.json
├── public/
│   ├── .htaccess
│   └── index.php
├── storage/
├── bootstrap/
└── vendor/
```

**Vérifications importantes :**

1. Le fichier `public/index.php` existe
2. Le dossier `vendor/` existe (si absent, exécutez `composer install`)
3. Le fichier `.env` existe et est configuré

---

### Solution 5 : Vérifier les permissions

Les permissions doivent être :

-   Dossiers : **755**
-   Fichiers : **644**
-   `storage/` et `bootstrap/cache/` : **775**

Utilisez le script `fix_permissions_simple.sh` pour corriger automatiquement.

---

### Solution 6 : Vérifier la configuration PHP

1. Dans cPanel, allez dans **"Select PHP Version"**
2. Vérifiez que PHP 8.0+ est sélectionné
3. Activez les extensions requises :
    - `openssl`
    - `pdo`
    - `pdo_mysql`
    - `mbstring`
    - `tokenizer`
    - `xml`
    - `ctype`
    - `json`
    - `fileinfo`

---

### Solution 7 : Vérifier le fichier .env

Assurez-vous que votre fichier `.env` contient :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=votre_base
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

**Important :** Remplacez les valeurs par vos vraies informations.

---

### Solution 8 : Générer la clé d'application

Si vous n'avez pas encore généré la clé d'application :

1. Connectez-vous en SSH (ou utilisez le terminal cPanel)
2. Naviguez vers votre application :
    ```bash
    cd ~/public_html/thevie
    ```
3. Exécutez :
    ```bash
    php artisan key:generate
    ```

---

### Solution 9 : Vider les caches

1. Connectez-vous en SSH
2. Naviguez vers votre application :
    ```bash
    cd ~/public_html/thevie
    ```
3. Exécutez :
    ```bash
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    ```

---

## Checklist de vérification

-   [ ] Document root pointe vers `public/` OU fichier `.htaccess` à la racine existe
-   [ ] Fichier `public/.htaccess` contient les règles Laravel
-   [ ] Fichier `public/index.php` existe
-   [ ] Dossier `vendor/` existe
-   [ ] Fichier `.env` existe et est configuré
-   [ ] Permissions correctes (755/644)
-   [ ] PHP 8.0+ activé avec extensions requises
-   [ ] Clé d'application générée (`APP_KEY` dans `.env`)
-   [ ] Base de données configurée et accessible

---

## Test rapide

Après avoir appliqué les corrections, testez :

1. Accédez à votre domaine : `https://votre-domaine.com`
2. Si vous voyez toujours 404, essayez : `https://votre-domaine.com/public`
3. Si `/public` fonctionne, le problème vient du document root ou du `.htaccess` à la racine

---

## Besoin d'aide ?

Si le problème persiste, vérifiez les logs :

1. **Logs Laravel** : `storage/logs/laravel.log`
2. **Logs PHP** : Dans cPanel → "Erreurs PHP"
3. **Logs Apache** : Dans cPanel → "Erreurs Apache"










