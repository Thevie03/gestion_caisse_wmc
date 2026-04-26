<?php

/**
 * Script pour réinitialiser les mots de passe des utilisateurs
 *
 * Usage: php reset_passwords.php
 *
 * ATTENTION: Ce script permet de définir un nouveau mot de passe pour les utilisateurs.
 * Les anciens mots de passe ne peuvent pas être récupérés.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "========================================\n";
echo "RÉINITIALISATION DES MOTS DE PASSE\n";
echo "========================================\n\n";

// Récupérer tous les utilisateurs
$users = User::orderBy('name')->get();

if ($users->isEmpty()) {
    echo "❌ Aucun utilisateur trouvé.\n";
    exit(1);
}

echo "Liste des utilisateurs:\n\n";
foreach ($users as $index => $user) {
    echo ($index + 1) . ". ID: {$user->id} - {$user->name} ({$user->email}) - Rôle: " . strtoupper($user->role) . "\n";
}

echo "\n";
echo "Options:\n";
echo "1. Réinitialiser un mot de passe spécifique\n";
echo "2. Générer un fichier avec des mots de passe temporaires pour tous les utilisateurs\n";
echo "3. Quitter\n\n";

$choice = readline("Votre choix (1-3): ");

if ($choice == '1') {
    $userId = readline("Entrez l'ID de l'utilisateur: ");
    $user = User::find($userId);

    if (!$user) {
        echo "❌ Utilisateur non trouvé.\n";
        exit(1);
    }

    echo "\nUtilisateur sélectionné: {$user->name} ({$user->email})\n";
    $newPassword = readline("Entrez le nouveau mot de passe: ");

    if (strlen($newPassword) < 8) {
        echo "❌ Le mot de passe doit contenir au moins 8 caractères.\n";
        exit(1);
    }

    $user->update(['password' => bcrypt($newPassword)]);

    echo "✅ Mot de passe réinitialisé avec succès pour {$user->name}!\n";
    echo "📧 Nouveau mot de passe: {$newPassword}\n";

} elseif ($choice == '2') {
    echo "\nGénération de mots de passe temporaires...\n\n";

    $passwords = [];
    $content = "========================================\n";
    $content .= "MOTS DE PASSE TEMPORAIRES\n";
    $content .= "Généré le: " . now()->format('d/m/Y à H:i:s') . "\n";
    $content .= "⚠️  CONSERVEZ CE FICHIER EN SÉCURITÉ ET SUPPRIMEZ-LE APRÈS UTILISATION!\n";
    $content .= "========================================\n\n";

    foreach ($users as $user) {
        // Générer un mot de passe aléatoire
        $tempPassword = bin2hex(random_bytes(8)); // 16 caractères hexadécimaux

        // Mettre à jour le mot de passe dans la base de données
        $user->update(['password' => bcrypt($tempPassword)]);

        $content .= "--- {$user->name} ---\n";
        $content .= "ID: {$user->id}\n";
        $content .= "Email: {$user->email}\n";
        $content .= "Rôle: " . strtoupper($user->role) . "\n";
        $content .= "Mot de passe temporaire: {$tempPassword}\n";
        $content .= "\n";

        $passwords[] = [
            'user' => $user,
            'password' => $tempPassword
        ];

        echo "✅ {$user->name}: {$tempPassword}\n";
    }

    // Sauvegarder dans un fichier
    $filename = 'MOTS_DE_PASSE_TEMPORAIRES_' . now()->format('Y-m-d_H-i-s') . '.txt';
    file_put_contents($filename, $content);

    echo "\n✅ Fichier généré: {$filename}\n";
    echo "⚠️  ATTENTION: Ce fichier contient des mots de passe en clair!\n";
    echo "⚠️  Supprimez-le après avoir distribué les mots de passe aux utilisateurs.\n";

} else {
    echo "Au revoir!\n";
    exit(0);
}



