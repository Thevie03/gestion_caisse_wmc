<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametreSysteme extends Model
{
    use HasFactory;

    protected $table = 'parametres_systeme';

    protected $fillable = [
        'cle',
        'valeur',
        'type',
        'description',
    ];

    /**
     * Récupérer la valeur d'un paramètre
     */
    public static function get(string $cle, $default = null)
    {
        $parametre = self::where('cle', $cle)->first();
        
        if (!$parametre) {
            return $default;
        }

        return match ($parametre->type) {
            'integer' => (int) $parametre->valeur,
            'boolean' => (bool) $parametre->valeur,
            'json' => json_decode($parametre->valeur, true),
            default => $parametre->valeur,
        };
    }

    /**
     * Définir la valeur d'un paramètre
     */
    public static function set(string $cle, $valeur, string $type = 'string', ?string $description = null): void
    {
        $valeurFinale = match ($type) {
            'json' => json_encode($valeur),
            'boolean' => $valeur ? '1' : '0',
            default => (string) $valeur,
        };

        self::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => $valeurFinale,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
