<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Affichage sécurisé des messages de notification (échappement + mise en forme contrôlée).
 */
class NotificationMessageFormatter
{
    public static function format(string $message, ?string $module = null, ?int $limit = null): string
    {
        if ($limit !== null) {
            $message = Str::limit($message, $limit);
        }

        $escaped = e($message);

        if ($module === 'stock') {
            $escaped = str_replace(
                '&#039;sortie&#039;',
                '<span class="text-danger fw-bold">&#039;sortie&#039;</span>',
                $escaped
            );
            $escaped = str_replace(
                '&#039;entree&#039;',
                '<span class="text-success fw-bold">&#039;entree&#039;</span>',
                $escaped
            );
        }

        return $escaped;
    }
}
