<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'super-admin:create {--email=superadmin@test.com} {--password=password} {--name=Super Administrateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un utilisateur super administrateur';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $user = User::where('email', $email)->first();
            if ($user->role === 'super_admin') {
                $this->info("Un super administrateur existe déjà avec l'email: {$email}");
                return Command::SUCCESS;
            } else {
                // Convertir l'utilisateur existant en super admin
                $user->assignRole('super_admin');
                $this->info("L'utilisateur {$email} a été converti en super administrateur.");
                return Command::SUCCESS;
            }
        }

        // Vérifier si la colonne role supporte super_admin
        try {
            // Créer l'utilisateur super admin
            $superAdmin = User::createWithRole([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'boutique_id' => null,
                'telephone' => '+221 33 000 00 00',
                'actif' => true,
            ], 'super_admin');

            $this->info("✅ Super administrateur créé avec succès !");
            $this->info("Email: {$email}");
            $this->info("Mot de passe: {$password}");
            $this->info("Nom: {$name}");
            $this->info("Vous pouvez maintenant vous connecter à l'application.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création du super administrateur: " . $e->getMessage());
            $this->warn("Assurez-vous d'avoir exécuté la migration pour ajouter le rôle 'super_admin'.");
            return Command::FAILURE;
        }
    }
}






















