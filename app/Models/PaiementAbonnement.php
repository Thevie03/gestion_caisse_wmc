<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementAbonnement extends Model
{
    use HasFactory;

    protected $table = 'paiements_abonnements';

    protected $fillable = [
        'abonnement_id',
        'user_id',
        'montant',
        'mode_paiement',
        'statut',
        'date_paiement',
        'reference',
        'notes',
        'confirme_par',
        'confirme_le',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
        'confirme_le' => 'datetime',
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_CONFIRME = 'confirme';
    public const STATUT_REFUSE = 'refuse';
    public const STATUT_REMBOURSE = 'rembourse';

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmePar()
    {
        return $this->belongsTo(User::class, 'confirme_par');
    }

    public function scopeConfirmes($query)
    {
        return $query->where('statut', self::STATUT_CONFIRME);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }
}
