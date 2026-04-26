<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville',
        'code_postal', 'pays', 'date_naissance', 'sexe', 'notes',
        'solde_points', 'actif', 'user_id', 'boutique_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'solde_points' => 'decimal:2',
        'actif' => 'boolean',
    ];

    /**
     * Relations
     */
    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return trim($this->prenom . ' ' . ($this->nom ?? ''));
    }

    /**
     * Scope pour les clients actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour la recherche
     */
    public function scopeRecherche($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nom', 'like', "%{$term}%")
              ->orWhere('prenom', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('telephone', 'like', "%{$term}%");
        });
    }
}
