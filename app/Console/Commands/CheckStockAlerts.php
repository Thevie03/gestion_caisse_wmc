<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-alerts {--force : Forcer la vérification même si déjà exécutée récemment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les alertes de stock et créer des notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des alertes de stock...');

        try {
            // Vérifier les alertes de stock
            NotificationService::verifierAlertesStock();

            $this->info('✅ Vérification des alertes de stock terminée avec succès');

            // Afficher les statistiques
            $this->displayStats();

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la vérification des alertes: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Afficher les statistiques des notifications
     */
    private function displayStats()
    {
        $totalNotifications = \App\Models\Notification::where('module', 'stock')->count();
        $notificationsNonLues = \App\Models\Notification::where('module', 'stock')
            ->where('lue', false)
            ->count();

        $this->line('');
        $this->info('📊 Statistiques des notifications de stock:');
        $this->line("   • Total des notifications: {$totalNotifications}");
        $this->line("   • Notifications non lues: {$notificationsNonLues}");

        if ($notificationsNonLues > 0) {
            $this->warn("   ⚠️  {$notificationsNonLues} notification(s) nécessitent votre attention");
        } else {
            $this->info("   ✅ Aucune notification en attente");
        }
    }
}
