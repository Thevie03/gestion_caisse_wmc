<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_abonnement',
        'date_debut',
        'date_expiration',
        'statut',
        'montant',
        'notes',
        'derniere_notification',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_expiration' => 'date',
        'derniere_notification' => 'datetime',
        'montant' => 'decimal:2',
    ];

    public const TYPE_MENSUEL = 'mensuel';
    public const TYPE_TRIMESTRIEL = 'trimestriel';
    public const TYPE_SEMESTRIEL = 'semestriel';
    public const TYPE_ANNUEL = 'annuel';
    public const TYPE_ACQUISITION_DEFINITIVE = 'acquisition_definitive';

    public const STATUT_ACTIF = 'actif';
    public const STATUT_EXPIRE = 'expire';
    public const STATUT_SUSPENDU = 'suspendu';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paiements()
    {
        return $this->hasMany(PaiementAbonnement::class);
    }

    public function paiementConfirme()
    {
        return $this->hasOne(PaiementAbonnement::class)->where('statut', PaiementAbonnement::STATUT_CONFIRME);
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', self::STATUT_ACTIF)
            ->where(function($q) {
                $q->where('type_abonnement', self::TYPE_ACQUISITION_DEFINITIVE)
                  ->orWhereDate('date_expiration', '>=', now());
            });
    }

    /**
     * Calcule le montant total payé pour cet abonnement (paiements confirmés uniquement)
     */
    public function getMontantPayeAttribute(): float
    {
        return (float) $this->paiements()
            ->where('statut', PaiementAbonnement::STATUT_CONFIRME)
            ->sum('montant');
    }

    /**
     * Calcule le montant restant à payer
     */
    public function getMontantRestantAttribute(): float
    {
        return max(0, (float) $this->montant - $this->montant_paye);
    }

    /**
     * Vérifie si l'abonnement est entièrement payé
     */
    public function estEntierementPaye(): bool
    {
        return $this->montant_restant <= 0.01; // Tolérance de 0.01 pour les erreurs d'arrondi
    }

    /**
     * Vérifie si l'abonnement est expiré
     */
    public function estExpire(): bool
    {
        // L'acquisition définitive n'expire jamais
        if ($this->type_abonnement === self::TYPE_ACQUISITION_DEFINITIVE) {
            return $this->statut === self::STATUT_EXPIRE;
        }
        return $this->date_expiration->isPast() || $this->statut === self::STATUT_EXPIRE;
    }

    /**
     * Vérifie si l'abonnement peut recevoir un nouveau paiement
     */
    public function peutRecevoirPaiement(): bool
    {
        // Un abonnement peut recevoir un paiement s'il n'est pas entièrement payé
        // ou s'il est expiré (pour renouvellement)
        return !$this->estEntierementPaye() || $this->estExpire();
    }

    /**
     * Obtenir le libellé formaté du type d'abonnement
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type_abonnement) {
            self::TYPE_MENSUEL => 'Mensuel',
            self::TYPE_TRIMESTRIEL => 'Trimestriel',
            self::TYPE_SEMESTRIEL => 'Semestriel',
            self::TYPE_ANNUEL => 'Annuel',
            self::TYPE_ACQUISITION_DEFINITIVE => 'Acquisition Définitive',
            default => ucfirst($this->type_abonnement),
        };
    }

    /**
     * Récupère le dernier paiement confirmé
     */
    public function dernierPaiementConfirme()
    {
        return $this->paiements()
            ->where('statut', PaiementAbonnement::STATUT_CONFIRME)
            ->latest('date_paiement')
            ->first();
    }
}

