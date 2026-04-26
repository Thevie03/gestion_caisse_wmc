<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index pour les boutiques
        Schema::table('boutiques', function (Blueprint $table) {
            if (!$this->indexExists('boutiques', 'boutiques_actif_index')) {
                $table->index('actif', 'boutiques_actif_index');
            }
            if (!$this->indexExists('boutiques', 'boutiques_owner_id_index')) {
                $table->index('owner_id', 'boutiques_owner_id_index');
            }
        });

        // Index pour les dépenses
        Schema::table('depenses', function (Blueprint $table) {
            if (!$this->indexExists('depenses', 'depenses_boutique_date_index')) {
                $table->index(['boutique_id', 'date_depense'], 'depenses_boutique_date_index');
            }
        });

        // Index pour les utilisateurs
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_boutique_id_index')) {
                $table->index('boutique_id', 'users_boutique_id_index');
            }
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
        });

        // Index pour les notifications
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->indexExists('notifications', 'notifications_user_id_index')) {
                $table->index('user_id', 'notifications_user_id_index');
            }
            if (!$this->indexExists('notifications', 'notifications_lue_created_index')) {
                $table->index(['lue', 'created_at'], 'notifications_lue_created_index');
            }
        });

        // Index pour les catégories
        Schema::table('categories', function (Blueprint $table) {
            if (!$this->indexExists('categories', 'categories_boutique_id_index')) {
                $table->index('boutique_id', 'categories_boutique_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropIndex('boutiques_actif_index');
            $table->dropIndex('boutiques_owner_id_index');
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->dropIndex('depenses_boutique_date_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_boutique_id_index');
            $table->dropIndex('users_role_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_id_index');
            $table->dropIndex('notifications_lue_created_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_boutique_id_index');
        });
    }

    /**
     * Vérifier si un index existe déjà
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


