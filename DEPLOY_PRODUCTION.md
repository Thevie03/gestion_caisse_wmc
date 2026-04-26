# Checklist de mise en production

Ce guide permet de déployer l'application Laravel avec une configuration sûre et performante, sans modifier la logique métier.

## 1) Pré-requis serveur

- PHP 8.2+ avec extensions nécessaires (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd`).
- Base de données MySQL/MariaDB.
- Process manager (Supervisor/PM2/systemd) pour:
  - `php artisan queue:work`
  - `php artisan reverb:start`
- HTTPS activé (certificat valide).

## 2) Configuration `.env` (production)

Points obligatoires:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://votre-domaine`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `LOG_LEVEL=error` (ou `warning`)
- `BROADCAST_CONNECTION=reverb`
- variables Reverb correctement renseignées:
  - `REVERB_APP_ID`
  - `REVERB_APP_KEY`
  - `REVERB_APP_SECRET`
  - `REVERB_HOST`
  - `REVERB_PORT`
  - `REVERB_SCHEME=https` (si TLS)

Notes sécurité:

- Ne jamais versionner `.env`.
- Régénérer/faire rotation des secrets si exposition suspectée.

## 3) Installation et build

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

## 4) Optimisations Laravel (obligatoire)

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5) Processus long running

Lancer et superviser en permanence:

```bash
php artisan queue:work --tries=3 --timeout=120
php artisan reverb:start
```

## 6) Vérifications post-déploiement

- Auth Breeze: login/logout OK.
- Permissions:
  - accès refusé si permission absente
  - sidebar dynamique conforme.
- Notifications:
  - réception temps réel
  - son bip
  - passage lu/non lu.
- Dashboards:
  - chargement rapide
  - filtres dates/vehicules opérationnels.
- Rapports:
  - affichage
  - export PDF/Excel.

## 7) Commandes de contrôle rapide

```bash
php artisan about
php artisan route:list
php artisan test --no-ansi
```

## 8) Rollback minimal

Si incident après release:

1. Revenir au build frontend précédent.
2. Revenir au commit applicatif précédent.
3. Si migration problématique: rollback ciblé uniquement après validation fonctionnelle.

