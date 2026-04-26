<?php

namespace App\Services;

use App\Models\Boutique;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Récupère la liste des boutiques actives avec cache
     */
    public static function getActiveBoutiques()
    {
        return Cache::remember('boutiques_actives_list', 600, function () {
            return Boutique::select('id', 'nom', 'actif')
                ->where('actif', true)
                ->orderBy('nom')
                ->get();
        });
    }

    /**
     * Récupère une boutique avec cache
     */
    public static function getBoutique($id)
    {
        return Cache::remember("boutique_{$id}", 300, function () use ($id) {
            return Boutique::select('id', 'nom', 'theme_color', 'logo', 'actif', 'devise', 'pos_banner_image')
                ->find($id);
        });
    }

    /**
     * Invalide le cache d'une boutique
     */
    public static function forgetBoutique($id)
    {
        Cache::forget("boutique_{$id}");
        Cache::forget("boutique_theme_{$id}");
        Cache::forget('boutiques_actives_list');
    }

    /**
     * Invalide tous les caches de boutiques
     */
    public static function forgetAllBoutiques()
    {
        Cache::forget('boutiques_actives_list');
        // Note: Les caches individuels expireront automatiquement
    }
}


