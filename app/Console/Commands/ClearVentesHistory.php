<?php

namespace App\Console\Commands;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Facture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearVentesHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ventes:clear {--force : Force la suppression sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime toutes les données de l\'historique de ventes (ventes, détails de ventes et factures associées)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== SUPPRESSION DE L\'HISTORIQUE DE VENTES ===');
        $this->warn('⚠️  ATTENTION: Cette opération va supprimer toutes les ventes et leurs données associées !');

        if (!$this->option('force') && !$this->confirm('Voulez-vous continuer ?')) {
            $this->info('Opération annulée.');
            return Command::FAILURE;
        }

        try {
            DB::beginTransaction();

            // Compter les données avant suppression
            $countFactures = Facture::count();
            $countDetails = VenteDetail::count();
            $countVentes = Vente::count();

            $this->info("\nDonnées à supprimer :");
            $this->line("- Factures : {$countFactures}");
            $this->line("- Détails de ventes : {$countDetails}");
            $this->line("- Ventes : {$countVentes}");

            // Supprimer dans l'ordre pour éviter les erreurs de contraintes
            // Note: Les migrations utilisent onDelete('cascade'), donc la suppression des ventes
            // devrait automatiquement supprimer les factures et détails, mais on les supprime
            // explicitement pour être sûr et avoir un meilleur contrôle

            $this->info("\nSuppression des factures...");
            Facture::truncate();
            $this->info("✅ {$countFactures} facture(s) supprimée(s)");

            $this->info("Suppression des détails de ventes...");
            VenteDetail::truncate();
            $this->info("✅ {$countDetails} détail(s) de vente(s) supprimé(s)");

            $this->info("Suppression des ventes...");
            Vente::truncate();
            $this->info("✅ {$countVentes} vente(s) supprimée(s)");

            DB::commit();

            $this->info("\n=== VÉRIFICATION FINALE ===");
            $this->line("Factures restantes : " . Facture::count());
            $this->line("Détails de ventes restants : " . VenteDetail::count());
            $this->line("Ventes restantes : " . Vente::count());

            $this->info("\n✅ SUPPRESSION TERMINÉE AVEC SUCCÈS !");
            $this->info("L'historique de ventes a été entièrement supprimé.");
            $this->info("Le code et les autres données (produits, clients, etc.) sont intacts.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ ERREUR lors de la suppression : " . $e->getMessage());
            $this->error("Trace : " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}

