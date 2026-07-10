<?php

namespace App\Support;

/**
 * Nettoyage HTML pour contenu administrateur (CGU, etc.).
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><b><i><a><blockquote><hr><table><thead><tbody><tr><th><td>';

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $cleaned = strip_tags($html, self::ALLOWED_TAGS);

        return preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned) ?? $cleaned;
    }
}
