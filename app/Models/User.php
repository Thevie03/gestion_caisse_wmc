<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'employe'; // Utiliser 'employe' au lieu de 'USER' pour correspondre à l'ENUM
    public const SUPER_ADMIN_EMAIL = 'support@wmcci.com'; // Email du super admin unique

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'boutique_id',
        'telephone',
        'actif',
        'tenant_key',
        'subscription_status',
        'subscription_expires_at',
        'trial_ends_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'actif' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Relation many-to-many : Un propriétaire peut avoir plusieurs boutiques
     */
    public function ownedBoutiques()
    {
        return $this->belongsToMany(Boutique::class, 'boutique_user', 'user_id', 'boutique_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Obtenir toutes les boutiques accessibles par l'utilisateur
     * (boutiques possédées + boutique assignée si employé)
     */
    public function accessibleBoutiques()
    {
        $boutiques = collect();

        // Si c'est un propriétaire, récupérer toutes ses boutiques
        if ($this->isAdmin() && $this->ownedBoutiques()->exists()) {
            $boutiques = $boutiques->merge($this->ownedBoutiques);
        }

        // Si l'utilisateur a une boutique assignée (employé ou propriétaire avec boutique principale)
        if ($this->boutique_id) {
            $boutiques = $boutiques->push($this->boutique);
        }

        // Retirer les doublons
        return $boutiques->unique('id');
    }

    /**
     * Obtenir la boutique principale du propriétaire
     */
    public function primaryBoutique()
    {
        return $this->ownedBoutiques()
            ->wherePivot('is_primary', true)
            ->first() ?? $this->ownedBoutiques()->first() ?? $this->boutique;
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    /**
     * Relation avec les permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Vérifier si l'utilisateur est super admin
     * Le super admin unique est identifié par son email défini dans SUPER_ADMIN_EMAIL.
     *
     * IMPORTANT : Même si un propriétaire de boutique a le rôle 'admin' et une acquisition définitive,
     * il n'est PAS considéré comme super admin. Seul l'email SUPER_ADMIN_EMAIL est le super admin.
     * Les propriétaires de boutique doivent toujours voir leur dashboard de boutique.
     */
    public function isSuperAdmin()
    {
        return $this->email === self::SUPER_ADMIN_EMAIL && $this->normalized_role === self::ROLE_ADMIN;
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin()
    {
        return $this->normalized_role === self::ROLE_ADMIN;
    }

    /**
     * Vérifier si l'utilisateur est employé
     */
    public function isEmploye()
    {
        return $this->normalized_role === self::ROLE_USER;
    }

    /**
     * Vérifier si l'utilisateur est propriétaire d'au moins une boutique
     */
    public function isOwner()
    {
        // Vérifier via la relation many-to-many (nouveau système)
        if ($this->ownedBoutiques()->exists()) {
            return true;
        }

        // Vérifier via owner_id (ancien système pour rétrocompatibilité)
        if (!$this->boutique_id) {
            return false;
        }

        // Charger la relation boutique si elle n'est pas déjà chargée
        if (!$this->relationLoaded('boutique')) {
            $this->load(['boutique' => function($query) {
                $query->select('id', 'nom', 'owner_id', 'actif', 'devise', 'theme_color', 'logo');
            }]);
        }

        // Si la boutique n'existe pas, retourner false
        if (!$this->boutique) {
            return false;
        }

        // Vérifier que l'utilisateur est bien le propriétaire
        return $this->boutique->owner_id === $this->id;
    }

    /**
     * Vérifier si l'utilisateur est propriétaire d'une boutique spécifique
     */
    public function ownsBoutique($boutiqueId)
    {
        // Vérifier via la relation many-to-many
        if ($this->ownedBoutiques()->where('boutiques.id', $boutiqueId)->exists()) {
            return true;
        }

        // Vérifier via owner_id (rétrocompatibilité)
        $boutique = Boutique::find($boutiqueId);
        return $boutique && $boutique->owner_id === $this->id;
    }

    /**
     * Vérifier si l'utilisateur peut gérer sa boutique (propriétaire ou admin)
     */
    public function canManageBoutique()
    {
        return $this->isAdmin() || $this->isOwner();
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    public function hasPermission($permission)
    {
        if ($this->isAdmin()) {
            return true; // Le super admin et l'admin ont toutes les permissions
        }

        return $this->permissions()->where('nom', $permission)->exists();
    }

    /**
     * Vérifier si l'utilisateur a une permission pour un module et une action
     */
    public function canAccess($module, $action)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Obtenir les permissions par module
     */
    public function getPermissionsByModule($module)
    {
        return $this->permissions()->where('module', $module)->get();
    }

    /**
     * Vérifier si l'utilisateur peut accéder à un onglet de la sidebar
     * Les propriétaires et admins ont accès à tous les onglets
     * Les employés ont accès selon leurs permissions
     */
    public function canAccessSidebarItem($itemName)
    {
        // Les admins et propriétaires ont accès à tous les onglets
        if ($this->isAdmin() || $this->isOwner()) {
            return true;
        }

        // Pour les employés, vérifier la permission correspondante
        // Format: sidebar.{item_name} (ex: sidebar.dashboard, sidebar.produits)
        $permissionName = 'sidebar.' . strtolower($itemName);

        return $this->permissions()
            ->where(function($query) use ($permissionName, $itemName) {
                $query->where('nom', $permissionName)
                      ->orWhere(function($q) use ($itemName) {
                          $q->where('module', 'sidebar')
                            ->where('action', strtolower($itemName));
                      });
            })
            ->exists();
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class)
            ->where('statut', Abonnement::STATUT_ACTIF)
            ->where(function($q) {
                $q->where('type_abonnement', Abonnement::TYPE_ACQUISITION_DEFINITIVE)
                  ->orWhereDate('date_expiration', '>=', now());
            })
            ->latestOfMany('date_expiration');
    }

    public function parametresBoutique()
    {
        return $this->hasOne(ParametresBoutique::class);
    }

    public function wantsSubscriptionNotifications(): bool
    {
        return (bool) ($this->parametresBoutique?->notifications_abonnement ?? true);
    }

    public function getNormalizedRoleAttribute(): string
    {
        return match ($this->role) {
            'admin', 'super_admin' => self::ROLE_ADMIN,
            'employe' => self::ROLE_USER,
            default => self::ROLE_USER,
        };
    }
}
