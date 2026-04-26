<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_id',
        'montant',
        'mode_paiement',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

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

    /**
     * Scope pour les paiements d'une vente
     */
    public function scopeParVente($query, $venteId)
    {
        return $query->where('vente_id', $venteId);
    }

    /**
     * Accessor pour le statut du paiement
     */
    public function getStatutAttribute()
    {
        return $this->montant > 0 ? 'complet' : 'partiel';
    }
}
