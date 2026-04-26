<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'code', 'taux', 'description', 'inclus_prix', 'actif'
    ];

    protected $casts = [
        'taux' => 'decimal:2',
        'inclus_prix' => 'boolean',
        'actif' => 'boolean',
    ];

    /**
     * Scope pour les taxes actives
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Calculer le montant de la taxe
     */
    public function calculerMontant($montant)
    {
        return ($montant * $this->taux) / 100;
    }

    /**
     * Calculer le prix avec taxe
     */
    public function calculerPrixAvecTaxe($prixHorsTaxe)
    {
        if ($this->inclus_prix) {
            return $prixHorsTaxe; // Le prix inclut déjà la taxe
        }
        
        return $prixHorsTaxe + $this->calculerMontant($prixHorsTaxe);
    }

    /**
     * Calculer le prix hors taxe
     */
    public function calculerPrixHorsTaxe($prixAvecTaxe)
    {
        if ($this->inclus_prix) {
            return $prixAvecTaxe / (1 + ($this->taux / 100));
        }
        
        return $prixAvecTaxe;
    }

    /**
     * Accessor pour le taux formaté
     */
    public function getTauxFormateAttribute()
    {
        return number_format($this->taux, 2) . '%';
    }
}
