<?php

use App\Http\Controllers\Admin\AdminDashboardController as CentralAdminDashboardController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Landing page (publique, redirige vers dashboard si déjà connecté)
Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome')->middleware('guest');

// Route pour rafraîchir le token CSRF (utilisateurs authentifiés uniquement)
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware(['web', 'auth', 'throttle:30,1']);

// Routes protégées par authentification
Route::middleware(['auth', 'verified', 'theme', 'subscription', 'tenant'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('boutique');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // Dashboard Super Admin
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Gestion des utilisateurs
        Route::resource('users', UserManagementController::class);
        Route::post('users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/abonnements', [AdminSubscriptionController::class, 'store'])->name('users.abonnements.store');
        Route::patch('abonnements/{abonnement}', [AdminSubscriptionController::class, 'update'])->name('abonnements.update');

        // Gestion des boutiques
        Route::resource('boutiques', \App\Http\Controllers\Admin\BoutiqueManagementController::class);
        Route::get('boutiques/{boutiqueId}/supervision', [\App\Http\Controllers\Admin\BoutiqueManagementController::class, 'supervision'])->name('boutiques.supervision');
        Route::post('boutiques/{boutique}/toggle-actif', [\App\Http\Controllers\Admin\BoutiqueManagementController::class, 'toggleActif'])->name('boutiques.toggle-actif');

        // Gestion des paiements d'abonnements
        Route::get('paiements', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'index'])->name('paiements.index');
        Route::get('paiements/{paiement}', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'show'])->name('paiements.show');
        Route::get('paiements/{paiement}/edit', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'edit'])->name('paiements.edit');
        Route::post('paiements', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'store'])->name('paiements.store');
        Route::put('paiements/{paiement}', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'update'])->name('paiements.update');
        Route::post('paiements/{paiement}/confirmer', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'confirmer'])->name('paiements.confirmer');
        Route::post('paiements/{paiement}/refuser', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'refuser'])->name('paiements.refuser');
        Route::post('abonnements/{abonnement}/paiements', [\App\Http\Controllers\Admin\PaiementAbonnementController::class, 'storeForAbonnement'])->name('abonnements.paiements.store');

        // Statistiques
        Route::get('statistiques', [\App\Http\Controllers\Admin\StatistiquesController::class, 'index'])->name('statistiques.index');

        // Paramètres système
        Route::get('parametres', [\App\Http\Controllers\Admin\ParametresSystemeController::class, 'index'])->name('parametres.index');
        Route::post('parametres', [\App\Http\Controllers\Admin\ParametresSystemeController::class, 'update'])->name('parametres.update');

        // Support & Assistance
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('index');
            Route::get('/{ticket}', [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('show');
            Route::post('/{ticket}/repondre', [\App\Http\Controllers\Admin\SupportController::class, 'repondre'])->name('repondre');
            Route::post('/{ticket}/update-statut', [\App\Http\Controllers\Admin\SupportController::class, 'updateStatut'])->name('update-statut');
        });

        // Notifications Admin
        Route::get('notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/count', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'count'])->name('notifications.count');
        Route::get('notifications/{notification}', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{notification}/mark-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('notifications/{notification}', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('notifications/delete-all-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');

        // Gestion des contrats
        Route::resource('contrats', \App\Http\Controllers\Admin\ContratController::class);
        Route::get('contrats/{contrat}/view', [\App\Http\Controllers\Admin\ContratController::class, 'view'])->name('contrats.view');
        Route::get('contrats/{contrat}/download', [\App\Http\Controllers\Admin\ContratController::class, 'download'])->name('contrats.download');
    });

    // Archivage
    Route::post('/archive', [ArchiveController::class, 'store'])->name('archive.create');
    Route::get('/archive/download/{filename}', [ArchiveController::class, 'downloadLegacy'])->name('archive.download');

    Route::prefix('archives')->name('archives.')->group(function () {
        Route::get('/', [ArchiveController::class, 'index'])->name('index');
        Route::post('/', [ArchiveController::class, 'store'])->name('store');
        Route::get('/importer', [ArchiveController::class, 'createImport'])->name('import.create');
        Route::post('/importer', [ArchiveController::class, 'storeImport'])->name('import.store');
        Route::get('/{archive}/telecharger', [ArchiveController::class, 'download'])->name('download');
        Route::delete('/{archive}', [ArchiveController::class, 'destroy'])->name('destroy');
    });

    // Gestion des boutiques (Admin et propriétaires)
    Route::get('/boutiques', [BoutiqueController::class, 'index'])->name('boutiques.index');
    Route::get('/boutiques/select/{id}', [BoutiqueController::class, 'select'])->name('boutiques.select');
    Route::get('/boutiques/{boutique}/edit', [BoutiqueController::class, 'edit'])->name('boutiques.edit');
    Route::put('/boutiques/{boutique}', [BoutiqueController::class, 'update'])->name('boutiques.update');

    // Routes pour les modules (à créer)
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/', [App\Http\Controllers\VenteController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\VenteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\VenteController::class, 'store'])->name('store');
        Route::get('/pos/interface', [App\Http\Controllers\VenteController::class, 'pos'])->name('pos');
        Route::get('/{vente}/imprimer', [App\Http\Controllers\VenteController::class, 'imprimer'])->name('imprimer');
        Route::get('/{vente}/edit', [App\Http\Controllers\VenteController::class, 'edit'])->name('edit');
        Route::put('/{vente}', [App\Http\Controllers\VenteController::class, 'update'])->name('update');
        Route::delete('/{vente}', [App\Http\Controllers\VenteController::class, 'destroy'])->name('destroy');
        Route::get('/{vente}', [App\Http\Controllers\VenteController::class, 'show'])->name('show');
    });

    // Route API pour la recherche de produits par code-barres (utilisée par le POS)
    // Utilise le middleware web pour l'authentification par session
    Route::get('/api/find-product-by-barcode', [App\Http\Controllers\BarcodeController::class, 'find'])
        ->middleware('auth')
        ->name('api.find-product-by-barcode');

    // API PWA — bootstrap des données pour le mode hors connexion
    Route::prefix('api/offline')->name('api.offline.')->group(function () {
        Route::get('/bootstrap', [App\Http\Controllers\Api\OfflineBootstrapController::class, 'bootstrap'])
            ->name('bootstrap');
        Route::get('/ping', [App\Http\Controllers\Api\OfflineBootstrapController::class, 'ping'])
            ->name('ping');
        Route::post('/sync', [App\Http\Controllers\Api\OfflineSyncController::class, 'sync'])
            ->name('sync');
        Route::post('/ventes', [App\Http\Controllers\Api\OfflineSyncController::class, 'syncVente'])
            ->name('ventes.sync');
        Route::post('/clients', [App\Http\Controllers\Api\OfflineSyncController::class, 'syncClient'])
            ->name('clients.sync');
    });

    // Routes pour les paiements partiels
    Route::prefix('paiements')->name('paiements.')->group(function () {
        Route::post('/{vente}', [App\Http\Controllers\PaiementVenteController::class, 'store'])->name('store');
        Route::delete('/{paiementVente}', [App\Http\Controllers\PaiementVenteController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('produits')->name('produits.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProduitController::class, 'index'])->name('index');
        Route::get('/import', [App\Http\Controllers\ProduitImportController::class, 'create'])->name('import.create');
        Route::get('/import/modele', [App\Http\Controllers\ProduitImportController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/import/modele-csv', [App\Http\Controllers\ProduitImportController::class, 'downloadCsvTemplate'])->name('import.template.csv');
        Route::post('/import', [App\Http\Controllers\ProduitImportController::class, 'store'])->name('import.store');
        Route::get('/create', [App\Http\Controllers\ProduitController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ProduitController::class, 'store'])->name('store');
        // Routes spécifiques AVANT les routes avec paramètre générique
        Route::get('/{produit}/edit', [App\Http\Controllers\ProduitController::class, 'edit'])->name('edit');
        Route::post('/{produit}/ajuster-stock', [App\Http\Controllers\ProduitController::class, 'ajusterStock'])->name('ajuster-stock');
        // Routes avec paramètre générique en dernier
        Route::get('/{produit}', [App\Http\Controllers\ProduitController::class, 'show'])->name('show');
        Route::put('/{produit}', [App\Http\Controllers\ProduitController::class, 'update'])->name('update');
        Route::delete('/{produit}', [App\Http\Controllers\ProduitController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [App\Http\Controllers\StockController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\StockController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\StockController::class, 'store'])->name('store');
        Route::get('/{produit}', [App\Http\Controllers\StockController::class, 'show'])->name('show');
        Route::get('/{produit}/edit', [App\Http\Controllers\StockController::class, 'edit'])->name('edit');
        Route::put('/{produit}', [App\Http\Controllers\StockController::class, 'update'])->name('update');
        Route::delete('/{produit}', [App\Http\Controllers\StockController::class, 'destroy'])->name('destroy');
        Route::get('/historique/mouvements', [App\Http\Controllers\StockController::class, 'historique'])->name('historique');
        Route::get('/alertes/stock', [App\Http\Controllers\StockController::class, 'alertes'])->name('alertes');
    });

    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/', [App\Http\Controllers\DepenseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\DepenseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\DepenseController::class, 'store'])->name('store');
        Route::get('/{depense}', [App\Http\Controllers\DepenseController::class, 'show'])->name('show');
        Route::get('/{depense}/edit', [App\Http\Controllers\DepenseController::class, 'edit'])->name('edit');
        Route::put('/{depense}', [App\Http\Controllers\DepenseController::class, 'update'])->name('update');
        Route::delete('/{depense}', [App\Http\Controllers\DepenseController::class, 'destroy'])->name('destroy');
        Route::get('/statistiques/analyse', [App\Http\Controllers\DepenseController::class, 'statistiques'])->name('statistiques');
    });

    Route::prefix('factures')->name('factures.')->group(function () {
        Route::get('/', [App\Http\Controllers\FactureController::class, 'index'])->name('index');
        Route::get('/{facture}', [App\Http\Controllers\FactureController::class, 'show'])->name('show');
        Route::get('/{facture}/afficher', [App\Http\Controllers\FactureController::class, 'afficher'])->name('afficher');
        Route::get('/{facture}/telecharger', [App\Http\Controllers\FactureController::class, 'telecharger'])->name('telecharger');
        Route::post('/{facture}/envoyer-email', [App\Http\Controllers\FactureController::class, 'envoyerEmail'])->name('envoyer-email');
        Route::middleware('admin')->group(function () {
            Route::get('/{facture}/edit', [App\Http\Controllers\FactureController::class, 'edit'])->name('edit');
            Route::put('/{facture}', [App\Http\Controllers\FactureController::class, 'update'])->name('update');
            Route::delete('/{facture}', [App\Http\Controllers\FactureController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [App\Http\Controllers\RapportController::class, 'index'])->name('index');
        Route::get('/ventes', [App\Http\Controllers\RapportController::class, 'ventes'])->name('ventes');
        Route::get('/stock', [App\Http\Controllers\RapportController::class, 'stock'])->name('stock');
        Route::get('/depenses', [App\Http\Controllers\RapportController::class, 'depenses'])->name('depenses');
        Route::get('/financier', [App\Http\Controllers\RapportController::class, 'financier'])->name('financier');

        // Route de test PDF
        // Route de test - Protégée par authentification et uniquement en mode debug
        if (config('app.debug')) {
            Route::get('/test-pdf', [App\Http\Controllers\TestPdfController::class, 'test'])->name('test-pdf');
        }
    });

    // Routes des notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [App\Http\Controllers\NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/mark-read', [App\Http\Controllers\NotificationController::class, 'marquerLue'])->name('mark-read');
        Route::get('/api/count', [App\Http\Controllers\NotificationController::class, 'count'])->name('count');
    });

    Route::prefix('historique')->name('historique.')->group(function () {
        Route::prefix('ventes')->name('ventes.')->group(function () {
            Route::get('/', [App\Http\Controllers\HistoriqueVenteController::class, 'index'])->name('index');
            Route::get('/{historique}', [App\Http\Controllers\HistoriqueVenteController::class, 'show'])->name('show');
        });
    });

    Route::prefix('employes')->name('employes.')->middleware('admin')->group(function () {
        Route::get('/', [App\Http\Controllers\EmployeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\EmployeController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\EmployeController::class, 'store'])->name('store');
        Route::get('/{employe}', [App\Http\Controllers\EmployeController::class, 'show'])->name('show');
        Route::get('/{employe}/edit', [App\Http\Controllers\EmployeController::class, 'edit'])->name('edit');
        Route::put('/{employe}', [App\Http\Controllers\EmployeController::class, 'update'])->name('update');
        Route::delete('/{employe}', [App\Http\Controllers\EmployeController::class, 'destroy'])->name('destroy');
        Route::post('/{employe}/toggle-status', [App\Http\Controllers\EmployeController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{employe}/ventes', [App\Http\Controllers\EmployeController::class, 'ventes'])->name('ventes');
        Route::get('/{employe}/permissions', [App\Http\Controllers\EmployeController::class, 'permissions'])->name('permissions');
        Route::put('/{employe}/permissions', [App\Http\Controllers\EmployeController::class, 'updatePermissions'])->name('update-permissions');
    });

    // Routes pour la gestion des administrateurs (super admin seulement)
    Route::prefix('admins')->name('admins.')->middleware('super_admin')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AdminController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\AdminController::class, 'store'])->name('store');
        Route::get('/{admin}', [App\Http\Controllers\AdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [App\Http\Controllers\AdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [App\Http\Controllers\AdminController::class, 'update'])->name('update');
        Route::delete('/{admin}', [App\Http\Controllers\AdminController::class, 'destroy'])->name('destroy');
        Route::post('/{admin}/toggle-status', [App\Http\Controllers\AdminController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Clients routes
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ClientController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [App\Http\Controllers\ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [App\Http\Controllers\ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}', [App\Http\Controllers\ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [App\Http\Controllers\ClientController::class, 'destroy'])->name('destroy');
        Route::post('/{client}/toggle', [App\Http\Controllers\ClientController::class, 'toggle'])->name('toggle');
    });

    // Fournisseurs routes
    Route::prefix('fournisseurs')->name('fournisseurs.')->group(function () {
        Route::get('/', [App\Http\Controllers\FournisseurController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\FournisseurController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\FournisseurController::class, 'store'])->name('store');
        Route::get('/{fournisseur}', [App\Http\Controllers\FournisseurController::class, 'show'])->name('show');
        Route::get('/{fournisseur}/edit', [App\Http\Controllers\FournisseurController::class, 'edit'])->name('edit');
        Route::put('/{fournisseur}', [App\Http\Controllers\FournisseurController::class, 'update'])->name('update');
        Route::delete('/{fournisseur}', [App\Http\Controllers\FournisseurController::class, 'destroy'])->name('destroy');
        Route::post('/{fournisseur}/toggle', [App\Http\Controllers\FournisseurController::class, 'toggle'])->name('toggle');
    });

    // Notifications routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/marquer-lue', [App\Http\Controllers\NotificationController::class, 'marquerLue'])->name('marquer-lue');
        Route::post('/marquer-toutes-lues', [App\Http\Controllers\NotificationController::class, 'marquerToutesLues'])->name('marquer-toutes-lues');
        Route::get('/api', [App\Http\Controllers\NotificationController::class, 'api'])->name('api');
        Route::post('/verifier-stock', [App\Http\Controllers\NotificationController::class, 'verifierStock'])->name('verifier-stock');
    });

    // Routes pour la gestion des catégories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [App\Http\Controllers\CategoryController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CategoryController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [App\Http\Controllers\CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [App\Http\Controllers\CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [App\Http\Controllers\CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/toggle', [App\Http\Controllers\CategoryController::class, 'toggle'])->name('toggle');
    });

    // Routes pour les conditions générales d'utilisation
    Route::prefix('conditions-generales')->name('conditions-generales.')->group(function () {
        Route::get('/', [App\Http\Controllers\ConditionGeneraleController::class, 'index'])->name('index');
        Route::post('/accept', [App\Http\Controllers\ConditionGeneraleController::class, 'accept'])->name('accept');
    });

    // Routes pour les tickets/bugs (propriétaires de boutique uniquement)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [App\Http\Controllers\TicketController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\TicketController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [App\Http\Controllers\TicketController::class, 'show'])->name('show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
