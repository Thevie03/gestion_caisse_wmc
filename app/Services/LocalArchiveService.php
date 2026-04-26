<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\Category;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Depense;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\Notification;
use App\Models\PaiementAbonnement;
use App\Models\PaiementVente;
use App\Models\ParametreSysteme;
use App\Models\Permission;
use App\Models\Produit;
use App\Models\Taxe;
use App\Models\User;
use App\Models\Vente;
use App\Models\VenteDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Service d'archivage local des données de l'application.
 *
 * Génère un ensemble d'exports (JSON) et rassemble les pièces jointes
 * avant de créer un fichier ZIP prêt à être téléchargé.
 */
class LocalArchiveService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.archive_disk', config('filesystems.default'));
    }

    /**
     * Lance le processus d'archivage et retourne les informations du fichier généré.
     *
     * @param array $options Options d'archivage (peut contenir 'boutique_id' pour filtrer)
     * @param callable|null $progress Callback pour suivre la progression
     * @return array{
     *     success: bool,
     *     filename: string,
     *     path: string,
     *     size: int,
     *     created_at: \Carbon\CarbonInterface
     * }
     *
     * @throws \Exception
     */
    public function archiveData(array $options = [], ?callable $progress = null): array
    {
        // Utiliser un timestamp plus précis avec microsecondes pour éviter les doublons
        $timestamp = Carbon::now()->format('Ymd_His');
        $microseconds = Carbon::now()->micro;
        $uniqueId = uniqid('', true); // Génère un ID unique basé sur le temps et un préfixe aléatoire
        
        $boutiqueId = $options['boutique_id'] ?? null;
        $archiveAllBoutiques = $options['archive_all_boutiques'] ?? false;
        $archiveSystemData = $options['archive_system_data'] ?? false;

        // Nom du fichier selon le type d'archivage avec un identifiant unique
        if ($archiveSystemData) {
            // Archivage des données système
            $archiveName = "archive_donnees_systeme_wmc_{$timestamp}_{$uniqueId}.zip";
        } elseif ($boutiqueId && !$archiveAllBoutiques) {
            // Une boutique spécifique
            $boutique = Boutique::find($boutiqueId);
            $boutiqueName = $boutique ? \Illuminate\Support\Str::slug($boutique->nom) : 'boutique_' . $boutiqueId;
            $archiveName = "archive_{$boutiqueName}_{$timestamp}_{$uniqueId}.zip";
        } elseif ($archiveAllBoutiques) {
            // Archive complète de toutes les boutiques
            $archiveName = "archive_complete_toutes_boutiques_{$timestamp}_{$uniqueId}.zip";
        } else {
            $archiveName = "archive_boutique_{$timestamp}_{$uniqueId}.zip";
        }

        // Utiliser un identifiant unique pour le répertoire temporaire aussi
        $tempRoot = storage_path("app/archive_tmp_{$uniqueId}");
        $dataPath = "{$tempRoot}/data";
        $filesPath = "{$tempRoot}/files";

        File::ensureDirectoryExists($dataPath);
        File::ensureDirectoryExists($filesPath);

        try {
            // Récupérer les options d'archivage
            $boutiqueId = $options['boutique_id'] ?? null;
            $archiveAllBoutiques = $options['archive_all_boutiques'] ?? false;
            $archiveSystemData = $options['archive_system_data'] ?? false;

            if ($archiveSystemData) {
                // Archivage des données système uniquement
                $this->reportProgress($progress, 5, 'Export des données système…');
                $this->exportSystemData($dataPath);

                $this->reportProgress($progress, 35, 'Copie des fichiers système…');
                $this->copySystemFiles($filesPath);
            } else {
                // Archivage des données des boutiques
                $this->reportProgress($progress, 5, 'Export des données métiers…');
                $this->exportDatasets($dataPath, $boutiqueId, $archiveAllBoutiques);

                $this->reportProgress($progress, 35, 'Copie des fichiers…');
                $this->copyAttachments($filesPath, $boutiqueId);
            }

            $this->reportProgress($progress, 55, 'Sauvegarde de la base de données…');
            $this->copyDatabase($tempRoot, $options);

            // Le chemin relatif pour le disque "archives" (root = storage_path('app/archives'))
            $zipRelativePath = $archiveName;
            // Le chemin absolu complet
            $zipPath = storage_path("app/archives/{$archiveName}");
            File::ensureDirectoryExists(dirname($zipPath));
            
            // Vérifier si le fichier existe déjà (sécurité supplémentaire)
            if (File::exists($zipPath)) {
                // Si le fichier existe déjà, ajouter un suffixe unique supplémentaire
                $pathInfo = pathinfo($zipPath);
                $newArchiveName = $pathInfo['filename'] . '_' . uniqid() . '.' . $pathInfo['extension'];
                $zipPath = storage_path("app/archives/{$newArchiveName}");
                $zipRelativePath = $newArchiveName;
            }

            $this->reportProgress($progress, 75, 'Création de l’archive compressée…');
            $this->createZipFromDirectory($tempRoot, $zipPath);

            File::deleteDirectory($tempRoot);

            $checksum = hash_file('sha256', $zipPath);
            $size = File::size($zipPath);

            $this->reportProgress($progress, 95, 'Finalisation…');

            return [
                'success' => true,
                'filename' => $archiveName,
                'path' => $zipPath,
                'relative_path' => $zipRelativePath,
                'size' => $size,
                'checksum' => $checksum,
                'created_at' => Carbon::now(),
            ];
        } catch (Exception $e) {
            File::deleteDirectory($tempRoot);

            if (isset($zipPath) && File::exists($zipPath)) {
                File::delete($zipPath);
            }

            throw $e;
        }
    }

    /**
     * Exporte les données système du super admin (contrats, paiements, abonnements, boutiques, etc.)
     *
     * @param  string  $dataPath
     * @return void
     */
    protected function exportSystemData(string $dataPath): void
    {
        $datasets = [
            // Informations sur les boutiques (métadonnées)
            'boutiques.json' => Boutique::with(['owner'])->orderBy('created_at')->get(),

            // Contrats
            'contrats.json' => Contrat::with(['createur', 'boutique'])->orderBy('created_at')->get(),

            // Abonnements
            'abonnements.json' => Abonnement::with(['user', 'paiements'])->orderBy('created_at')->get(),

            // Paiements d'abonnements
            'paiements_abonnements.json' => PaiementAbonnement::with(['abonnement'])->orderBy('created_at')->get(),

            // Paramètres système
            'parametres_systeme.json' => ParametreSysteme::orderBy('cle')->get(),

            // Utilisateurs (tous les utilisateurs avec leurs informations)
            // ATTENTION : Les mots de passe sont exportés pour la restauration complète
            // En production, considérer le chiffrement de l'archive
            'utilisateurs.json' => User::with(['boutique', 'permissions'])->orderBy('created_at')->get()
                ->makeVisible(['password', 'remember_token']),

            // Permissions
            'permissions.json' => Permission::orderBy('created_at')->get(),
        ];

        foreach ($datasets as $filename => $collection) {
            $this->storeJson(
                "{$dataPath}/{$filename}",
                $collection->toArray()
            );
        }
    }

    /**
     * Exporte les principales données métiers au format JSON (données opérationnelles des boutiques).
     *
     * @param  string  $dataPath
     * @param  int|null  $boutiqueId ID de la boutique pour filtrer les données
     * @param  bool  $archiveAllBoutiques Si true, archiver toutes les boutiques
     * @return void
     */
    protected function exportDatasets(string $dataPath, ?int $boutiqueId = null, bool $archiveAllBoutiques = false): void
    {
        // Construire les requêtes avec filtrage par boutique si fourni
        $ventesQuery = Vente::with(['details', 'client', 'boutique', 'user', 'paiements', 'facture']);
        $produitsQuery = Produit::with(['boutique', 'fournisseur']);
        $categoriesQuery = Category::query();
        $clientsQuery = Client::query();
        $depensesQuery = Depense::query();
        $fournisseursQuery = Fournisseur::query();
        $taxesQuery = Taxe::query();
        $mouvementsStockQuery = MouvementStock::query();
        $ventesDetailsQuery = VenteDetail::query();
        $paiementsVentesQuery = PaiementVente::query();
        $facturesQuery = Facture::query();
        $notificationsQuery = Notification::query();

        // Filtrer par boutique si fourni (et si on n'archive pas toutes les boutiques)
        if ($boutiqueId && !$archiveAllBoutiques) {
            $ventesQuery->where('boutique_id', $boutiqueId);
            $produitsQuery->where('boutique_id', $boutiqueId);
            $categoriesQuery->where('boutique_id', $boutiqueId);
            $clientsQuery->where('boutique_id', $boutiqueId);
            $depensesQuery->where('boutique_id', $boutiqueId);
            $fournisseursQuery->where('boutique_id', $boutiqueId);
            // Taxe peut ne pas avoir boutique_id, donc on filtre seulement si la colonne existe
            // Note: Les taxes sont généralement globales, mais on peut les filtrer si nécessaire
            // Pour l'instant, on exporte toutes les taxes car elles peuvent être utilisées par plusieurs boutiques
            $mouvementsStockQuery->where('boutique_id', $boutiqueId);

            // Pour les ventes_details, filtrer via les ventes
            $venteIds = Vente::where('boutique_id', $boutiqueId)->pluck('id');
            $ventesDetailsQuery->whereIn('vente_id', $venteIds);

            // Pour les paiements_ventes, filtrer via les ventes
            $paiementsVentesQuery->whereIn('vente_id', $venteIds);

            // Pour les factures, filtrer via les ventes
            $facturesQuery->whereIn('vente_id', $venteIds);

            // Pour les notifications, filtrer par les utilisateurs de la boutique
            // (car notifications n'a pas de colonne boutique_id, seulement user_id)
            $userIds = User::where('boutique_id', $boutiqueId)->pluck('id');
            $notificationsQuery->whereIn('user_id', $userIds);
        }
        // Si archiveAllBoutiques est true, on exporte toutes les données sans filtre

        $datasets = [
            'ventes.json' => $ventesQuery->orderBy('created_at')->get(),
            'ventes_details.json' => $ventesDetailsQuery->orderBy('created_at')->get(),
            'paiements_ventes.json' => $paiementsVentesQuery->orderBy('created_at')->get(),
            'factures.json' => $facturesQuery->orderBy('created_at')->get(),
            'produits.json' => $produitsQuery->orderBy('created_at')->get(),
            'categories.json' => $categoriesQuery->orderBy('created_at')->get(),
            'clients.json' => $clientsQuery->orderBy('created_at')->get(),
            'depenses.json' => $depensesQuery->orderBy('created_at')->get(),
            'fournisseurs.json' => $fournisseursQuery->orderBy('created_at')->get(),
            'taxes.json' => $taxesQuery->orderBy('created_at')->get(),
            'mouvements_stock.json' => $mouvementsStockQuery->orderBy('created_at')->get(),
            'notifications.json' => $notificationsQuery->orderBy('created_at')->get(),
        ];

        // Boutiques : exporter selon les options
        if ($boutiqueId) {
            // Une boutique spécifique
            $datasets['boutiques.json'] = Boutique::where('id', $boutiqueId)->orderBy('created_at')->get();
        } elseif ($archiveAllBoutiques) {
            // Toutes les boutiques
            $datasets['boutiques.json'] = Boutique::orderBy('created_at')->get();
        } else {
            // Par défaut, toutes les boutiques
            $datasets['boutiques.json'] = Boutique::orderBy('created_at')->get();
        }

        // Permissions : toujours toutes (pas de filtrage par boutique)
        $datasets['permissions.json'] = Permission::orderBy('created_at')->get();

        // Utilisateurs : filtrer par boutique si fourni (et si on n'archive pas toutes les boutiques)
        $usersQuery = User::with(['boutique', 'permissions']);
        if ($boutiqueId && !$archiveAllBoutiques) {
            $usersQuery->where('boutique_id', $boutiqueId);
        }
        // Si archiveAllBoutiques est true, on exporte tous les utilisateurs
        $users = $usersQuery->orderBy('created_at')->get()->makeVisible(['password', 'remember_token']);

        $datasets['utilisateurs.json'] = $users;

        foreach ($datasets as $filename => $collection) {
            $this->storeJson(
                "{$dataPath}/{$filename}",
                $collection->toArray()
            );
        }
    }

    /**
     * Copie les fichiers système (contrats, documents administratifs, etc.)
     *
     * @param  string  $filesPath
     * @return void
     */
    protected function copySystemFiles(string $filesPath): void
    {
        // Copier les fichiers de contrats
        $contratsPath = storage_path('app/contrats');
        if (File::isDirectory($contratsPath)) {
            File::copyDirectory($contratsPath, "{$filesPath}/contrats");
        }

        // Copier les documents administratifs
        $documentsAdminPath = storage_path('app/documents_admin');
        if (File::isDirectory($documentsAdminPath)) {
            File::copyDirectory($documentsAdminPath, "{$filesPath}/documents_admin");
        }

        // Copier les logos des boutiques (métadonnées)
        $logosPath = public_path('images/logos');
        if (File::isDirectory($logosPath)) {
            File::copyDirectory($logosPath, "{$filesPath}/logos_boutiques");
        }
    }

    /**
     * Copie les pièces jointes et fichiers importants dans le dossier temporaire.
     *
     * @param  string  $filesPath
     * @param  int|null  $boutiqueId ID de la boutique pour filtrer les fichiers
     * @return void
     */
    protected function copyAttachments(string $filesPath, ?int $boutiqueId = null): void
    {
        // Copier le storage public (peut contenir des fichiers de toutes les boutiques)
        $publicStoragePath = storage_path('app/public');
        if (File::isDirectory($publicStoragePath)) {
            File::copyDirectory($publicStoragePath, "{$filesPath}/storage_public");
        }

        // Copier les images publiques (logos, etc.)
        $publicImagesPath = public_path('images');
        if (File::isDirectory($publicImagesPath)) {
            File::copyDirectory($publicImagesPath, "{$filesPath}/public_images");
        }

        // Copier les documents
        $documentsPath = storage_path('app/documents');
        if (File::isDirectory($documentsPath)) {
            File::copyDirectory($documentsPath, "{$filesPath}/documents");
        }

        // Note: Si besoin de filtrer les fichiers par boutique, on pourrait le faire ici
        // mais généralement les fichiers sont partagés ou identifiés par leur nom
    }

    /**
     * Copie la base SQLite si elle est utilisée par l'application.
     *
     * @param  string  $tempRoot
     * @return void
     */
    protected function copyDatabase(string $tempRoot, array $options = []): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (! $config) {
            Log::warning('Configuration de base de données introuvable pour l\'archivage');
            return;
        }

        if ($config['driver'] === 'sqlite') {
            $sqlitePath = $config['database'] ?? database_path('database.sqlite');
            if (File::exists($sqlitePath)) {
                File::copy($sqlitePath, "{$tempRoot}/database.sqlite");
                Log::info('Base SQLite copiée dans l\'archive');
            } else {
                Log::warning('Fichier SQLite introuvable: ' . $sqlitePath);
            }

            return;
        }

        if ($config['driver'] === 'mysql') {
            $dumpPath = "{$tempRoot}/database.sql";
            try {
                $this->dumpMySqlDatabase($config, $dumpPath, $options);
                Log::info('Dump MySQL créé avec succès');
            } catch (Exception $e) {
                Log::warning('Impossible de créer le dump MySQL: ' . $e->getMessage());
                Log::info('L\'archive contiendra uniquement les fichiers JSON. Les données pourront être restaurées depuis ces fichiers.');
                // Ne pas lever l'exception, continuer avec les fichiers JSON uniquement
            }

            return;
        }

        Log::warning('Driver de base de données non supporté pour l\'archivage', [
            'driver' => $config['driver'] ?? 'unknown',
        ]);
    }

    protected function dumpMySqlDatabase(array $config, string $dumpPath, array $options = []): void
    {
        // Essayer plusieurs chemins possibles pour mysqldump
        $possiblePaths = [
            $options['mysqldump_path'] ?? null,
            $config['dump_binary_path'] ?? null,
            'mysqldump', // Dans le PATH
        ];

        // Chemins Windows courants
        if (PHP_OS_FAMILY === 'Windows') {
            $mysqlVersion = $config['mysql_version'] ?? '8.0';
            $possiblePaths = array_merge($possiblePaths, [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                "C:\\wamp\\bin\\mysql\\mysql{$mysqlVersion}\\bin\\mysqldump.exe",
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
            ]);
        }

        $binary = null;
        foreach ($possiblePaths as $path) {
            if ($path && $this->commandExists($path)) {
                $binary = $path;
                break;
            }
        }

        if (!$binary) {
            throw new Exception(
                'mysqldump n\'est pas disponible sur ce système. ' .
                'Veuillez installer MySQL ou spécifier le chemin vers mysqldump dans la configuration. ' .
                'L\'archive contiendra uniquement les fichiers JSON qui permettront de restaurer toutes les données.'
            );
        }

        $command = [
            $binary,
            '--no-tablespaces',
            '--single-transaction',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ];

        $process = new Process($command);
        $process->setTimeout(300); // 5 minutes max
        $process->setEnv([
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            $error = $this->normalizeEncoding($process->getErrorOutput());
            $output = $this->normalizeEncoding($process->getOutput());

            throw new Exception(sprintf(
                'Échec du dump MySQL: %s. Sortie: %s',
                $error,
                $output
            ));
        }

        $output = $process->getOutput();
        if (empty($output)) {
            throw new Exception('Le dump MySQL est vide. Vérifiez les permissions et la connexion à la base de données.');
        }

        File::put($dumpPath, $output);
    }

    /**
     * Vérifie si une commande existe et est exécutable
     */
    protected function commandExists(string $command): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Sur Windows, vérifier si le fichier existe
            if (file_exists($command)) {
                return true;
            }
            // Essayer avec where.exe pour trouver la commande
            $process = new Process(['where.exe', $command]);
            $process->run();
            return $process->isSuccessful();
        } else {
            // Sur Unix/Linux, utiliser which
            $process = new Process(['which', $command]);
            $process->run();
            return $process->isSuccessful();
        }
    }

    public function restoreFromArchive(string $absolutePath, array $options = [], ?callable $progress = null): array
    {
        if (! File::exists($absolutePath)) {
            throw new Exception('Le fichier d’archive est introuvable.');
        }

        $this->reportProgress($progress, 5, 'Extraction de l’archive…');

        $tempRoot = storage_path('app/archive_restore_'.uniqid());
        File::ensureDirectoryExists($tempRoot);

        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            throw new Exception('Impossible de lire l’archive fournie.');
        }

        $zip->extractTo($tempRoot);
        $zip->close();

        try {
            $databaseRestored = $this->restoreDatabase($tempRoot, $progress);

            // Si la base de données n'a pas été restaurée, restaurer depuis les fichiers JSON
            if (!$databaseRestored) {
                $this->reportProgress($progress, 45, 'Restauration des données depuis les fichiers JSON…');
                $this->restoreFromJsonFiles($tempRoot, $progress);
            }

            $this->reportProgress($progress, 75, 'Restauration des fichiers…');
            $this->restoreFiles($tempRoot);

            $this->reportProgress($progress, 90, 'Nettoyage…');
            File::deleteDirectory($tempRoot);

            return [
                'database_restored' => $databaseRestored,
                'json_restored' => !$databaseRestored,
                'files_restored' => true,
            ];
        } catch (Exception $e) {
            File::deleteDirectory($tempRoot);
            throw $e;
        }
    }

    protected function restoreDatabase(string $tempRoot, ?callable $progress = null): bool
    {
        $sqliteBackup = "{$tempRoot}/database.sqlite";
        $mysqlDump = "{$tempRoot}/database.sql";
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] === 'sqlite' && File::exists($sqliteBackup)) {
            $this->reportProgress($progress, 45, 'Restauration de la base SQLite…');

            $destination = $config['database'] ?? database_path('database.sqlite');

            // Créer le répertoire si nécessaire
            File::ensureDirectoryExists(dirname($destination));

            // Sauvegarder l'ancienne base si elle existe
            if (File::exists($destination)) {
                $backupPath = sprintf('%s.bak_%s', $destination, Carbon::now()->format('YmdHis'));
                File::copy($destination, $backupPath);
                Log::info("Sauvegarde de la base existante créée: {$backupPath}");
            }

            File::copy($sqliteBackup, $destination);

            // S'assurer que les permissions sont correctes
            chmod($destination, 0666);

            Log::info("Base SQLite restaurée depuis l'archive.");
            return true;
        }

        if ($config['driver'] === 'mysql' && File::exists($mysqlDump)) {
            $this->reportProgress($progress, 45, 'Import des données MySQL…');

            try {
                $this->importMySqlDump($config, $mysqlDump);
                Log::info("Base MySQL restaurée depuis l'archive.");
                return true;
            } catch (Exception $e) {
                Log::error("Erreur lors de la restauration MySQL: " . $e->getMessage());
                // Continuer avec la restauration JSON si MySQL échoue
                return false;
            }
        }

        Log::info('Aucun dump de base compatible trouvé. Utilisation de la restauration JSON.');
        return false;
    }

    protected function importMySqlDump(array $config, string $dumpPath): void
    {
        $command = [
            'mysql',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ];

        $process = Process::fromShellCommandline(implode(' ', $command));
        $process->setTimeout(null);
        $process->setEnv([
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setInput(File::get($dumpPath));
        $process->run();

        if (! $process->isSuccessful()) {
            $error = $this->normalizeEncoding($process->getErrorOutput());

            throw new Exception(sprintf(
                'Échec de l’import MySQL: %s',
                $error
            ));
        }
    }

    protected function restoreFromJsonFiles(string $tempRoot, ?callable $progress = null): void
    {
        $dataPath = "{$tempRoot}/data";

        if (! File::isDirectory($dataPath)) {
            throw new Exception('Le dossier de données JSON est introuvable dans l\'archive.');
        }

        // Ordre de restauration important : d'abord les entités de base, puis les relations
        $restoreOrder = [
            'boutiques.json' => Boutique::class,
            'categories.json' => Category::class,
            'taxes.json' => Taxe::class,
            'fournisseurs.json' => Fournisseur::class,
            'clients.json' => Client::class,
            'produits.json' => Produit::class,
            'utilisateurs.json' => User::class,
            'depenses.json' => Depense::class,
            'ventes.json' => Vente::class,
            'ventes_details.json' => VenteDetail::class,
            'paiements_ventes.json' => PaiementVente::class,
            'factures.json' => Facture::class,
            'mouvements_stock.json' => MouvementStock::class,
            'notifications.json' => Notification::class,
            'permissions.json' => Permission::class,
        ];

        $total = count($restoreOrder);
        $current = 0;

        foreach ($restoreOrder as $filename => $modelClass) {
            $current++;
            $filePath = "{$dataPath}/{$filename}";

            if (! File::exists($filePath)) {
                Log::warning("Fichier JSON manquant dans l'archive: {$filename}");
                continue;
            }

            $percent = 45 + (int)(($current / $total) * 20);
            $this->reportProgress($progress, $percent, "Restauration de {$filename}…");

            $this->restoreJsonFile($filePath, $modelClass);
        }
    }

    protected function restoreJsonFile(string $filePath, string $modelClass): void
    {
        $jsonContent = File::get($filePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur de décodage JSON pour {$filePath}: " . json_last_error_msg());
        }

        if (empty($data) || !is_array($data)) {
            Log::info("Aucune donnée à restaurer dans {$filePath}");
            return;
        }

        $count = count($data);
        Log::info("Restauration de {$count} enregistrements depuis {$filePath}");

        // Utiliser une transaction pour garantir l'intégrité
        DB::beginTransaction();

        try {
            // Pour les utilisateurs, gérer les mots de passe
            if ($modelClass === User::class) {
                foreach ($data as $userData) {
                    // Vérifier si l'utilisateur existe déjà
                    $existingUser = User::where('email', $userData['email'] ?? null)->first();

                    if ($existingUser) {
                        // Mettre à jour l'utilisateur existant
                        $updateData = Arr::except($userData, ['id', 'created_at', 'updated_at', 'password']);
                        $existingUser->update($updateData);
                        if (isset($userData['password']) && !empty($userData['password'])) {
                            $existingUser->password = $userData['password'];
                            $existingUser->save();
                        }
                    } else {
                        // Créer un nouvel utilisateur
                        $createData = Arr::except($userData, ['id']);
                        User::create($createData);
                    }
                }
            } else {
                // Pour les autres modèles, utiliser upsert ou create
                foreach ($data as $item) {
                    $itemData = Arr::except($item, ['id', 'created_at', 'updated_at']);

                    // Essayer de trouver un enregistrement existant par des clés uniques
                    $existing = null;

                    if ($modelClass === Boutique::class && isset($itemData['nom'])) {
                        $existing = Boutique::where('nom', $itemData['nom'])->first();
                    } elseif ($modelClass === Category::class && isset($itemData['nom'])) {
                        $existing = Category::where('nom', $itemData['nom'])
                            ->where('boutique_id', $itemData['boutique_id'] ?? null)
                            ->first();
                    } elseif ($modelClass === Produit::class && isset($itemData['nom'])) {
                        $existing = Produit::where('nom', $itemData['nom'])
                            ->where('boutique_id', $itemData['boutique_id'] ?? null)
                            ->first();
                    } elseif ($modelClass === Client::class && isset($itemData['nom']) && isset($itemData['prenom'])) {
                        $existing = Client::where('nom', $itemData['nom'])
                            ->where('prenom', $itemData['prenom'])
                            ->where('boutique_id', $itemData['boutique_id'] ?? null)
                            ->first();
                    }

                    if ($existing) {
                        // Mettre à jour l'enregistrement existant
                        $existing->update($itemData);
                    } else {
                        // Créer un nouvel enregistrement
                        try {
                            $modelClass::create($itemData);
                        } catch (Exception $createException) {
                            // Si la création échoue (clé étrangère manquante, etc.), logger et continuer
                            Log::warning("Impossible de créer l'enregistrement dans {$modelClass}: " . $createException->getMessage());
                            Log::debug("Données: " . json_encode($itemData));
                        }
                    }
                }
            }

            DB::commit();
            Log::info("Restauration réussie de {$filePath}");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la restauration de {$filePath}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw new Exception("Erreur lors de la restauration de {$filePath}: " . $e->getMessage());
        }
    }

    protected function restoreFiles(string $tempRoot): void
    {
        $filesRoot = "{$tempRoot}/files";

        if (! File::isDirectory($filesRoot)) {
            return;
        }

        $map = [
            'storage_public' => storage_path('app/public'),
            'public_images' => public_path('images'),
            'documents' => storage_path('app/documents'),
        ];

        foreach ($map as $folder => $destination) {
            $source = "{$filesRoot}/{$folder}";

            if (! File::isDirectory($source)) {
                continue;
            }

            File::ensureDirectoryExists($destination);

            $this->copyDirectoryOverwrite($source, $destination);
        }
    }

    protected function copyDirectoryOverwrite(string $source, string $destination): void
    {
        $files = $this->getAllFiles($source);
        foreach ($files as $file) {
            $relative = $this->relativePath($source, $file);
            $target = $destination.DIRECTORY_SEPARATOR.$relative;

            File::ensureDirectoryExists(dirname($target));
            File::copy($file, $target);
        }
    }

    protected function reportProgress(?callable $progress, int $percent, string $message): void
    {
        if ($progress) {
            $progress($percent, $this->normalizeEncoding($message));
        }
    }

    protected function normalizeEncoding(string $value): string
    {
        if (mb_detect_encoding($value, 'UTF-8', true)) {
            return $value;
        }

        $encodings = ['UTF-8', 'ISO-8859-1', 'CP1252', 'CP850', 'ASCII'];

        return mb_convert_encoding($value, 'UTF-8', $encodings);
    }

    /**
     * Crée un fichier ZIP à partir d'un répertoire source.
     *
     * @param  string  $sourcePath
     * @param  string  $destinationPath
     * @return void
     *
     * @throws \Exception
     */
    protected function createZipFromDirectory(string $sourcePath, string $destinationPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Impossible de créer l\'archive ZIP.');
        }

        // Obtenir tous les répertoires de manière récursive
        $directories = $this->getAllDirectories($sourcePath);
        foreach ($directories as $directory) {
            $relativeDirectory = $this->relativePath($sourcePath, $directory);
            $zip->addEmptyDir($relativeDirectory);
        }

        // Obtenir tous les fichiers de manière récursive
        $files = $this->getAllFiles($sourcePath);
        foreach ($files as $file) {
            $relativeFile = $this->relativePath($sourcePath, $file);
            $zip->addFile($file, $relativeFile);
        }

        $zip->close();
    }

    /**
     * Obtient tous les répertoires de manière récursive
     */
    protected function getAllDirectories(string $path): array
    {
        $directories = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $directories[] = $file->getPathname();
            }
        }

        return $directories;
    }

    /**
     * Obtient tous les fichiers de manière récursive
     */
    protected function getAllFiles(string $path): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Enregistre un tableau PHP dans un fichier JSON mis en forme.
     *
     * @param  string  $path
     * @param  array<int|string, mixed>  $data
     * @return void
     */
    protected function storeJson(string $path, array $data): void
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            throw new Exception('Impossible de sérialiser les données en JSON pour l’archive.');
        }

        File::put($path, $json);
    }

    /**
     * Calcule le chemin relatif d'un fichier/dossier par rapport à une racine.
     *
     * @param  string  $root
     * @param  string  $absolutePath
     * @return string
     */
    protected function relativePath(string $root, string $absolutePath): string
    {
        return ltrim(str_replace($root, '', $absolutePath), DIRECTORY_SEPARATOR);
    }
}

