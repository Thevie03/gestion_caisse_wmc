<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected bool $autoAssignBoutique = true;

    protected $fillable = [
        'description',
        'categorie',
        'montant',
        'boutique_id',
        'user_id',
        'date_depense',
        'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
    ];

    /**
     * Relations
     */
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
