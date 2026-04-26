<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRATION SQLITE VERS MYSQL ===\n\n";

// Configuration MySQL - MODIFIEZ CES VALEURS SELON VOTRE CONFIGURATION
$mysqlConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'gestion_caisse',
    'username' => 'root',
    'password' => '',
];

echo "Configuration MySQL:\n";
echo "Host: {$mysqlConfig['host']}\n";
echo "Port: {$mysqlConfig['port']}\n";
echo "Base de données: {$mysqlConfig['database']}\n";
echo "Utilisateur: {$mysqlConfig['username']}\n";
echo "Mot de passe: " . (empty($mysqlConfig['password']) ? '(vide)' : '***') . "\n\n";

// Test de connexion MySQL
echo "Test de connexion MySQL...\n";
try {
    $pdo = new PDO("mysql:host={$mysqlConfig['host']};port={$mysqlConfig['port']}", $mysqlConfig['username'], $mysqlConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion MySQL réussie\n";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion MySQL: " . $e->getMessage() . "\n";
    echo "\nVérifiez que:\n";
    echo "1. MySQL/MariaDB est démarré\n";
    echo "2. Les identifiants sont corrects\n";
    echo "3. L'utilisateur a les droits de création de base de données\n";
    echo "\nModifiez les valeurs dans migrate_to_mysql.php si nécessaire.\n";
    exit(1);
}

// Créer la base de données
echo "\nCréation de la base de données '{$mysqlConfig['database']}'...\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$mysqlConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de données prête\n";
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// Se connecter à SQLite
echo "\nConnexion à SQLite...\n";
$sqlitePath = database_path('database.sqlite');
if (!file_exists($sqlitePath)) {
    echo "❌ Fichier SQLite non trouvé: $sqlitePath\n";
    exit(1);
}

$sqlite = new PDO("sqlite:$sqlitePath");
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "✅ Connexion SQLite réussie\n";

// Obtenir toutes les tables
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables trouvées: " . count($tables) . "\n";

// Se connecter à MySQL
$mysql = new PDO("mysql:host={$mysqlConfig['host']};port={$mysqlConfig['port']};dbname={$mysqlConfig['database']}", $mysqlConfig['username'], $mysqlConfig['password']);
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$mysql->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Exécuter les migrations Laravel
echo "\nExécution des migrations Laravel...\n";
try {
    // Temporairement changer la configuration
    config(['database.default' => 'mysql']);
    config(['database.connections.mysql' => array_merge(config('database.connections.mysql'), [
        'host' => $mysqlConfig['host'],
        'port' => $mysqlConfig['port'],
        'database' => $mysqlConfig['database'],
        'username' => $mysqlConfig['username'],
        'password' => $mysqlConfig['password'],
    ])]);

    \Illuminate\Support\Facades\DB::purge('mysql');
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
    echo "✅ Migrations exécutées\n";
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// Ordre de migration des tables (respect des dépendances)
$migrationOrder = [
    'boutiques',
    'categories',
    'clients',
    'fournisseurs',
    'users',
    'permissions',
    'user_permissions',
    'produits',
    'taxes',
    'ventes',
    'vente_details',
    'factures',
    'depenses',
    'mouvements_stock',
    'notifications',
    'password_resets',
    'failed_jobs',
    'personal_access_tokens',
    'migrations',
];

// Désactiver les contraintes de clés étrangères temporairement
$mysql->exec("SET FOREIGN_KEY_CHECKS = 0");

// Migrer les données dans l'ordre
echo "\nMigration des données...\n";
$totalMigrated = 0;

foreach ($migrationOrder as $table) {
    if (!in_array($table, $tables)) {
        continue;
    }

    try {
        $data = $sqlite->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            continue;
        }

        $columns = array_keys($data[0]);
        $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES $placeholders";

        $stmt = $mysql->prepare($sql);

        $count = 0;
        foreach ($data as $row) {
            $values = array_values($row);
            // Convertir les valeurs NULL correctement
            foreach ($values as $key => $value) {
                if ($value === null || $value === '') {
                    $values[$key] = null;
                }
            }
            $stmt->execute($values);
            $count++;
        }

        echo "  ✅ $table: $count enregistrements\n";
        $totalMigrated += $count;
    } catch (\Exception $e) {
        echo "  ⚠️  $table: " . $e->getMessage() . "\n";
    }
}

// Réactiver les contraintes de clés étrangères
$mysql->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n✅ Migration terminée ! ($totalMigrated enregistrements)\n";

// Mettre à jour le fichier .env
echo "\nMise à jour du fichier .env...\n";
$envFile = base_path('.env');

if (!file_exists($envFile)) {
    $envContent = "APP_NAME=GestionCaisse\n";
    $envContent .= "APP_ENV=local\n";
    $envContent .= "APP_KEY=\n";
    $envContent .= "APP_DEBUG=true\n";
    $envContent .= "APP_URL=http://localhost\n\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST={$mysqlConfig['host']}\n";
    $envContent .= "DB_PORT={$mysqlConfig['port']}\n";
    $envContent .= "DB_DATABASE={$mysqlConfig['database']}\n";
    $envContent .= "DB_USERNAME={$mysqlConfig['username']}\n";
    $envContent .= "DB_PASSWORD={$mysqlConfig['password']}\n";
    file_put_contents($envFile, $envContent);
    echo "✅ Fichier .env créé\n";
} else {
    $envContent = file_get_contents($envFile);
    $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $envContent);
    $envContent = preg_replace('/^DB_HOST=.*/m', "DB_HOST={$mysqlConfig['host']}", $envContent);
    $envContent = preg_replace('/^DB_PORT=.*/m', "DB_PORT={$mysqlConfig['port']}", $envContent);
    $envContent = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$mysqlConfig['database']}", $envContent);
    $envContent = preg_replace('/^DB_USERNAME=.*/m', "DB_USERNAME={$mysqlConfig['username']}", $envContent);
    $envContent = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$mysqlConfig['password']}", $envContent);
    file_put_contents($envFile, $envContent);
    echo "✅ Fichier .env mis à jour\n";
}

// Générer la clé d'application si nécessaire
if (empty(env('APP_KEY'))) {
    \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
    echo "✅ Clé d'application générée\n";
}

// Vider le cache
\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "✅ Cache vidé\n";

echo "\n🎉 MIGRATION TERMINÉE AVEC SUCCÈS !\n";
echo "\nVos données sont maintenant dans MySQL.\n";
echo "Vous pouvez voir vos données dans phpMyAdmin :\n";
echo "URL: http://localhost/phpmyadmin\n";
echo "Base de données: {$mysqlConfig['database']}\n";
echo "\nRedémarrez votre serveur Laravel pour appliquer les changements.\n";
