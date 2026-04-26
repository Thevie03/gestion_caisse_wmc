<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'nom',
        'icone',
        'couleur',
        'description',
        'active',
        'boutique_id',
    ];

    protected bool $autoAssignBoutique = true;

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relation avec les produits
     */
    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorie', 'nom')
            ->when($this->boutique_id, function ($query) {
                $query->where('boutique_id', $this->boutique_id);
            });
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Scope pour les catégories actives
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Obtenir l'icône formatée
     */
    public function getIconeFormateeAttribute()
    {
        return $this->icone ?: 'fas fa-tag';
    }

    /**
     * Obtenir la couleur formatée
     */
    public function getCouleurFormateeAttribute()
    {
        return $this->couleur ?: 'secondary';
    }
}
