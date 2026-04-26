<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'boutique_id',
        'numero_contrat',
        'type_contrat',
        'date_signature',
        'date_expiration',
        'fichier_contrat',
        'statut',
        'montant',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_signature' => 'date',
        'date_expiration' => 'date',
        'montant' => 'decimal:2',
    ];

    /**
     * Relation avec la boutique
     */
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé le contrat
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Vérifier si le contrat est expiré
     */
    public function estExpire()
    {
        if (!$this->date_expiration) {
            return false;
        }
        return $this->date_expiration->isPast() && $this->statut !== 'resilie';
    }

    /**
     * Vérifier si le contrat est actif
     */
    public function estActif()
    {
        return $this->statut === 'actif' && !$this->estExpire();
    }

    /**
     * Générer un numéro de contrat unique
     */
    public static function genererNumeroContrat()
    {
        $prefix = 'CTR-' . date('Y') . '-';
        $lastContrat = self::where('numero_contrat', 'like', $prefix . '%')
            ->orderBy('numero_contrat', 'desc')
            ->first();

        if ($lastContrat) {
            $lastNumber = (int) substr($lastContrat->numero_contrat, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
