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
        Schema::create('parametres_systeme', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('parametres_systeme')->insert([
            ['cle' => 'app_nom', 'valeur' => config('app.name', 'GestionCaisse'), 'type' => 'string', 'description' => 'Nom de l\'application', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'app_logo', 'valeur' => 'images/logos/logo_thevie.png', 'type' => 'string', 'description' => 'Logo de l\'application', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'devise_defaut', 'valeur' => 'FCFA', 'type' => 'string', 'description' => 'Devise par défaut', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'periode_essai_jours', 'valeur' => '30', 'type' => 'integer', 'description' => 'Durée de la période d\'essai en jours', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'rappel_abonnement_jours', 'valeur' => '7', 'type' => 'integer', 'description' => 'Nombre de jours avant expiration pour envoyer un rappel', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'message_rappel_abonnement', 'valeur' => 'Votre abonnement expire bientôt. Veuillez le renouveler pour continuer à utiliser nos services.', 'type' => 'string', 'description' => 'Message de rappel d\'abonnement', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'cgu', 'valeur' => '<h3>Conditions Générales d\'Utilisation</h3>

<h4>1. Gestion des Employés</h4>
<p>Chaque administrateur de boutique peut créer et gérer un maximum de <strong>2 employés</strong> pour sa boutique. Cette limite est fixe et ne peut être dépassée.</p>

<h4>2. Responsabilités</h4>
<p>L\'administrateur de boutique est responsable de la gestion de ses employés et de leurs accès au système.</p>

<h4>3. Utilisation du Service</h4>
<p>L\'utilisation de ce service est soumise au respect des conditions générales d\'utilisation et des règles de bon usage.</p>', 'type' => 'string', 'description' => 'Conditions générales d\'utilisation', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parametres_systeme');
    }
};
