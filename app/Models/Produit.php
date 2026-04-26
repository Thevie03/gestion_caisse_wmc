<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $fillable = [
        'nom',
        'categorie',
        'description',
        'prix_achat',
        'prix_vente',
        'quantite_stock',
        'stock_minimum',
        'code_produit',
        'barcode',
        'image',
        'boutique_id',
        'fournisseur_id',
        'user_id',
        'actif',
    ];

    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'actif' => 'boolean',
    ];

    /**
     * Relations
     */
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function venteDetails()
    {
        return $this->hasMany(VenteDetail::class);
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    /**
     * Méthodes helper
     */
    public function isStockFaible()
    {
        return $this->quantite_stock <= $this->stock_minimum && $this->quantite_stock > 0;
    }

    public function isEnRupture()
    {
        return $this->quantite_stock == 0;
    }

    public function getStatutStockAttribute()
    {
        if ($this->isEnRupture()) {
            return 'rupture';
        } elseif ($this->isStockFaible()) {
            return 'faible';
        } else {
            return 'disponible';
        }
    }


    /**
     * Calculer la marge
     */
    public function getMargeAttribute()
    {
        return $this->prix_vente - $this->prix_achat;
    }

    /**
     * Calculer le pourcentage de marge
     */
    public function getMargePourcentageAttribute()
    {
        if ($this->prix_achat > 0) {
            return (($this->prix_vente - $this->prix_achat) / $this->prix_achat) * 100;
        }
        return 0;
    }
}
