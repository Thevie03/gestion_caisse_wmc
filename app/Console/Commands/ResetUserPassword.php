<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email : Email de l\'utilisateur} {password : Nouveau mot de passe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialiser le mot de passe d\'un utilisateur';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Utilisateur introuvable pour l'email {$email}");
            return Command::FAILURE;
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        $this->info("Mot de passe mis à jour pour {$email}");

        return Command::SUCCESS;
    }
}
