#!/bin/bash

# Script simple pour corriger les permissions
# Usage: bash fix_permissions_simple.sh

echo "=========================================="
echo "  CORRECTION DES PERMISSIONS"
echo "=========================================="
echo ""

# Corriger tous les dossiers à 755
echo "Correction des dossiers (755)..."
find . -type d \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -path '*/storage/framework/cache/*' \
    -not -path '*/storage/framework/sessions/*' \
    -not -path '*/storage/framework/views/*' \
    -not -path '*/storage/logs/*' \
    -not -path '*/bootstrap/cache/*' \
    -exec chmod 755 {} \;

echo "✓ Dossiers corrigés"
echo ""

# Corriger tous les fichiers à 644
echo "Correction des fichiers (644)..."
find . -type f \
    -not -path '*/\.*' \
    -not -path '*/node_modules/*' \
    -not -path '*/vendor/*' \
    -not -name 'artisan' \
    -not -name '*.sh' \
    -exec chmod 644 {} \;

echo "✓ Fichiers corrigés"
echo ""

# Permissions spéciales pour Laravel
echo "Application des permissions spéciales pour Laravel..."

# Storage doit être en 775
if [ -d "storage" ]; then
    chmod -R 775 storage
    echo "✓ storage/ -> 775"
fi

# Bootstrap cache doit être en 775
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    echo "✓ bootstrap/cache/ -> 775"
fi

# Rendre artisan exécutable
if [ -f "artisan" ]; then
    chmod 755 artisan
    echo "✓ artisan -> 755"
fi

# Rendre les scripts shell exécutables
find . -name "*.sh" -type f -exec chmod 755 {} \;
SH_COUNT=$(find . -name "*.sh" -type f | wc -l)
if [ "$SH_COUNT" -gt 0 ]; then
    echo "✓ $SH_COUNT script(s) .sh -> 755"
fi

echo ""
echo "=========================================="
echo "✓ CORRECTION TERMINÉE"
echo "=========================================="
echo ""
echo "Permissions appliquées:"
echo "  - Dossiers: 755"
echo "  - Fichiers: 644"
echo "  - storage/: 775 (écriture requise)"
echo "  - bootstrap/cache/: 775 (écriture requise)"
echo "  - artisan: 755 (exécutable)"
echo ""











