# Configuration pour accès depuis public_html/gestioncaisse

## Structure des fichiers

Votre application doit être structurée ainsi sur le serveur :

```
public_html/
└── gestioncaisse/
    ├── .htaccess          ← Fichier à la racine (déjà dans le projet)
    ├── .env
    ├── artisan
    ├── composer.json
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/            ← Document root recommandé (cPanel)
    │   ├── .htaccess
    │   ├── index.php
    │   └── ...
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

## Document root (recommandé)

Dans cPanel → **Domaines** → configurez le document root vers :

```
public_html/gestioncaisse/public
```

Avec cette configuration :

```env
APP_URL=https://votre-domaine.com
```

## Option alternative : accès via /gestioncaisse

Si le document root reste `public_html`, le `.htaccess` à la racine de `gestioncaisse/` redirige vers `public/`.

Dans ce cas :

```env
APP_URL=https://votre-domaine.com/gestioncaisse
```

## Fichier .htaccess à la racine

Le fichier `.htaccess` à la racine (`public_html/gestioncaisse/.htaccess`) est déjà inclus dans le projet.

## Vérifications

1. Le fichier `.htaccess` existe à la racine (`public_html/gestioncaisse/.htaccess`)
2. Le fichier `public/.htaccess` existe
3. Le fichier `public/index.php` existe
4. Le dossier `vendor/` existe (`composer install --no-dev`)
5. Le fichier `.env` est configuré avec le bon `APP_URL`

## Test

1. Accédez à `https://votre-domaine.com` (document root → `public/`)
2. Ou `https://votre-domaine.com/gestioncaisse` (option sous-dossier)

## Si ça ne fonctionne pas

1. Vérifiez les permissions des fichiers `.htaccess` (644)
2. Vérifiez que `mod_rewrite` est activé
3. Consultez `storage/logs/laravel.log`
4. Activez temporairement `APP_DEBUG=true` dans `.env`
