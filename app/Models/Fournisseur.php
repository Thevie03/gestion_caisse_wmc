<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $fillable = [
        'nom', 'contact_nom', 'email', 'telephone', 'adresse', 'ville',
        'code_postal', 'pays', 'site_web', 'notes', 'solde_compte', 'actif',
        'user_id', 'boutique_id'
    ];

    protected $casts = [
        'solde_compte' => 'decimal:2',
        'actif' => 'boolean',
    ];

    /**
     * Relations
     */
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Scope pour les fournisseurs actifs
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
              ->orWhere('contact_nom', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('telephone', 'like', "%{$term}%");
        });
    }
}
