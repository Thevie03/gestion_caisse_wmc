<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $fillable = [
        'user_id',
        'boutique_id',
        'client_id',
        'total',
        'remise',
        'total_final',
        'montant_paye',
        'solde_restant',
        'statut_paiement',
        'mode_paiement',
        'numero_vente',
        'offline_uuid',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'remise' => 'decimal:2',
        'total_final' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'solde_restant' => 'decimal:2',
    ];

    /**
     * Résoudre le binding de route en ignorant le scope tenant si nécessaire
     * et en vérifiant les autorisations
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $user = auth()->user();
        
        // Désactiver temporairement le scope tenant pour trouver la vente
        $vente = static::withoutGlobalScope('tenant')
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
        
        if (!$vente) {
            return null;
        }
        
        // Vérifier les autorisations
        if ($user) {
            // Les admins peuvent accéder à toutes les ventes
            if ($user->isAdmin()) {
                return $vente;
            }
            
            // Les propriétaires et employés peuvent accéder aux ventes de leur boutique
            if (($user->isOwner() || $user->isEmploye()) && $vente->boutique_id === $user->boutique_id) {
                return $vente;
            }
        }
        
        // Si aucune autorisation, retourner null (générera une 404)
        return null;
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function venteDetails()
    {
        return $this->hasMany(VenteDetail::class);
    }

    // Alias pour la relation details
    public function details()
    {
        return $this->hasMany(VenteDetail::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    public function paiements()
    {
        return $this->hasMany(PaiementVente::class);
    }

    /**
     * Savoir si la vente possède plusieurs modes de paiement.
     */
    public function hasMultiplePaymentModes(): bool
    {
        return $this->paiements->pluck('mode_paiement')->unique()->count() > 1;
    }

    /**
     * Résumé des montants payés par mode.
     */
    public function getModesPaiementSummaryAttribute()
    {
        return $this->paiements
            ->groupBy('mode_paiement')
            ->map(function ($paiements) {
                return $paiements->sum('montant');
            });
    }

    /**
     * Mode de paiement principal (utilisé pour l'affichage historique).
     */
    public function getModePaiementPrincipalAttribute()
    {
        if ($this->paiements->isNotEmpty()) {
            return $this->paiements->sortBy('created_at')->first()->mode_paiement;
        }

        return $this->mode_paiement;
    }

    /**
     * Libellé du mode de paiement pour l'affichage (inclut le cas mixte).
     */
    public function getModePaiementDisplayAttribute()
    {
        $summary = $this->modes_paiement_summary;

        if ($summary->count() > 1) {
            return 'mixte';
        }

        return $summary->keys()->first() ?? $this->mode_paiement;
    }

    /**
     * Vérifier si la vente est complètement payée
     */
    public function isCompletementPaye()
    {
        return $this->solde_restant <= 0;
    }

    /**
     * Obtenir le montant total payé
     */
    public function getMontantTotalPaye()
    {
        return $this->paiements()->sum('montant');
    }

    /**
     * Obtenir le solde restant
     */
    public function getSoldeRestant()
    {
        return $this->total_final - $this->getMontantTotalPaye();
    }

    /**
     * Boot method pour générer le numéro de vente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vente) {
            if (empty($vente->numero_vente)) {
                // Format: V-YYYYMMDD-NNNN (exemple: V-20251029-0001)
                $date = date('Ymd');

                $lastVente = static::withoutGlobalScopes()
                    ->whereDate('created_at', today())
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastVente) {
                    $lastNumero = explode('-', $lastVente->numero_vente);
                    $lastNumber = isset($lastNumero[2]) ? (int) $lastNumero[2] : 0;
                    $numero = $lastNumber + 1;
                } else {
                    $numero = 1;
                }

                // Garantir l'unicité même si deux boutiques créent une vente simultanément
                do {
                    $candidate = 'V-' . $date . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
                    $numero++;
                } while (static::withoutGlobalScopes()->where('numero_vente', $candidate)->exists());

                $vente->numero_vente = $candidate;
            }
        });
    }
}
