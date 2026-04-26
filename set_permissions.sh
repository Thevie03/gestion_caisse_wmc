#!/bin/bash

# Script pour définir les permissions des dossiers (755) et fichiers (644)
# Usage: bash set_permissions.sh

echo "=========================================="
echo "  DÉFINITION DES PERMISSIONS"
echo "=========================================="
echo ""
echo "Dossiers: 755"
echo "Fichiers: 644"
echo ""

# Compteurs
DIR_COUNT=0
FILE_COUNT=0
ERROR_COUNT=0

# Définir les permissions pour tous les dossiers (755)
echo "Mise à jour des permissions des dossiers..."
find . -type d \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -path '*/storage/framework/cache/*' \
    -not -path '*/storage/framework/sessions/*' \
    -not -path '*/storage/framework/views/*' \
    -not -path '*/storage/logs/*' \
    -not -path '*/bootstrap/cache/*' \
    -exec chmod 755 {} \; 2>/dev/null

DIR_COUNT=$(find . -type d \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' | wc -l)

# Définir les permissions pour tous les fichiers (644)
echo "Mise à jour des permissions des fichiers..."
find . -type f \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -name 'artisan' \
    -not -name '*.sh' \
    -not -name '*.ps1' \
    -exec chmod 644 {} \; 2>/dev/null

FILE_COUNT=$(find . -type f \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -name 'artisan' \
    -not -name '*.sh' \
    -not -name '*.ps1' | wc -l)

# Permissions spéciales pour Laravel
echo ""
echo "Application des permissions spéciales pour Laravel..."

# Storage doit être en 775 pour permettre l'écriture
if [ -d "storage" ]; then
    chmod -R 775 storage 2>/dev/null
    echo "✓ storage/ mis à 775"
fi

# Bootstrap cache doit être en 775
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache 2>/dev/null
    echo "✓ bootstrap/cache/ mis à 775"
fi

# Rendre les scripts exécutables
if [ -f "artisan" ]; then
    chmod 755 artisan 2>/dev/null
    echo "✓ artisan mis à 755"
fi

# Rendre les scripts shell exécutables
find . -name "*.sh" -type f -exec chmod 755 {} \; 2>/dev/null
SH_COUNT=$(find . -name "*.sh" -type f | wc -l)
if [ "$SH_COUNT" -gt 0 ]; then
    echo "✓ $SH_COUNT script(s) .sh mis à 755"
fi

echo ""
echo "=========================================="
echo "  VÉRIFICATION DES PERMISSIONS"
echo "=========================================="
echo ""

# Vérifier les dossiers
echo "Vérification des dossiers..."
BAD_DIRS=$(find . -type d \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -path '*/storage/*' \
    -not -path '*/bootstrap/cache/*' \
    -exec stat -c "%a %n" {} \; 2>/dev/null | grep -v "^755 " | wc -l)

if [ "$BAD_DIRS" -gt 0 ]; then
    echo "⚠ $BAD_DIRS dossier(s) avec mauvaises permissions"
    ERROR_COUNT=$((ERROR_COUNT + BAD_DIRS))
else
    echo "✓ Tous les dossiers ont les bonnes permissions (755)"
fi

# Vérifier les fichiers
echo "Vérification des fichiers..."
BAD_FILES=$(find . -type f \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -name 'artisan' \
    -not -name '*.sh' \
    -not -name '*.ps1' \
    -not -path '*/storage/*' \
    -not -path '*/bootstrap/cache/*' \
    -exec stat -c "%a %n" {} \; 2>/dev/null | grep -v "^644 " | wc -l)

if [ "$BAD_FILES" -gt 0 ]; then
    echo "⚠ $BAD_FILES fichier(s) avec mauvaises permissions"
    ERROR_COUNT=$((ERROR_COUNT + BAD_FILES))
else
    echo "✓ Tous les fichiers ont les bonnes permissions (644)"
fi

echo ""
echo "=========================================="
echo "  RÉSUMÉ"
echo "=========================================="
echo "Dossiers traités: $DIR_COUNT"
echo "Fichiers traités: $FILE_COUNT"
echo ""

if [ "$ERROR_COUNT" -eq 0 ]; then
    echo "✓ Toutes les permissions sont correctes!"
else
    echo "⚠ $ERROR_COUNT élément(s) avec des permissions incorrectes"
fi

echo ""
echo "Note: Les dossiers storage/ et bootstrap/cache/ ont été mis à 775"
echo "      pour permettre l'écriture par Laravel."
echo ""


