<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boutique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'adresse',
        'telephone',
        'email',
        'logo',
        'pos_banner_image',
        'pos_stock_image',
        'pos_payment_image',
        'actif',
        'devise',
        'owner_id',
        'theme_color',
        'theme_style',
        'ticket_width',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    /**
     * Accessor pour le logo de la boutique
     * Retourne null si aucun logo n'est défini
     */
    public function getLogoAttribute($value)
    {
        // Si un logo personnalisé est défini dans la base de données et que le fichier existe, l'utiliser
        if ($value && file_exists(public_path('images/logos/' . $value))) {
            return asset('images/logos/' . $value);
        }

        // Si aucun logo n'est défini, retourner null (pas de logo par défaut)
        return null;
    }

    /**
     * Obtenir l'URL du logo pour l'impression
     */
    public function getLogoForPrintAttribute()
    {
        $logoUrl = $this->logo;

        // Pour l'impression, utiliser le chemin absolu
        if (str_starts_with($logoUrl, asset(''))) {
            return public_path(str_replace(asset(''), '', $logoUrl));
        }

        return $logoUrl;
    }

    /**
     * Accessor pour le thème de la boutique
     */
    public function getThemeAttribute()
    {
        // Si un theme_color est défini, l'utiliser
        if ($this->theme_color && $this->theme_color !== 'default') {
            return $this->theme_color;
        }

        // Sinon, utiliser le mapping par nom (rétrocompatibilité)
        $themeMap = [
            'Cosmetica' => 'default',
            'Maison des Abaya' => 'abaya',
        ];

        return $themeMap[$this->nom] ?? 'default';
    }

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Boot method to clear cache on model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($boutique) {
            \App\Services\CacheService::forgetBoutique($boutique->id);
        });

        static::deleted(function ($boutique) {
            \App\Services\CacheService::forgetBoutique($boutique->id);
        });
    }

    /**
     * Relations
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function fournisseurs()
    {
        return $this->hasMany(Fournisseur::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relation many-to-many : Une boutique peut avoir plusieurs propriétaires
     */
    public function owners()
    {
        return $this->belongsToMany(User::class, 'boutique_user', 'boutique_id', 'user_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Obtenir le propriétaire principal de la boutique
     */
    public function primaryOwner()
    {
        return $this->owners()
            ->wherePivot('is_primary', true)
            ->first() ?? $this->owner;
    }
}
