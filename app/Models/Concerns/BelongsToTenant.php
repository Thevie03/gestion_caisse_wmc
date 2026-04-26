<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            $context = app(TenantContext::class);
            // Vérifier si user_id existe dans le fillable ou dans la table avant de l'assigner
            if ($context->userId() && empty($model->user_id) && static::hasTenantColumnForModel($model, 'user_id')) {
                $model->user_id = $context->userId();
            }
            if (property_exists($model, 'autoAssignBoutique') && $model->autoAssignBoutique && empty($model->boutique_id)) {
                $model->boutique_id = $context->boutiqueId();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(TenantContext::class);

            if (!$context->shouldScope()) {
                return;
            }

            // Priorité à la boutique pour partager les données entre administrateur et employés d'une même boutique
            if (static::hasTenantColumn($builder, 'boutique_id') && $context->boutiqueId()) {
                $builder->where($builder->getModel()->getTable() . '.boutique_id', $context->boutiqueId());
            } elseif (static::hasTenantColumn($builder, 'user_id')) {
                $builder->where($builder->getModel()->getTable() . '.user_id', $context->userId());
            }
        });
    }

    protected static function hasTenantColumn(Builder $builder, string $column): bool
    {
        return in_array($column, $builder->getModel()->getFillable(), true)
            || array_key_exists($column, $builder->getModel()->getCasts());
    }

    /**
     * Vérifier si une colonne tenant existe pour un modèle donné
     * Utilisé lors de la création pour éviter d'assigner des colonnes inexistantes
     */
    protected static function hasTenantColumnForModel($model, string $column): bool
    {
        return in_array($column, $model->getFillable(), true)
            || array_key_exists($column, $model->getCasts())
            || Schema::hasColumn($model->getTable(), $column);
    }
}


