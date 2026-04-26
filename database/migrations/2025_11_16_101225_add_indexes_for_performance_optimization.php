<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventes', function (Blueprint $table) {
            // Index composites pour les requêtes fréquentes
            if (!$this->indexExists('ventes', 'ventes_boutique_created_index')) {
                $table->index(['boutique_id', 'created_at'], 'ventes_boutique_created_index');
            }
            if (!$this->indexExists('ventes', 'ventes_mode_paiement_index')) {
                $table->index('mode_paiement', 'ventes_mode_paiement_index');
            }
            if (!$this->indexExists('ventes', 'ventes_statut_paiement_index')) {
                $table->index('statut_paiement', 'ventes_statut_paiement_index');
            }
            if (!$this->indexExists('ventes', 'ventes_client_id_index')) {
                $table->index('client_id', 'ventes_client_id_index');
            }
        });

        Schema::table('produits', function (Blueprint $table) {
            // Index pour les recherches fréquentes
            if (!$this->indexExists('produits', 'produits_boutique_categorie_index')) {
                $table->index(['boutique_id', 'categorie'], 'produits_boutique_categorie_index');
            }
            if (!$this->indexExists('produits', 'produits_stock_status_index')) {
                $table->index(['quantite_stock', 'stock_minimum'], 'produits_stock_status_index');
            }
            if (!$this->indexExists('produits', 'produits_code_produit_index')) {
                $table->index('code_produit', 'produits_code_produit_index');
            }
        });

        Schema::table('vente_details', function (Blueprint $table) {
            // Index pour les jointures fréquentes
            if (!$this->indexExists('vente_details', 'vente_details_vente_produit_index')) {
                $table->index(['vente_id', 'produit_id'], 'vente_details_vente_produit_index');
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            // Index pour les recherches de clients
            if (!$this->indexExists('clients', 'clients_nom_prenom_index')) {
                $table->index(['nom', 'prenom'], 'clients_nom_prenom_index');
            }
            if (!$this->indexExists('clients', 'clients_telephone_index')) {
                $table->index('telephone', 'clients_telephone_index');
            }
            if (!$this->indexExists('clients', 'clients_email_index')) {
                $table->index('email', 'clients_email_index');
            }
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            // Index pour l'historique des mouvements
            if (!$this->indexExists('mouvements_stock', 'mouvements_stock_produit_created_index')) {
                $table->index(['produit_id', 'created_at'], 'mouvements_stock_produit_created_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropIndex('ventes_boutique_created_index');
            $table->dropIndex('ventes_mode_paiement_index');
            $table->dropIndex('ventes_statut_paiement_index');
            $table->dropIndex('ventes_client_id_index');
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropIndex('produits_boutique_categorie_index');
            $table->dropIndex('produits_stock_status_index');
            $table->dropIndex('produits_code_produit_index');
        });

        Schema::table('vente_details', function (Blueprint $table) {
            $table->dropIndex('vente_details_vente_produit_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_nom_prenom_index');
            $table->dropIndex('clients_telephone_index');
            $table->dropIndex('clients_email_index');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropIndex('mouvements_stock_produit_created_index');
        });
    }

    /**
     * Vérifier si un index existe déjà (simplifié)
     */
    private function indexExists($table, $indexName)
    {
        try {
            $connection = Schema::getConnection();
            $indexes = $connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
