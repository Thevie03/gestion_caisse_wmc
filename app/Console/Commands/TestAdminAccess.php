<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test admin access and users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== Test d'accès administrateur ===");

        // Vérifier les utilisateurs
        $users = User::all();
        $this->info("Nombre d'utilisateurs: " . $users->count());

        foreach ($users as $user) {
            $this->info("- {$user->name} ({$user->email}) - Role: {$user->role}");
        }

        // Vérifier l'utilisateur admin
        $admin = User::where('email', 'admin@test.com')->first();
        if ($admin) {
            $this->info("✅ Utilisateur admin trouvé: {$admin->name}");
            $this->info("   - Role: {$admin->role}");
            $this->info("   - isAdmin(): " . ($admin->isAdmin() ? 'OUI' : 'NON'));
        } else {
            $this->error("❌ Utilisateur admin non trouvé");
        }

        return Command::SUCCESS;
    }
}
