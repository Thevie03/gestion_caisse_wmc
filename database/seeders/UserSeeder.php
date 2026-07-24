<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Boutique;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cosmetica = Boutique::where('nom', 'Cosmetica')->first();
        $abaya = Boutique::where('nom', 'Maison des Abaya')->first();

        // Garantir un super admin unique
        User::where('role', 'super_admin')
            ->where('email', '!=', 'support@wmcci.com')
            ->update(['role' => 'admin']);

        $superAdminPassword = env('SUPER_ADMIN_PASSWORD');
        if (empty($superAdminPassword)) {
            $this->command?->warn('SUPER_ADMIN_PASSWORD non défini — super admin non créé. Utilisez : php artisan super-admin:create');
            return;
        }

        User::updateOrCreate(
            ['email' => 'support@wmcci.com'],
            [
                'name' => 'Support WMC',
                'password' => Hash::make($superAdminPassword),
                'role' => 'super_admin',
                'boutique_id' => null,
                'telephone' => '+221 33 000 00 00',
                'actif' => true,
            ]
        );

    }
}
