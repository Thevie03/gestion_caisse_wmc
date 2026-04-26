<?php
/**
 * Script de correction des permissions pour serveur Linux
 * Usage: php fix_permissions.php
 * OU: Accéder via navigateur: https://votre-domaine.com/fix_permissions.php
 *
 * ATTENTION: Supprimez ce fichier après utilisation pour des raisons de sécurité !
 */

// Sécurité: Vérifier que le script est exécuté depuis la ligne de commande ou avec un token
$allowedToken = 'CHANGEZ_MOI_AVANT_UTILISATION_' . date('Ymd');
$providedToken = $_GET['token'] ?? '';

// Si accès via navigateur, exiger un token
if (php_sapi_name() !== 'cli' && $providedToken !== $allowedToken) {
    die("Accès refusé. Utilisez: ?token=" . $allowedToken . " OU exécutez via ligne de commande: php fix_permissions.php");
}

echo "==========================================\n";
echo "  CORRECTION DES PERMISSIONS\n";
echo "==========================================\n\n";

$baseDir = __DIR__;
$errors = [];
$fixed = ['directories' => 0, 'files' => 0];
$checked = ['directories' => 0, 'files' => 0];

// Dossiers et fichiers à ignorer
$ignorePaths = [
    '.git',
    'node_modules',
    'vendor',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

function shouldIgnore($path, $ignorePaths) {
    foreach ($ignorePaths as $ignore) {
        if (strpos($path, $ignore) !== false) {
            return true;
        }
    }
    return false;
}

function fixPermissions($path, $isDir) {
    global $errors, $fixed, $checked, $baseDir;

    if (shouldIgnore($path, $GLOBALS['ignorePaths'])) {
        return;
    }

    if (!file_exists($path)) {
        return;
    }

    $relativePath = str_replace($baseDir . '/', '', $path);

    if ($isDir) {
        $checked['directories']++;
        // Les dossiers doivent être en 755
        if (chmod($path, 0755)) {
            $fixed['directories']++;
            echo "✓ Dossier: $relativePath -> 755\n";
        } else {
            $errors[] = "Impossible de modifier: $relativePath";
            echo "✗ ERREUR: $relativePath\n";
        }
    } else {
        $checked['files']++;
        // Les fichiers doivent être en 644
        // Exception: artisan et scripts .sh doivent être en 755
        $shouldBeExecutable = (basename($path) === 'artisan' ||
                              strpos($path, '.sh') !== false);

        $mode = $shouldBeExecutable ? 0755 : 0644;

        if (chmod($path, $mode)) {
            $fixed['files']++;
            $modeStr = $shouldBeExecutable ? '755' : '644';
            echo "✓ Fichier: $relativePath -> $modeStr\n";
        } else {
            $errors[] = "Impossible de modifier: $relativePath";
            echo "✗ ERREUR: $relativePath\n";
        }
    }
}

function scanDirectory($dir) {
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (shouldIgnore($path, $GLOBALS['ignorePaths'])) {
            continue;
        }

        if (is_dir($path)) {
            fixPermissions($path, true);
            scanDirectory($path);
        } else {
            fixPermissions($path, false);
        }
    }
}

// Vérifier le répertoire racine
fixPermissions($baseDir, true);

// Scanner tous les dossiers et fichiers
echo "\nCorrection en cours...\n\n";
scanDirectory($baseDir);

// Permissions spéciales pour Laravel
echo "\nApplication des permissions spéciales pour Laravel...\n";

$specialDirs = [
    'storage',
    'storage/app',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($specialDirs as $dir) {
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . $dir;
    if (is_dir($fullPath)) {
        if (chmod($fullPath, 0775)) {
            echo "✓ $dir -> 775 (écriture requise pour Laravel)\n";
        } else {
            echo "✗ ERREUR: $dir\n";
        }
    }
}

// Rendre artisan exécutable
$artisanPath = $baseDir . DIRECTORY_SEPARATOR . 'artisan';
if (file_exists($artisanPath)) {
    if (chmod($artisanPath, 0755)) {
        echo "✓ artisan -> 755\n";
    }
}

// Afficher les résultats
echo "\n==========================================\n";
echo "  RÉSUMÉ\n";
echo "==========================================\n\n";
echo "Dossiers vérifiés: {$checked['directories']}\n";
echo "Fichiers vérifiés: {$checked['files']}\n";
echo "Dossiers corrigés: {$fixed['directories']}\n";
echo "Fichiers corrigés: {$fixed['files']}\n\n";

if (empty($errors)) {
    echo "✓ Toutes les permissions ont été corrigées avec succès !\n";
} else {
    echo "⚠ " . count($errors) . " erreur(s) rencontrée(s)\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n==========================================\n";
echo "⚠ IMPORTANT: Supprimez ce fichier (fix_permissions.php) après utilisation !\n";
echo "==========================================\n";











