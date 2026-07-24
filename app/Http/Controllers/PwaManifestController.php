<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Manifest PWA dynamique — URLs absolues basées sur APP_URL.
 *
 * start_url = /app (page publique) pour éviter ERR_FAILED au lancement
 * de l'application installée (mobile iOS/Android et desktop Windows).
 */
class PwaManifestController extends Controller
{
    public function show(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');
        $startUrl = $base . '/app';
        $icon192 = $base . '/images/icons/icon-192.png';
        $icon512 = $base . '/images/icons/icon-512.png';
        $appleIcon = $base . '/images/icons/apple-touch-icon.png';

        return response()->json([
            'id' => $startUrl,
            'name' => config('app.name', 'GestionCaisse WMC'),
            'short_name' => 'WMC Caisse',
            'description' => 'Application de gestion de caisse WMC — ventes, stock, clients et paiements.',
            'lang' => 'fr',
            'dir' => 'ltr',
            'start_url' => $startUrl,
            'scope' => $base . '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui', 'browser'],
            'orientation' => 'any',
            'background_color' => '#111827',
            'theme_color' => '#F59E0B',
            'categories' => ['business', 'finance', 'productivity'],
            'prefer_related_applications' => false,
            'launch_handler' => [
                'client_mode' => 'navigate-existing',
            ],
            'icons' => [
                [
                    'src' => $appleIcon,
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts' => [
                [
                    'name' => 'Connexion',
                    'short_name' => 'Login',
                    'description' => 'Ouvrir l\'application WMC Caisse',
                    'url' => $startUrl,
                    'icons' => [
                        [
                            'src' => $icon192,
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
                [
                    'name' => 'Point de vente',
                    'short_name' => 'POS',
                    'description' => 'Ouvrir l\'interface caisse',
                    'url' => $base . '/ventes/pos/interface',
                    'icons' => [
                        [
                            'src' => $icon192,
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
