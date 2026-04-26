<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== NETTOYAGE DE LA BASE DE DONNÉES ===\n";
echo "⚠️  ATTENTION: Cette opération va supprimer toutes les données sauf les utilisateurs !\n";
echo "Appuyez sur Entrée pour continuer ou Ctrl+C pour annuler...\n";
readline();

echo "\n=== SAUVEGARDE DES UTILISATEURS ===\n";

// Sauvegarder les utilisateurs
$users = \App\Models\User::all();
echo "Utilisateurs trouvés: " . $users->count() . "\n";

$usersData = [];
foreach ($users as $user) {
    $usersData[] = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'boutique_id' => $user->boutique_id,
        'email_verified_at' => $user->email_verified_at,
        'password' => $user->password,
        'remember_token' => $user->remember_token,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at,
    ];
    echo "- {$user->name} ({$user->email}) - Role: {$user->role} - Boutique: {$user->boutique_id}\n";
}

echo "\n=== SUPPRESSION DES DONNÉES ===\n";

// Désactiver les contraintes de clés étrangères temporairement
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

try {
    // Supprimer les données dans l'ordre pour éviter les erreurs de contraintes

    echo "1. Suppression des détails de ventes...\n";
    \App\Models\DetailVente::truncate();
    echo "✅ Détails de ventes supprimés\n";

    echo "2. Suppression des ventes...\n";
    \App\Models\Vente::truncate();
    echo "✅ Ventes supprimées\n";

    echo "3. Suppression des mouvements de stock...\n";
    \App\Models\MouvementStock::truncate();
    echo "✅ Mouvements de stock supprimés\n";

    echo "4. Suppression des produits...\n";
    \App\Models\Produit::truncate();
    echo "✅ Produits supprimés\n";

    echo "5. Suppression des dépenses...\n";
    \App\Models\Depense::truncate();
    echo "✅ Dépenses supprimées\n";

    echo "6. Suppression des sessions de caisse...\n";
    \App\Models\SessionCaisse::truncate();
    echo "✅ Sessions de caisse supprimées\n";

    echo "7. Suppression des clients...\n";
    \App\Models\Client::truncate();
    echo "✅ Clients supprimés\n";

    echo "8. Suppression des fournisseurs...\n";
    \App\Models\Fournisseur::truncate();
    echo "✅ Fournisseurs supprimés\n";

    echo "9. Suppression des catégories...\n";
    \App\Models\Categorie::truncate();
    echo "✅ Catégories supprimées\n";

    echo "10. Suppression des boutiques...\n";
    \App\Models\Boutique::truncate();
    echo "✅ Boutiques supprimées\n";

    // Réactiver les contraintes
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "\n=== RECRÉATION DES DONNÉES DE BASE ===\n";

    // Recréer les boutiques
    echo "Recréation des boutiques...\n";
    $boutique1 = \App\Models\Boutique::create([
        'nom' => 'Cosmetica',
        'adresse' => 'Adresse Cosmetica',
        'telephone' => '123456789',
        'actif' => true,
    ]);
    echo "✅ Boutique 'Cosmetica' créée (ID: {$boutique1->id})\n";

    $boutique2 = \App\Models\Boutique::create([
        'nom' => 'Maison des Abaya',
        'adresse' => 'Adresse Maison des Abaya',
        'telephone' => '987654321',
        'actif' => true,
    ]);
    echo "✅ Boutique 'Maison des Abaya' créée (ID: {$boutique2->id})\n";

    // Recréer les catégories
    echo "Recréation des catégories...\n";
    $categories = [
        ['nom' => 'Cosmétiques', 'description' => 'Produits cosmétiques'],
        ['nom' => 'Vêtements', 'description' => 'Vêtements et accessoires'],
        ['nom' => 'Autres', 'description' => 'Autres produits'],
    ];

    foreach ($categories as $cat) {
        $categorie = \App\Models\Categorie::create($cat);
        echo "✅ Catégorie '{$categorie->nom}' créée (ID: {$categorie->id})\n";
    }

    // Mettre à jour les utilisateurs avec les nouveaux IDs de boutiques
    echo "Mise à jour des utilisateurs...\n";
    foreach ($usersData as $userData) {
        $user = \App\Models\User::find($userData['id']);
        if ($user) {
            // Réassigner les boutiques selon l'ancien ID
            if ($userData['boutique_id'] == 1) {
                $user->boutique_id = $boutique1->id;
            } elseif ($userData['boutique_id'] == 2) {
                $user->boutique_id = $boutique2->id;
            }
            $user->save();
            echo "✅ Utilisateur '{$user->name}' mis à jour - Boutique: {$user->boutique_id}\n";
        }
    }

    echo "\n=== VÉRIFICATION FINALE ===\n";
    echo "Utilisateurs: " . \App\Models\User::count() . "\n";
    echo "Boutiques: " . \App\Models\Boutique::count() . "\n";
    echo "Catégories: " . \App\Models\Categorie::count() . "\n";
    echo "Produits: " . \App\Models\Produit::count() . "\n";
    echo "Ventes: " . \App\Models\Vente::count() . "\n";
    echo "Dépenses: " . \App\Models\Depense::count() . "\n";
    echo "Clients: " . \App\Models\Client::count() . "\n";

    echo "\n✅ NETTOYAGE TERMINÉ AVEC SUCCÈS !\n";
    echo "La base de données a été vidée et réinitialisée avec les données de base.\n";
    echo "Les utilisateurs et leurs accès ont été préservés.\n";

} catch (\Exception $e) {
    echo "\n❌ ERREUR lors du nettoyage: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";

    // Réactiver les contraintes en cas d'erreur
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}


























