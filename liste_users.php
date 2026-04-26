<?php

/**
 * Script pour générer la liste de tous les utilisateurs
 *
 * Usage: php liste_users.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Boutique;
use App\Models\Abonnement;

echo "Génération de la liste des utilisateurs...\n\n";

// Récupérer tous les utilisateurs avec leurs relations
$users = User::with(['boutique', 'abonnementActif'])
    ->orderBy('role')
    ->orderBy('name')
    ->get();

// Créer le contenu du fichier
$content = "========================================\n";
$content .= "LISTE DES UTILISATEURS\n";
$content .= "Généré le: " . now()->format('d/m/Y à H:i:s') . "\n";
$content .= "========================================\n\n";

$content .= "TOTAL: " . $users->count() . " utilisateur(s)\n\n";

// Statistiques par rôle
$stats = [
    'admin' => $users->where('role', 'admin')->count(),
    'employe' => $users->where('role', 'employe')->count(),
    'actifs' => $users->where('actif', true)->count(),
    'inactifs' => $users->where('actif', false)->count(),
];

$content .= "STATISTIQUES:\n";
$content .= "- Administrateurs: {$stats['admin']}\n";
$content .= "- Employés/Commerçants: {$stats['employe']}\n";
$content .= "- Actifs: {$stats['actifs']}\n";
$content .= "- Inactifs: {$stats['inactifs']}\n\n";

$content .= "========================================\n\n";

// Liste détaillée
$content .= "DÉTAIL DES UTILISATEURS:\n\n";

foreach ($users as $index => $user) {
    $num = $index + 1;
    $content .= "--- Utilisateur #{$num} ---\n";
    $content .= "ID: {$user->id}\n";
    $content .= "Nom: {$user->name}\n";
    $content .= "Email: {$user->email}\n";
    $content .= "Téléphone: " . ($user->telephone ?? 'N/A') . "\n";
    $content .= "Rôle: " . strtoupper($user->role) . "\n";

    // Vérifier si c'est un propriétaire
    $isOwner = false;
    if ($user->boutique_id) {
        $boutique = Boutique::find($user->boutique_id);
        if ($boutique && $boutique->owner_id == $user->id) {
            $isOwner = true;
            $content .= "Statut: PROPRIÉTAIRE DE BOUTIQUE\n";
        } else {
            $content .= "Statut: EMPLOYÉ\n";
        }
    } else {
        $content .= "Statut: " . ($user->isAdmin() ? 'ADMINISTRATEUR' : 'AUCUNE BOUTIQUE') . "\n";
    }

    $content .= "Boutique: " . ($user->boutique ? $user->boutique->nom : 'Aucune') . "\n";
    $content .= "Boutique ID: " . ($user->boutique_id ?? 'N/A') . "\n";
    $content .= "Actif: " . ($user->actif ? 'OUI' : 'NON') . "\n";

    // Abonnement
    $abonnement = $user->abonnementActif;
    if ($abonnement) {
        $content .= "Abonnement: ACTIF\n";
        $content .= "  - Type: " . ucfirst($abonnement->type_abonnement) . "\n";
        $content .= "  - Date début: " . $abonnement->date_debut->format('d/m/Y') . "\n";
        $content .= "  - Date expiration: " . $abonnement->date_expiration->format('d/m/Y') . "\n";
        $content .= "  - Montant: " . number_format($abonnement->montant, 0, ',', ' ') . " FCFA\n";
        $content .= "  - Statut: " . strtoupper($abonnement->statut) . "\n";
    } else {
        $content .= "Abonnement: AUCUN\n";
    }

    $content .= "Dernière connexion: " . ($user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : 'Jamais') . "\n";
    $content .= "Créé le: " . $user->created_at->format('d/m/Y H:i:s') . "\n";

    // Note sur le mot de passe
    $content .= "Mot de passe: HASHÉ (non récupérable en clair)\n";
    $content .= "⚠️  Pour réinitialiser: Utilisez la fonction 'Réinitialiser le mot de passe' dans le dashboard admin\n";
    $content .= "\n";
}

// Grouper par boutique
$content .= "\n========================================\n";
$content .= "UTILISATEURS PAR BOUTIQUE:\n";
$content .= "========================================\n\n";

$boutiques = Boutique::with(['users', 'owner'])->get();

foreach ($boutiques as $boutique) {
    $content .= "--- {$boutique->nom} (ID: {$boutique->id}) ---\n";
    $content .= "Propriétaire: " . ($boutique->owner ? $boutique->owner->name . " ({$boutique->owner->email})" : 'N/A') . "\n";
    $content .= "Employés: " . $boutique->users->count() . "\n";

    if ($boutique->users->count() > 0) {
        foreach ($boutique->users as $user) {
            $role = $user->id == $boutique->owner_id ? 'PROPRIÉTAIRE' : 'EMPLOYÉ';
            $content .= "  - {$user->name} ({$user->email}) - {$role}\n";
        }
    }
    $content .= "\n";
}

// Section sur les mots de passe
$content .= "\n========================================\n";
$content .= "⚠️  IMPORTANT - MOTS DE PASSE\n";
$content .= "========================================\n\n";
$content .= "Les mots de passe sont stockés de manière sécurisée (hashés) dans la base de données.\n";
$content .= "Il est IMPOSSIBLE de les récupérer en clair pour des raisons de sécurité.\n\n";
$content .= "Pour réinitialiser un mot de passe:\n";
$content .= "1. Connectez-vous au dashboard admin\n";
$content .= "2. Allez dans 'Commerçants' ou 'Employés'\n";
$content .= "3. Cliquez sur l'utilisateur concerné\n";
$content .= "4. Utilisez la fonction 'Réinitialiser le mot de passe'\n\n";
$content .= "OU utilisez la commande artisan:\n";
$content .= "php artisan tinker\n";
$content .= "\\$user = \\App\\Models\\User::find(ID);\n";
$content .= "\\$user->update(['password' => bcrypt('nouveau_mot_de_passe')]);\n\n";

// Sauvegarder dans un fichier
$filename = 'LISTE_UTILISATEURS_' . now()->format('Y-m-d_H-i-s') . '.txt';
file_put_contents($filename, $content);

echo "✅ Fichier généré avec succès: {$filename}\n";
echo "📊 Total: {$users->count()} utilisateur(s)\n";
echo "📁 Emplacement: " . __DIR__ . DIRECTORY_SEPARATOR . $filename . "\n";
echo "⚠️  Note: Les mots de passe sont hashés et ne peuvent pas être récupérés en clair.\n";

