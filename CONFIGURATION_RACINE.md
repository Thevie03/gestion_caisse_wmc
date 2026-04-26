# Configuration pour accès depuis public_html/thevie

## Structure des fichiers

Votre application doit être structurée ainsi sur le serveur :

```
public_html/
└── thevie/
    ├── .htaccess          ← Fichier à la racine (déjà corrigé)
    ├── .env
    ├── artisan
    ├── composer.json
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/            ← Dossier public Laravel
    │   ├── .htaccess      ← Fichier Laravel standard (déjà corrigé)
    │   ├── index.php
    │   └── ...
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

## Fichier .htaccess à la racine

Le fichier `.htaccess` à la racine (`public_html/thevie/.htaccess`) doit contenir :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Rediriger toutes les requêtes vers le dossier public
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>

<FilesMatch "^\.(env|git)">
    Order allow,deny
    Deny from all
</FilesMatch>

<Files composer.json>
    Order allow,deny
    Deny from all
</Files>

<Files composer.lock>
    Order allow,deny
    Deny from all
</Files>
```

## Fichier .htaccess dans public/

Le fichier `public/.htaccess` doit contenir les règles Laravel standard (déjà corrigé).

## Configuration du fichier .env

Dans votre fichier `.env` sur le serveur, assurez-vous que :

```env
APP_URL=https://votre-domaine.com/thevie
```

**Important :** Incluez le chemin `/thevie` dans l'URL si votre application n'est pas à la racine du domaine.

## Comment ça fonctionne

1. Quand vous accédez à `https://domaine.com/thevie`, le serveur cherche dans `public_html/thevie/`
2. Le fichier `.htaccess` à la racine intercepte la requête
3. Il redirige vers `public/index.php`
4. Laravel traite la requête normalement

## Vérifications

1. ✅ Le fichier `.htaccess` existe à la racine (`public_html/thevie/.htaccess`)
2. ✅ Le fichier `public/.htaccess` existe et contient les règles Laravel
3. ✅ Le fichier `public/index.php` existe
4. ✅ Le dossier `vendor/` existe (exécutez `composer install` si absent)
5. ✅ Le fichier `.env` est configuré avec `APP_URL` incluant `/thevie`

## Test

Après avoir téléversé les fichiers corrigés :

1. Accédez à : `https://votre-domaine.com/thevie`
2. L'application devrait se charger normalement

## Si ça ne fonctionne pas

1. Vérifiez les permissions des fichiers `.htaccess` (doivent être 644)
2. Vérifiez que `mod_rewrite` est activé sur le serveur
3. Vérifiez les logs d'erreur dans `storage/logs/laravel.log`
4. Activez temporairement `APP_DEBUG=true` dans `.env` pour voir les erreurs détaillées










