<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueModificationVente extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'historique_modifications_ventes';

    protected $fillable = [
        'type_action',
        'vente_id',
        'numero_vente',
        'user_id',
        'boutique_id',
        'donnees_avant',
        'donnees_apres',
        'changements',
        'produits_modifies',
        'motif',
    ];

    protected bool $autoAssignBoutique = true;

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
        'produits_modifies' => 'array',
    ];

    /**
     * Résoudre le binding de route en ignorant le scope tenant si nécessaire
     * et en vérifiant les autorisations
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $user = auth()->user();

        // Désactiver temporairement le scope tenant pour trouver l'historique
        $historique = static::withoutGlobalScope('tenant')
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();

        if (!$historique) {
            return null;
        }

        // Vérifier les autorisations
        if ($user) {
            // Les admins peuvent accéder à tous les historiques
            if ($user->isAdmin()) {
                return $historique;
            }

            // Les propriétaires peuvent accéder aux historiques de leurs boutiques
            if ($user->isOwner() && $historique->boutique_id === $user->boutique_id) {
                return $historique;
            }
        }

        // Si aucune autorisation, retourner null (générera une 404)
        return null;
    }

    /**
     * Relations
     */
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Scope pour filtrer par type d'action
     */
    public function scopeTypeAction($query, $type)
    {
        return $query->where('type_action', $type);
    }

    /**
     * Scope pour les modifications
     */
    public function scopeModifications($query)
    {
        return $query->where('type_action', 'modification');
    }

    /**
     * Scope pour les suppressions
     */
    public function scopeSuppressions($query)
    {
        return $query->where('type_action', 'suppression');
    }
}
