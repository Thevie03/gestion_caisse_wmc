<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tenant_key')) {
                $table->uuid('tenant_key')->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['actif', 'expire', 'suspendu'])
                    ->default('actif')
                    ->after('actif');
            }

            if (!Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            }

            if (!Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_expires_at');
            }
        });

        Schema::table('boutiques', function (Blueprint $table) {
            if (!Schema::hasColumn('boutiques', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('boutiques', 'devise')) {
                $table->string('devise', 10)->default('FCFA')->after('email');
            }
        });

        Schema::table('produits', function (Blueprint $table) {
            if (!Schema::hasColumn('produits', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('boutique_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('clients', 'boutique_id')) {
                $table->foreignId('boutique_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        Schema::table('fournisseurs', function (Blueprint $table) {
            if (!Schema::hasColumn('fournisseurs', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('fournisseurs', 'boutique_id')) {
                $table->foreignId('boutique_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('boutiques')
                    ->nullOnDelete();
            }
        });

        $this->backfillTenantKeys();
    }

    protected function backfillTenantKeys(): void
    {
        DB::table('users')
            ->whereNull('tenant_key')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'tenant_key' => (string) \Illuminate\Support\Str::uuid(),
                        ]);
                }
            });

        $defaultOwnerId = $this->resolveDefaultOwnerId();

        DB::table('produits')
            ->whereNull('user_id')
            ->whereNotNull('boutique_id')
            ->chunkById(200, function ($produits) use ($defaultOwnerId) {
                foreach ($produits as $produit) {
                    $owner = DB::table('users')
                        ->where('boutique_id', $produit->boutique_id)
                        ->orderBy('role', 'desc')
                        ->first();

                    if ($owner) {
                        DB::table('produits')
                            ->where('id', $produit->id)
                            ->update(['user_id' => $owner->id]);
                    } elseif ($defaultOwnerId) {
                        DB::table('produits')
                            ->where('id', $produit->id)
                            ->update(['user_id' => $defaultOwnerId]);
                    }
                }
            });

        DB::table('boutiques')
            ->whereNull('owner_id')
            ->chunkById(200, function ($boutiques) use ($defaultOwnerId) {
                foreach ($boutiques as $boutique) {
                    $ownerId = DB::table('users')
                        ->where('boutique_id', $boutique->id)
                        ->orderBy('role', 'desc')
                        ->value('id') ?? $defaultOwnerId;

                    if ($ownerId) {
                        DB::table('boutiques')
                            ->where('id', $boutique->id)
                            ->update(['owner_id' => $ownerId]);
                    }
                }
            });

        if ($defaultOwnerId) {
            foreach (['clients', 'fournisseurs'] as $table) {
                DB::table($table)
                    ->whereNull('user_id')
                    ->update(['user_id' => $defaultOwnerId]);
            }
        }
    }

    protected function resolveDefaultOwnerId(): ?int
    {
        return DB::table('users')
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->value('id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            if (Schema::hasColumn('fournisseurs', 'boutique_id')) {
                $table->dropConstrainedForeignId('boutique_id');
            }
            if (Schema::hasColumn('fournisseurs', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'boutique_id')) {
                $table->dropConstrainedForeignId('boutique_id');
            }
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('produits', function (Blueprint $table) {
            if (Schema::hasColumn('produits', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('boutiques', function (Blueprint $table) {
            if (Schema::hasColumn('boutiques', 'devise')) {
                $table->dropColumn('devise');
            }
            if (Schema::hasColumn('boutiques', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
            if (Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->dropColumn('subscription_expires_at');
            }
            if (Schema::hasColumn('users', 'subscription_status')) {
                $table->dropColumn('subscription_status');
            }
            if (Schema::hasColumn('users', 'tenant_key')) {
                $table->dropUnique('users_tenant_key_unique');
                $table->dropColumn('tenant_key');
            }
        });
    }
};
