<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'module',
        'action',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }

    /**
     * Scope pour les permissions actives
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope par module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
