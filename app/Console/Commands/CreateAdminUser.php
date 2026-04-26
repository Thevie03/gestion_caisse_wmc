<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Boutique;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--email=admin@test.com} {--password=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for testing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->info("L'utilisateur admin existe déjà avec l'email: {$email}");
            return Command::SUCCESS;
        }

        // Créer l'utilisateur admin
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'boutique_id' => null,
            'telephone' => '0000000000',
            'actif' => true,
        ]);

        $this->info("Utilisateur admin créé avec succès !");
        $this->info("Email: {$email}");
        $this->info("Mot de passe: {$password}");
        $this->info("Vous pouvez maintenant vous connecter à l'application.");

        return Command::SUCCESS;
    }
}
