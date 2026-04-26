<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modèle de CGU avec mention de la limite de 2 employés
        $cguDefault = '<h3>Conditions Générales d\'Utilisation</h3>

<h4>1. Gestion des Employés</h4>
<p>Chaque administrateur de boutique peut créer et gérer un maximum de <strong>2 employés</strong> pour sa boutique. Cette limite est fixe et ne peut être dépassée.</p>

<h4>2. Responsabilités</h4>
<p>L\'administrateur de boutique est responsable de la gestion de ses employés et de leurs accès au système.</p>

<h4>3. Utilisation du Service</h4>
<p>L\'utilisation de ce service est soumise au respect des conditions générales d\'utilisation et des règles de bon usage.</p>';

        // Mettre à jour les CGU seulement si elles sont vides
        $cguParametre = DB::table('parametres_systeme')
            ->where('cle', 'cgu')
            ->first();

        if ($cguParametre && (empty($cguParametre->valeur) || trim($cguParametre->valeur) === '')) {
            DB::table('parametres_systeme')
                ->where('cle', 'cgu')
                ->update([
                    'valeur' => $cguDefault,
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Ne rien faire en cas de rollback pour préserver les CGU personnalisées
    }
};
