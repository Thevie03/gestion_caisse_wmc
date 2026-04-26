<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'produit_id',
        'type',
        'quantite',
        'motif',
        'user_id',
        'boutique_id',
    ];

    /**
     * Relations
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }
}
