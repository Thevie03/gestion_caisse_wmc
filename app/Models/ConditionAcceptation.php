<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConditionAcceptation extends Model
{
    use HasFactory;

    protected $table = 'conditions_acceptations';

    protected $fillable = [
        'user_id',
        'version',
        'accepted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
