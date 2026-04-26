<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vente;
use App\Models\Facture;
use Illuminate\Support\Facades\DB;

class GenerateFactures extends Command
{
    protected $signature = 'app:generate-factures';
    protected $description = 'Generate invoices (factures) from existing sales (ventes)';

    public function handle()
    {
        $this->info('Génération des factures à partir des ventes existantes...');

        $ventes = Vente::whereDoesntHave('facture')->get();

        if ($ventes->count() === 0) {
            $this->info('Aucune vente sans facture trouvée.');
            return Command::SUCCESS;
        }

        $this->info("Trouvé {$ventes->count()} ventes sans facture.");

        $bar = $this->output->createProgressBar($ventes->count());
        $bar->start();

        foreach ($ventes as $vente) {
            DB::beginTransaction();
            try {
                // Générer le numéro de facture
                $numeroFacture = $this->genererNumeroFacture($vente->boutique_id);

                // Créer la facture
                Facture::create([
                    'vente_id' => $vente->id,
                    'numero_facture' => $numeroFacture,
                    'lien_pdf' => null, // Sera généré à la demande
                ]);

                DB::commit();
                $bar->advance();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Erreur lors de la création de la facture pour la vente {$vente->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Factures générées avec succès !');
        $this->info("Total factures créées: {$ventes->count()}");

        return Command::SUCCESS;
    }

    private function genererNumeroFacture($boutiqueId = null)
    {
        $prefixe = 'FAC';
        $annee = date('Y');
        $mois = date('m');

        // Compter les factures de ce mois
        $query = Facture::whereYear('created_at', $annee)
                     ->whereMonth('created_at', $mois);

        if ($boutiqueId) {
            $query->whereHas('vente', function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            });
        }

        $nombre = $query->count() + 1;

        return $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
    }
}
