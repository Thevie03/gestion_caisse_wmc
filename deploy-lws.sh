#!/bin/bash
# =============================================================================
# Déploiement / mise à jour LWS — Gestion Caisse WMC (Laravel 11)
# Usage (SSH) : bash deploy-lws.sh
# =============================================================================

set -e

APP_DIR="${APP_DIR:-$HOME/public_html/gestioncaisse}"
GIT_BRANCH="${GIT_BRANCH:-upgrade/laravel-11}"
GIT_REMOTE="${GIT_REMOTE:-origin}"

echo "=========================================="
echo "  DÉPLOIEMENT LWS — Gestion Caisse WMC"
echo "=========================================="
echo "Dossier   : $APP_DIR"
echo "Branche   : $GIT_BRANCH"
echo ""

cd "$APP_DIR"

if [ ! -f "artisan" ]; then
    echo "ERREUR : artisan introuvable dans $APP_DIR"
    exit 1
fi

if [ ! -f ".env" ]; then
    echo "ERREUR : fichier .env manquant."
    echo "Copiez .env.lws.example vers .env et configurez-le."
    exit 1
fi

echo "[1/7] Git pull..."
git pull "$GIT_REMOTE" "$GIT_BRANCH"

echo "[2/7] Composer (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "[3/7] Migrations..."
php artisan migrate --force

echo "[4/7] Lien storage..."
php artisan storage:link 2>/dev/null || true

echo "[5/7] Nettoyage cache..."
php artisan optimize:clear

echo "[6/7] Optimisation production..."
php artisan optimize

echo "[7/7] Permissions..."
if [ -f "fix_permissions_simple.sh" ]; then
    bash fix_permissions_simple.sh
else
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    chmod 755 artisan 2>/dev/null || true
fi

echo ""
echo "=========================================="
echo "  DÉPLOIEMENT TERMINÉ"
echo "=========================================="
echo "Vérifiez : https://<votre-domaine>"
echo "Logs     : storage/logs/laravel.log"
echo ""
