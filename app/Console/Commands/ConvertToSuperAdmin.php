<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ConvertToSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'super-admin:convert {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convertir un utilisateur existant en super administrateur';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Aucun utilisateur trouvé avec l'email: {$email}");
            return Command::FAILURE;
        }

        if ($user->role === 'super_admin') {
            $this->info("ℹ️  L'utilisateur {$email} est déjà un super administrateur.");
            return Command::SUCCESS;
        }

        try {
            $oldRole = $user->role;
            $user->update([
                'role' => 'super_admin'
            ]);

            $this->info("✅ L'utilisateur {$email} a été converti en super administrateur.");
            $this->info("   Ancien rôle: {$oldRole}");
            $this->info("   Nouveau rôle: super_admin");
            $this->info("   Nom: {$user->name}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la conversion: " . $e->getMessage());
            $this->warn("Assurez-vous d'avoir exécuté la migration pour ajouter le rôle 'super_admin'.");
            return Command::FAILURE;
        }
    }
}






















