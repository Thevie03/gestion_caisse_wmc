<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametresBoutique extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'parametres_boutique';

    protected $fillable = [
        'user_id',
        'boutique_id',
        'nom_affichage',
        'logo_path',
        'devise',
        'timezone',
        'options',
        'notifications_stock',
        'notifications_abonnement',
    ];

    protected bool $autoAssignBoutique = true;

    protected $casts = [
        'options' => 'array',
        'notifications_stock' => 'boolean',
        'notifications_abonnement' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }
}



