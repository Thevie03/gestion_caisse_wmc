<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Boutique;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Créer les boutiques si elles n'existent pas
        if (Boutique::count() == 0) {
            Boutique::create([
                'nom' => 'Cosmetica',
                'description' => 'Boutique de produits cosmétiques',
                'actif' => true
            ]);

            Boutique::create([
                'nom' => 'Maison des Abaya',
                'description' => 'Boutique de vêtements (abayas)',
                'actif' => true
            ]);

            $this->info('Boutiques créées');
        }

        // Créer l'admin s'il n'existe pas
        if (!User::where('email', 'admin@test.com')->exists()) {
            User::createWithRole([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'telephone' => '+221 33 000 00 00',
                'actif' => true,
            ], 'admin');
            $this->info('Admin créé: admin@test.com / password');
        } else {
            $this->info('Admin existe déjà');
        }

        // Créer un employé de test
        if (!User::where('email', 'employe@test.com')->exists()) {
            $boutique = Boutique::first();
            User::createWithRole([
                'name' => 'Employé Test',
                'email' => 'employe@test.com',
                'password' => Hash::make('password'),
                'boutique_id' => $boutique->id,
                'telephone' => '+221 33 111 11 11',
                'actif' => true,
            ], 'employe');
            $this->info('Employé créé: employe@test.com / password');
        } else {
            $this->info('Employé existe déjà');
        }

        $this->info('Utilisateurs de test créés avec succès !');
    }
}
