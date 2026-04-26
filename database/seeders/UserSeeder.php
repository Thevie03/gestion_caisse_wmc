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

        User::updateOrCreate(
            ['email' => 'support@wmcci.com'],
            [
                'name' => 'Support WMC',
                'password' => Hash::make('Support@2026'),
                'role' => 'super_admin',
                'boutique_id' => null,
                'telephone' => '+221 33 000 00 00',
                'actif' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin.global@gestioncaisse.com'],
            [
                'name' => 'Administrateur Global',
                'password' => Hash::make('AdminGlobal@2025'),
                'role' => 'admin',
                'boutique_id' => null,
                'telephone' => '+221 33 999 99 99',
                'actif' => true,
            ]
        );

        if ($cosmetica) {
            User::updateOrCreate(
                ['email' => 'employe@cosmetica.com'],
                [
                    'name' => 'Employé Cosmetica',
                    'password' => Hash::make('Cosmetica@2025'),
                    'role' => 'employe',
                    'boutique_id' => $cosmetica->id,
                    'telephone' => '+221 33 111 11 11',
                    'actif' => true,
                ]
            );
        }

        if ($abaya) {
            User::updateOrCreate(
                ['email' => 'employe@abaya.com'],
                [
                    'name' => 'Employé Abaya',
                    'password' => Hash::make('Abaya@2025'),
                    'role' => 'employe',
                    'boutique_id' => $abaya->id,
                    'telephone' => '+221 33 222 22 22',
                    'actif' => true,
                ]
            );
        }
    }
}
