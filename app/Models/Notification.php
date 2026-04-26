<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Notification extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'titre', 'message', 'type', 'priorite', 'user_id', 'boutique_id', 'module', 'data', 'lue', 'lue_at', 'actif'
    ];

    // Activer l'assignation automatique de boutique_id
    protected $autoAssignBoutique = true;

    protected $casts = [
        'data' => 'array',
        'lue' => 'boolean',
        'lue_at' => 'datetime',
        'actif' => 'boolean',
    ];

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

    /**
     * Scopes
     */
    public function scopeNonLues($query)
    {
        return $query->where('lue', false);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopeParModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopePourAdmin($query, $userId = null)
    {
        // Pour le Super Admin, retourner uniquement les notifications globales (user_id = null)
        // qui sont spécifiquement pour la plateforme TheVie
        return $query->whereNull('user_id');
    }

    /**
     * Marquer comme lue
     */
    public function marquerCommeLue()
    {
        $this->update([
            'lue' => true,
            'lue_at' => now()
        ]);
    }

    /**
     * Accessor pour l'icône selon le type
     */
    public function getIconeAttribute()
    {
        $icones = [
            'info' => 'fas fa-info-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
            'success' => 'fas fa-check-circle'
        ];

        return $icones[$this->type] ?? 'fas fa-bell';
    }

    /**
     * Accessor pour la couleur selon le type
     */
    public function getCouleurAttribute()
    {
        $couleurs = [
            'info' => 'primary',
            'warning' => 'warning',
            'error' => 'danger',
            'success' => 'success'
        ];

        return $couleurs[$this->type] ?? 'secondary';
    }
}
