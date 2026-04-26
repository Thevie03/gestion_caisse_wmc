<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vente;
use App\Models\Produit;
use App\Models\Depense;
use App\Models\Boutique;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $selectedBoutiqueId = session('boutique_active');

        // Statistiques générales
        $stats = $this->getStats($user, $selectedBoutiqueId);
        $totaux = $this->getTotals($user, $selectedBoutiqueId);

        // Graphiques
        $ventesChart = $this->getVentesChart($user, $selectedBoutiqueId);
        $produitsChart = $this->getProduitsChart($user, $selectedBoutiqueId);

        // Dernières ventes
        $dernieresVentes = $this->getDernieresVentes($user, $selectedBoutiqueId);

        // Produits en rupture de stock
        $produitsRupture = $this->getProduitsRupture($user, $selectedBoutiqueId);

        // Top 5 produits les plus vendus
        $topProduits = $this->getTopProduits($user, $selectedBoutiqueId);

        $abonnementActif = $user->abonnementActif()->first();
        $parametresBoutique = $user->parametresBoutique()->first();
        // Déterminer la boutique active (avec cache)
        if ($user->isAdmin() && $selectedBoutiqueId) {
            $boutiqueActive = \App\Services\CacheService::getBoutique($selectedBoutiqueId);
        } elseif ($user->isEmploye()) {
            // Les employés utilisent toujours leur boutique
            if (!$user->relationLoaded('boutique')) {
                $user->load(['boutique' => function($query) {
                    $query->select('id', 'nom', 'theme_color', 'logo', 'actif', 'devise');
                }]);
            }
            $boutiqueActive = $user->boutique;
        } elseif ($user->isOwner() && $selectedBoutiqueId) {
            // Les propriétaires utilisent la boutique active de la session
            $boutiqueActive = \App\Services\CacheService::getBoutique($selectedBoutiqueId);
        } elseif ($user->isOwner() && $user->boutique_id) {
            // Fallback pour les propriétaires sans session
            if (!$user->relationLoaded('boutique')) {
                $user->load(['boutique' => function($query) {
                    $query->select('id', 'nom', 'theme_color', 'logo', 'actif', 'devise');
                }]);
            }
            $boutiqueActive = $user->boutique;
        } else {
            $boutiqueActive = null;
        }

        return view('dashboard', compact(
            'stats',
            'totaux',
            'ventesChart',
            'produitsChart',
            'dernieresVentes',
            'produitsRupture',
            'topProduits',
            'abonnementActif',
            'parametresBoutique',
            'boutiqueActive',
            'user'
        ));
    }

    private function getStats($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les statistiques (2 minutes)
        $cacheKey = 'dashboard_stats_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($user, $selectedBoutiqueId) {
            $boutiqueId = null;
            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active (ils peuvent switcher)
            if ($user->isEmploye()) {
                $boutiqueId = $user->boutique_id;
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $boutiqueId = $selectedBoutiqueId;
            } elseif ($user->isOwner() && $user->boutique_id) {
                // Fallback pour les propriétaires sans session
                $boutiqueId = $user->boutique_id;
            }

            // Optimisation : Calculer toutes les statistiques en une seule requête
            // Sécurité : Utilisation de paramètres bindés pour éviter les injections SQL
            $ventesStats = Vente::selectRaw('
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_final ELSE 0 END), 0) as ventes_aujourdhui,
                COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN total_final ELSE 0 END), 0) as ventes_semaine,
                COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN total_final ELSE 0 END), 0) as ventes_mois,
                COUNT(*) as total_ventes
            ')
            ->when($boutiqueId, function($q) use ($boutiqueId) {
                // Sécurité : Utilisation de where() avec paramètre bindé (pas de concaténation SQL)
                $q->where('boutique_id', $boutiqueId);
            })
            ->first();

            $depensesMois = Depense::selectRaw('COALESCE(SUM(montant), 0) as total')
                ->whereMonth('date_depense', now()->month)
                ->whereYear('date_depense', now()->year)
                ->when($boutiqueId, function($q) use ($boutiqueId) {
                    $q->where('boutique_id', $boutiqueId);
                })
                ->value('total');

            return [
                'ventes_aujourdhui' => $ventesStats->ventes_aujourdhui ?? 0,
                'ventes_semaine' => $ventesStats->ventes_semaine ?? 0,
                'ventes_mois' => $ventesStats->ventes_mois ?? 0,
                'depenses_mois' => $depensesMois ?? 0,
                'total_ventes' => $ventesStats->total_ventes ?? 0,
                'benefice_mois' => ($ventesStats->ventes_mois ?? 0) - ($depensesMois ?? 0)
            ];
        });
    }

    private function getTotals($user, $selectedBoutiqueId): array
    {
        // Optimisation : Utiliser le cache pour les totaux (5 minutes)
        $cacheKey = 'dashboard_totals_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user, $selectedBoutiqueId) {
            $boutiqueId = null;
            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active (ils peuvent switcher)
            if ($user->isEmploye()) {
                $boutiqueId = $user->boutique_id;
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $boutiqueId = $selectedBoutiqueId;
            } elseif ($user->isOwner() && $user->boutique_id) {
                // Fallback pour les propriétaires sans session
                $boutiqueId = $user->boutique_id;
            }

            // Optimisation : Calculer toutes les statistiques en une seule requête par table
            $ventesStats = Vente::selectRaw('
                COALESCE(SUM(total_final), 0) as chiffre_affaires,
                COUNT(*) as nombre_ventes
            ')
            ->when($boutiqueId, function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            })
            ->first();

            $depensesTotal = Depense::selectRaw('COALESCE(SUM(montant), 0) as total')
                ->when($boutiqueId, function($q) use ($boutiqueId) {
                    $q->where('boutique_id', $boutiqueId);
                })
                ->value('total');

            $produitsStats = Produit::selectRaw('
                COUNT(*) as nombre_produits,
                COUNT(DISTINCT categorie) as nombre_categories
            ')
            ->whereNotNull('categorie')
            ->when($boutiqueId, function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            })
            ->first();

            return [
                'chiffre_affaires' => $ventesStats->chiffre_affaires ?? 0,
                'depenses_totales' => $depensesTotal ?? 0,
                'nombre_ventes' => $ventesStats->nombre_ventes ?? 0,
                'nombre_produits' => $produitsStats->nombre_produits ?? 0,
                'nombre_categories' => $produitsStats->nombre_categories ?? 0,
            ];
        });
    }

    private function getVentesChart($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les graphiques (5 minutes)
        $cacheKey = 'dashboard_ventes_chart_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user, $selectedBoutiqueId) {
            $query = Vente::query();

            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active
            if ($user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $query->where('boutique_id', $selectedBoutiqueId);
            }

            // Ventes des 7 derniers jours
            $ventes = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_final) as total')
                )
                ->where('created_at', '>=', Carbon::now()->subDays(6))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $data = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $labels[] = Carbon::now()->subDays($i)->format('d/m');

                $vente = $ventes->where('date', $date)->first();
                $data[] = $vente ? $vente->total : 0;
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        });
    }

    private function getProduitsChart($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les graphiques (5 minutes)
        $cacheKey = 'dashboard_produits_chart_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user, $selectedBoutiqueId) {
            $query = Produit::query();

            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active
            if ($user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $query->where('boutique_id', $selectedBoutiqueId);
            }

            $produits = $query->select('categorie', DB::raw('COUNT(*) as count'))
                             ->groupBy('categorie')
                             ->get();

            $labels = $produits->pluck('categorie')->map(function ($categorie) {
                return $categorie ?? 'Sans catégorie';
            })->toArray();

            return [
                'labels' => $labels,
                'data' => $produits->pluck('count')->toArray()
            ];
        });
    }

    private function getDernieresVentes($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les dernières ventes (2 minutes)
        $cacheKey = 'dashboard_dernieres_ventes_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($user, $selectedBoutiqueId) {
            // Optimisation : Limiter les colonnes chargées et utiliser eager loading optimisé
            $query = Vente::select('id', 'numero_vente', 'total_final', 'created_at', 'boutique_id', 'user_id', 'client_id')
                ->with([
                    'user:id,name',
                    'boutique:id,nom',
                    'venteDetails' => function($q) {
                        $q->select('id', 'vente_id', 'produit_id', 'quantite', 'prix_unitaire')
                          ->with('produit:id,nom');
                    }
                ]);

            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active
            if ($user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $query->where('boutique_id', $selectedBoutiqueId);
            }

            return $query->latest()->limit(5)->get();
        });
    }

    private function getProduitsRupture($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les produits en rupture (2 minutes)
        $cacheKey = 'dashboard_produits_rupture_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($user, $selectedBoutiqueId) {
            // Optimisation : Sélectionner uniquement les colonnes nécessaires
            $query = Produit::select('id', 'nom', 'quantite_stock', 'stock_minimum', 'categorie', 'boutique_id');

            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active
            if ($user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $query->where('boutique_id', $selectedBoutiqueId);
            }

            return $query->whereRaw('quantite_stock <= stock_minimum')
                        ->where('actif', true)
                        ->orderBy('quantite_stock', 'asc')
                        ->limit(5)
                        ->get();
        });
    }

    private function getTopProduits($user, $selectedBoutiqueId)
    {
        // Optimisation : Utiliser le cache pour les top produits (5 minutes)
        $cacheKey = 'dashboard_top_produits_' . $user->id . '_' . ($selectedBoutiqueId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user, $selectedBoutiqueId) {
            $query = DB::table('vente_details')
                      ->join('produits', 'vente_details.produit_id', '=', 'produits.id')
                      ->join('ventes', 'vente_details.vente_id', '=', 'ventes.id')
                      ->select('produits.nom', DB::raw('SUM(vente_details.quantite) as total_quantite'))
                      ->groupBy('produits.id', 'produits.nom');

            // Pour les employés, utiliser leur boutique_id
            // Pour les propriétaires, utiliser la session boutique_active
            if ($user->isEmploye()) {
                $query->where('ventes.boutique_id', $user->boutique_id);
            } elseif ($selectedBoutiqueId && $selectedBoutiqueId !== 'all') {
                $query->where('ventes.boutique_id', $selectedBoutiqueId);
            }

            return $query->orderBy('total_quantite', 'desc')
                        ->limit(5)
                        ->get();
        });
    }
}
