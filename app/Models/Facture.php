<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_id',
        'numero_facture',
        'lien_pdf',
    ];

    /**
     * Relations
     */
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function details()
    {
        return $this->vente->details();
    }

    public function boutique()
    {
        return $this->vente->boutique();
    }

    public function user()
    {
        return $this->vente->user();
    }

    /**
     * Accesseurs pour faciliter l'accès aux attributs de la vente
     */
    public function getModePaiementAttribute()
    {
        return $this->vente->mode_paiement ?? null;
    }

    public function getTotalAttribute()
    {
        return $this->vente->total ?? 0;
    }

    public function getNomClientAttribute()
    {
        return $this->vente->client ? $this->vente->client->nom_complet : null;
    }

    public function getTelephoneClientAttribute()
    {
        return $this->vente->client->telephone ?? null;
    }

    public function getEmailClientAttribute()
    {
        return $this->vente->client->email ?? null;
    }

    public function getRemiseGlobaleAttribute()
    {
        return $this->vente->remise ?? 0;
    }
}
