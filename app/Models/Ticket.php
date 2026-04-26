<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'boutique_id',
        'sujet',
        'message',
        'statut',
        'priorite',
        'reponse',
        'reponse_at',
        'reponse_par',
    ];

    protected bool $autoAssignBoutique = true;

    protected $casts = [
        'reponse_at' => 'datetime',
    ];

    // Constantes pour les statuts
    const STATUT_OUVERT = 'ouvert';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_RESOLU = 'resolu';
    const STATUT_FERME = 'ferme';

    // Constantes pour les priorités
    const PRIORITE_FAIBLE = 'faible';
    const PRIORITE_NORMALE = 'normale';
    const PRIORITE_ELEVEE = 'elevee';
    const PRIORITE_URGENTE = 'urgente';

    /**
     * Relation avec l'utilisateur qui a créé le ticket
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la boutique
     */
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Relation avec l'utilisateur qui a répondu
     */
    public function repondPar()
    {
        return $this->belongsTo(User::class, 'reponse_par');
    }

    /**
     * Vérifier si le ticket est ouvert
     */
    public function isOuvert()
    {
        return $this->statut === self::STATUT_OUVERT;
    }

    /**
     * Vérifier si le ticket est résolu
     */
    public function isResolu()
    {
        return $this->statut === self::STATUT_RESOLU;
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            self::STATUT_OUVERT => 'Ouvert',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_RESOLU => 'Résolu',
            self::STATUT_FERME => 'Fermé',
            default => $this->statut,
        };
    }

    /**
     * Obtenir le label de la priorité
     */
    public function getPrioriteLabelAttribute()
    {
        return match($this->priorite) {
            self::PRIORITE_FAIBLE => 'Faible',
            self::PRIORITE_NORMALE => 'Normale',
            self::PRIORITE_ELEVEE => 'Élevée',
            self::PRIORITE_URGENTE => 'Urgente',
            default => $this->priorite,
        };
    }

    /**
     * Obtenir la classe CSS pour le badge de priorité
     */
    public function getPrioriteBadgeClassAttribute()
    {
        return match($this->priorite) {
            self::PRIORITE_FAIBLE => 'bg-secondary',
            self::PRIORITE_NORMALE => 'bg-info',
            self::PRIORITE_ELEVEE => 'bg-warning',
            self::PRIORITE_URGENTE => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
