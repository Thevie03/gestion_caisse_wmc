<?php

/**
 * Script de vérification des permissions
 * Vérifie que tous les dossiers sont en 755 et tous les fichiers en 644
 */

$baseDir = __DIR__;
$errors = [];
$warnings = [];
$checked = ['directories' => 0, 'files' => 0];
$fixed = ['directories' => 0, 'files' => 0];

// Dossiers et fichiers à ignorer
$ignorePaths = [
    '.git',
    '.gitignore',
    'node_modules',
    'vendor',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
    '.env',
    '.env.backup',
    '.env.production',
];

function shouldIgnore($path, $ignorePaths) {
    foreach ($ignorePaths as $ignore) {
        if (strpos($path, $ignore) !== false) {
            return true;
        }
    }
    return false;
}

function checkPermissions($path, $isDir) {
    global $errors, $warnings, $checked, $fixed;

    if (shouldIgnore($path, $GLOBALS['ignorePaths'])) {
        return;
    }

    if (!file_exists($path)) {
        return;
    }

    $perms = fileperms($path);
    $octal = substr(sprintf('%o', $perms), -4);

    if ($isDir) {
        $checked['directories']++;
        // Les dossiers doivent être en 755 (0755)
        if ($octal !== '0755' && $octal !== '755') {
            $errors[] = [
                'type' => 'directory',
                'path' => $path,
                'current' => $octal,
                'expected' => '0755'
            ];
        }
    } else {
        $checked['files']++;
        // Les fichiers doivent être en 644 (0644)
        if ($octal !== '0644' && $octal !== '644') {
            // Certains fichiers doivent être exécutables (comme artisan)
            $shouldBeExecutable = (basename($path) === 'artisan' ||
                                  strpos($path, '.sh') !== false ||
                                  strpos($path, '.ps1') !== false);

            if ($shouldBeExecutable && ($octal === '0755' || $octal === '755')) {
                // C'est OK pour les fichiers exécutables
                return;
            }

            $errors[] = [
                'type' => 'file',
                'path' => $path,
                'current' => $octal,
                'expected' => '0644'
            ];
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
            checkPermissions($path, true);
            scanDirectory($path);
        } else {
            checkPermissions($path, false);
        }
    }
}

echo "=== VÉRIFICATION DES PERMISSIONS ===\n\n";
echo "Répertoire de base: $baseDir\n\n";
echo "Démarrage de la vérification...\n\n";

// Vérifier le répertoire racine
checkPermissions($baseDir, true);

// Scanner tous les dossiers et fichiers
scanDirectory($baseDir);

// Afficher les résultats
echo "=== RÉSULTATS ===\n\n";
echo "Dossiers vérifiés: {$checked['directories']}\n";
echo "Fichiers vérifiés: {$checked['files']}\n\n";

if (empty($errors)) {
    echo "✓ Tous les dossiers et fichiers ont les bonnes permissions !\n";
} else {
    echo "✗ " . count($errors) . " problème(s) trouvé(s) :\n\n";

    $dirErrors = array_filter($errors, fn($e) => $e['type'] === 'directory');
    $fileErrors = array_filter($errors, fn($e) => $e['type'] === 'file');

    if (!empty($dirErrors)) {
        echo "Dossiers avec mauvaises permissions (" . count($dirErrors) . ") :\n";
        foreach ($dirErrors as $error) {
            echo "  - {$error['path']} (actuel: {$error['current']}, attendu: {$error['expected']})\n";
        }
        echo "\n";
    }

    if (!empty($fileErrors)) {
        echo "Fichiers avec mauvaises permissions (" . count($fileErrors) . ") :\n";
        $displayCount = 0;
        foreach ($fileErrors as $error) {
            if ($displayCount < 50) { // Limiter l'affichage
                echo "  - {$error['path']} (actuel: {$error['current']}, attendu: {$error['expected']})\n";
                $displayCount++;
            }
        }
        if (count($fileErrors) > 50) {
            echo "  ... et " . (count($fileErrors) - 50) . " autres fichiers\n";
        }
        echo "\n";
    }

    echo "\n=== COMMANDES POUR CORRIGER ===\n\n";
    echo "Pour corriger les permissions, exécutez sur Linux/Mac :\n\n";

    if (!empty($dirErrors)) {
        echo "# Corriger les dossiers :\n";
        foreach ($dirErrors as $error) {
            $relativePath = str_replace($baseDir . '/', '', $error['path']);
            echo "chmod 755 \"{$relativePath}\"\n";
        }
        echo "\n";
    }

    if (!empty($fileErrors)) {
        echo "# Corriger les fichiers :\n";
        $count = 0;
        foreach ($fileErrors as $error) {
            if ($count < 20) {
                $relativePath = str_replace($baseDir . '/', '', $error['path']);
                echo "chmod 644 \"{$relativePath}\"\n";
                $count++;
            }
        }
        if (count($fileErrors) > 20) {
            echo "# ... et " . (count($fileErrors) - 20) . " autres fichiers\n";
        }
        echo "\n";
        echo "# Ou en une seule commande pour tous les fichiers :\n";
        echo "find . -type f ! -name 'artisan' ! -name '*.sh' ! -name '*.ps1' -exec chmod 644 {} \\;\n";
        echo "find . -type d -exec chmod 755 {} \\;\n";
    }
}

echo "\n=== FIN DE LA VÉRIFICATION ===\n";





