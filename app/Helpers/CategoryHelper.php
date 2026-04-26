<?php

namespace App\Helpers;

class CategoryHelper
{
    /**
     * Obtenir l'icône pour une catégorie
     */
    public static function getIcon($categorie)
    {
        $icons = [
            'Cosmétiques' => 'fas fa-palette',
            'Vêtements' => 'fas fa-tshirt',
            'Accessoires' => 'fas fa-gem',
            'Soins' => 'fas fa-spa',
            'Maquillage' => 'fas fa-paint-brush',
            'Parfums' => 'fas fa-wind',
            'Chaussures' => 'fas fa-shoe-prints',
            'Bijoux' => 'fas fa-ring',
            'Électronique' => 'fas fa-laptop',
            'Maison' => 'fas fa-home',
            'Sport' => 'fas fa-dumbbell',
            'Livre' => 'fas fa-book',
            'Alimentation' => 'fas fa-utensils',
            'Santé' => 'fas fa-heart',
            'Bébé' => 'fas fa-baby',
            'Autres' => 'fas fa-box'
        ];

        return $icons[$categorie] ?? 'fas fa-tag';
    }

    /**
     * Obtenir la couleur pour une catégorie
     */
    public static function getColor($categorie)
    {
        $colors = [
            'Cosmétiques' => 'primary',
            'Vêtements' => 'success',
            'Accessoires' => 'info',
            'Soins' => 'warning',
            'Maquillage' => 'danger',
            'Parfums' => 'secondary',
            'Chaussures' => 'dark',
            'Bijoux' => 'light',
            'Électronique' => 'primary',
            'Maison' => 'success',
            'Sport' => 'warning',
            'Livre' => 'info',
            'Alimentation' => 'danger',
            'Santé' => 'success',
            'Bébé' => 'primary',
            'Autres' => 'secondary'
        ];

        return $colors[$categorie] ?? 'secondary';
    }

    /**
     * Obtenir toutes les catégories prédéfinies
     */
    public static function getPredefinedCategories()
    {
        return [
            'Cosmétiques',
            'Vêtements',
            'Accessoires',
            'Soins',
            'Maquillage',
            'Parfums',
            'Chaussures',
            'Bijoux',
            'Électronique',
            'Maison',
            'Sport',
            'Livre',
            'Alimentation',
            'Santé',
            'Bébé',
            'Autres'
        ];
    }
}






























