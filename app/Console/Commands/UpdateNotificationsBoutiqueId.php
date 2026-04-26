<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateNotificationsBoutiqueId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update-boutique-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour les notifications existantes avec la boutique_id basée sur le user_id';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Mise à jour des notifications avec boutique_id...');

        // Mettre à jour les notifications qui ont un user_id mais pas de boutique_id
        $notifications = \App\Models\Notification::whereNotNull('user_id')
            ->whereNull('boutique_id')
            ->get();

        $updated = 0;
        foreach ($notifications as $notification) {
            $user = \App\Models\User::find($notification->user_id);
            if ($user && $user->boutique_id) {
                $notification->update(['boutique_id' => $user->boutique_id]);
                $updated++;
            }
        }

        $this->info("{$updated} notifications mises à jour avec succès.");

        return Command::SUCCESS;
    }
}
