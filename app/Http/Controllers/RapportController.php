<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Depense;
use App\Models\Produit;
use App\Models\Boutique;
use App\Models\User;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\VentesExport;
use App\Exports\StockExport;
use App\Exports\DepensesExport;
use App\Exports\FinancierExport;

class RapportController extends Controller
{
    /**
     * Afficher la page principale des rapports
     */
    public function index()
    {
        $boutiqueActive = session('boutique_active');
        $user = auth()->user();

        // Statistiques générales
        $stats = $this->getStatistiquesGenerales($user, $boutiqueActive);

        // Boutiques pour le filtre
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->get();
            }
        } else {
            $boutiques = collect();
        }

        // Données pour le graphique des 30 derniers jours
        $chartData = $this->getChartData30Days($user, $boutiqueActive);

        return view('rapports.index', compact('stats', 'boutiques', 'chartData'));
    }

    /**
     * Rapport des ventes
     */
    public function ventes(Request $request)
    {
        $boutiqueActive = session('boutique_active');
        $user = auth()->user();

        // Validation des dates
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type_rapport' => 'required|in:journalier,hebdomadaire,mensuel'
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;
        $typeRapport = $request->type_rapport;

        // Construire la requête avec toutes les relations nécessaires
        $query = Vente::with(['user', 'boutique', 'details.produit', 'client', 'paiements']);

        // Filtrage par boutique
        // Les employés voient uniquement les ventes de leur boutique
        // Les propriétaires voient les ventes de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $query->where('boutique_id', $boutiqueActive);
        }

        // Filtrage par dates (inclure la date de fin)
        $query->whereDate('created_at', '>=', $dateDebut)
              ->whereDate('created_at', '<=', $dateFin);

        $ventes = $query->orderBy('created_at', 'desc')->get();

        // Statistiques du rapport avec vérifications
        $stats = [
            'total_ventes' => $ventes->count(),
            'chiffre_affaires' => $ventes->sum('total_final') ?? 0,
            'moyenne_vente' => $ventes->count() > 0 ? $ventes->avg('total_final') : 0,
            'meilleur_jour' => $this->getMeilleurJour($ventes),
            'produits_vendus' => $this->getProduitsVendus($ventes),
            'ventes_par_mode_paiement' => $this->getVentesParModePaiement($ventes),
        ];

        // Export PDF si demandé
        if ($request->has('export_pdf')) {
            return $this->exportVentesPDF($ventes, $stats, $dateDebut, $dateFin, $typeRapport);
        }

        // Export Excel si demandé
        if ($request->has('export_excel')) {
            return $this->exportVentesExcel($ventes, $stats, $dateDebut, $dateFin, $typeRapport);
        }

        return view('rapports.ventes', compact('ventes', 'stats', 'dateDebut', 'dateFin', 'typeRapport'));
    }

    /**
     * Rapport du stock
     */
    public function stock(Request $request)
    {
        $this->validateListingFilters($request, [
            'stock_faible' => 'nullable|in:0,1,true,false',
        ]);

        $boutiqueActive = session('boutique_active');
        $user = auth()->user();

        // Construire la requête
        $baseQuery = Produit::with('boutique');

        // Filtrage par boutique
        // Les propriétaires et employés ne voient que les données de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $baseQuery->where('boutique_id', $boutiqueActive);
        }

        // Filtres
        if ($request->filled('categorie')) {
            $baseQuery->where('categorie', $request->categorie);
        }

        if ($request->filled('stock_faible')) {
            $baseQuery->whereColumn('quantite_stock', '<=', 'stock_minimum');
        }

        $produits = (clone $baseQuery)->orderBy('quantite_stock', 'asc')->get();

        // Statistiques du stock
        $stats = [
            'total_produits' => $produits->count(),
            'valeur_stock' => $produits->sum(function($p) { return $p->quantite_stock * $p->prix_achat; }),
            'produits_faibles' => $produits->filter(function($produit) {
                return $produit->quantite_stock <= $produit->stock_minimum;
            })->count(),
            'produits_rupture' => $produits->where('quantite_stock', 0)->count(),
            'categories' => $this->getCategoriesStock($produits),
        ];

        // Export PDF si demandé
        if ($request->has('export_pdf')) {
            return $this->exportStockPDF($produits, $stats);
        }

        // Export Excel si demandé
        if ($request->has('export_excel')) {
            return $this->exportStockExcel($produits, $stats);
        }

        return view('rapports.stock', compact('produits', 'stats'));
    }

    /**
     * Rapport des dépenses
     */
    public function depenses(Request $request)
    {
        $boutiqueActive = session('boutique_active');
        $user = auth()->user();

        // Validation des dates
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;

        // Construire la requête avec la relation boutique incluant theme_color
        $query = Depense::with(['user', 'boutique:id,nom,theme_color']);

        // Filtrage par boutique
        // Les propriétaires et employés ne voient que les données de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $query->where('boutique_id', $boutiqueActive);
        }

        // Filtrage par dates (inclure la date de fin)
        $query->whereDate('date_depense', '>=', $dateDebut)
              ->whereDate('date_depense', '<=', $dateFin);

        $depenses = $query->orderBy('date_depense', 'desc')->get();

        // Statistiques des dépenses
        $stats = [
            'total_depenses' => $depenses->count(),
            'montant_total' => $depenses->sum('montant'),
            'moyenne_depense' => $depenses->avg('montant'),
            'depenses_par_categorie' => $this->getDepensesParCategorie($depenses),
            'depenses_par_mois' => $this->getDepensesParMois($depenses),
        ];

        // Export PDF si demandé
        if ($request->has('export_pdf')) {
            return $this->exportDepensesPDF($depenses, $stats, $dateDebut, $dateFin);
        }

        // Export Excel si demandé
        if ($request->has('export_excel')) {
            return $this->exportDepensesExcel($depenses, $stats, $dateDebut, $dateFin);
        }

        return view('rapports.depenses', compact('depenses', 'stats', 'dateDebut', 'dateFin'));
    }

    /**
     * Rapport financier global
     */
    public function financier(Request $request)
    {
        $user = auth()->user();

        // Validation des dates
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;

        $rapportBoutiques = [];

        // Si super admin : données pour toutes les boutiques
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->get();
            foreach ($boutiques as $boutique) {
                $rapportBoutiques[] = $this->getRapportBoutique($boutique, $dateDebut, $dateFin);
            }
        }
        // Si propriétaire : données pour ses boutiques assignées uniquement
        elseif ($user->isOwner()) {
            $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                $ownedBoutiques = collect([$user->boutique]);
            }
            foreach ($ownedBoutiques as $boutique) {
                $rapportBoutiques[] = $this->getRapportBoutique($boutique, $dateDebut, $dateFin);
            }
        }
        // Si propriétaire : données pour la boutique active uniquement
        elseif ($user->isOwner() && $boutiqueActive) {
            $boutique = Boutique::find($boutiqueActive);
            if ($boutique && $user->ownsBoutique($boutique->id)) {
                $rapportBoutiques[] = $this->getRapportBoutique($boutique, $dateDebut, $dateFin);
            }
        } else {
            abort(403, 'Accès non autorisé.');
        }

        // Statistiques globales
        $statsGlobales = [
            'chiffre_affaires_total' => collect($rapportBoutiques)->sum('chiffre_affaires'),
            'depenses_totales' => collect($rapportBoutiques)->sum('depenses'),
            'benefice_net' => collect($rapportBoutiques)->sum('benefice'),
            'nombre_transactions' => collect($rapportBoutiques)->sum('nombre_ventes'),
        ];

        // Export PDF si demandé
        if ($request->has('export_pdf')) {
            return $this->exportFinancierPDF($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin);
        }

        // Export Excel si demandé
        if ($request->has('export_excel')) {
            return $this->exportFinancierExcel($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin);
        }

        return view('rapports.financier', compact('rapportBoutiques', 'statsGlobales', 'dateDebut', 'dateFin'));
    }

    /**
     * Obtenir les statistiques générales
     */
    private function getStatistiquesGenerales($user, $boutiqueActive)
    {
        $queryVentes = Vente::query();
        $queryDepenses = Depense::query();

        // Filtrage par boutique
        // Les propriétaires et employés ne voient que les données de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $queryVentes->where('boutique_id', $user->boutique_id);
            $queryDepenses->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $queryVentes->where('boutique_id', $boutiqueActive);
            $queryDepenses->where('boutique_id', $boutiqueActive);
        }

        return [
            'ventes_aujourd_hui' => (clone $queryVentes)->whereDate('created_at', today())->count(),
            'chiffre_affaires_aujourd_hui' => (clone $queryVentes)->whereDate('created_at', today())->sum('total_final'),
            'depenses_aujourd_hui' => (clone $queryDepenses)->whereDate('date_depense', today())->sum('montant'),
            'ventes_ce_mois' => (clone $queryVentes)->whereMonth('created_at', now()->month)->count(),
            'chiffre_affaires_ce_mois' => (clone $queryVentes)->whereMonth('created_at', now()->month)->sum('total_final'),
        ];
    }

    /**
     * Obtenir les données du graphique des 30 derniers jours
     */
    private function getChartData30Days($user, $boutiqueActive)
    {
        $labels = [];
        $queryVentes = Vente::query();
        $queryDepenses = Depense::query();

        // Filtrage par boutique
        // Les propriétaires et employés ne voient que les données de leur boutique
        if ($user->isEmploye() || $user->isOwner()) {
            $queryVentes->where('boutique_id', $user->boutique_id);
            $queryDepenses->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $queryVentes->where('boutique_id', $boutiqueActive);
            $queryDepenses->where('boutique_id', $boutiqueActive);
        }

        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $ventesMap = (clone $queryVentes)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total_final), 0) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'date');

        $depensesMap = (clone $queryDepenses)
            ->whereBetween('date_depense', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('DATE(date_depense) as date, COALESCE(SUM(montant), 0) as total')
            ->groupByRaw('DATE(date_depense)')
            ->pluck('total', 'date');

        $ventesData = [];
        $depensesData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $ventesData[] = (float) ($ventesMap[$key] ?? 0);
            $depensesData[] = (float) ($depensesMap[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'ventes' => $ventesData,
            'depenses' => $depensesData,
        ];
    }

    /**
     * Obtenir le meilleur jour de vente
     */
    private function getMeilleurJour($ventes)
    {
        $ventesParJour = $ventes->groupBy(function($vente) {
            return $vente->created_at->format('Y-m-d');
        });

        $meilleurJour = $ventesParJour->map(function($ventesJour) {
            return $ventesJour->sum('total_final');
        })->sortDesc()->first();

        return $meilleurJour;
    }

    /**
     * Obtenir les produits les plus vendus
     */
    private function getProduitsVendus($ventes)
    {
        $produitsVendus = [];

        foreach ($ventes as $vente) {
            foreach ($vente->details as $detail) {
                if ($detail->produit) {
                    $produitNom = $detail->produit->nom;
                    if (!isset($produitsVendus[$produitNom])) {
                        $produitsVendus[$produitNom] = [
                            'nom' => $produitNom,
                            'quantite' => 0,
                            'montant' => 0
                        ];
                    }
                    $produitsVendus[$produitNom]['quantite'] += $detail->quantite;
                    $produitsVendus[$produitNom]['montant'] += $detail->sous_total;
                }
            }
        }

        return collect($produitsVendus)->sortByDesc('montant')->take(10);
    }

    /**
     * Obtenir les ventes par mode de paiement
     */
    private function getVentesParModePaiement($ventes)
    {
        $resultats = [];

        foreach ($ventes as $vente) {
            if ($vente->paiements && $vente->paiements->count() > 0) {
                foreach ($vente->paiements as $paiement) {
                    $mode = $paiement->mode_paiement;
                    if (!isset($resultats[$mode])) {
                        $resultats[$mode] = [
                            'count' => 0,
                            'total' => 0,
                        ];
                    }
                    $resultats[$mode]['count']++;
                    $resultats[$mode]['total'] += $paiement->montant;
                }
            } else {
                $mode = $vente->mode_paiement;
                if (!isset($resultats[$mode])) {
                    $resultats[$mode] = [
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                $resultats[$mode]['count']++;
                $resultats[$mode]['total'] += $vente->total_final;
            }
        }

        return collect($resultats);
    }

    /**
     * Obtenir les catégories et leur stock
     */
    private function getCategoriesStock($produits)
    {
        return $produits->groupBy('categorie')->map(function($produitsCategorie) {
            return [
                'quantite' => $produitsCategorie->sum('quantite_stock'),
                'valeur' => $produitsCategorie->sum(function($p) { return $p->quantite_stock * $p->prix_achat; })
            ];
        });
    }

    /**
     * Obtenir les dépenses par catégorie
     */
    private function getDepensesParCategorie($depenses)
    {
        return $depenses->groupBy('categorie')->map(function($depensesCategorie) {
            return [
                'count' => $depensesCategorie->count(),
                'total' => $depensesCategorie->sum('montant')
            ];
        });
    }

    /**
     * Obtenir les dépenses par mois
     */
    private function getDepensesParMois($depenses)
    {
        return $depenses->groupBy(function($depense) {
            return $depense->date_depense->format('Y-m');
        })->map(function($depensesMois) {
            return $depensesMois->sum('montant');
        });
    }

    /**
     * Obtenir le rapport d'une boutique
     */
    private function getRapportBoutique($boutique, $dateDebut, $dateFin)
    {
        $ventes = Vente::where('boutique_id', $boutique->id)
            ->whereDate('created_at', '>=', $dateDebut)
            ->whereDate('created_at', '<=', $dateFin)
            ->get();

        $depenses = Depense::where('boutique_id', $boutique->id)
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->get();

        $chiffreAffaires = $ventes->sum('total_final');
        $depensesTotal = $depenses->sum('montant');
        $benefice = $chiffreAffaires - $depensesTotal;

        return [
            'boutique' => $boutique,
            'chiffre_affaires' => $chiffreAffaires,
            'depenses' => $depensesTotal,
            'benefice' => $benefice,
            'nombre_ventes' => $ventes->count(),
            'nombre_depenses' => $depenses->count(),
        ];
    }

    /**
     * Export PDF des ventes
     */
    private function exportVentesPDF($ventes, $stats, $dateDebut, $dateFin, $typeRapport)
    {
        // Vérifier que les données existent
        if ($ventes->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune donnée à exporter pour cette période.');
        }

        // S'assurer que les statistiques sont valides
        $stats = array_merge([
            'total_ventes' => 0,
            'chiffre_affaires' => 0,
            'moyenne_vente' => 0,
            'meilleur_jour' => 0,
            'produits_vendus' => collect(),
            'ventes_par_mode_paiement' => collect(),
        ], $stats);

        // Récupérer la boutique et son thème
        $boutique = $this->getBoutiqueFromVentes($ventes);
        $themeColor = $boutique ? ($boutique->theme_color ?? '#007bff') : '#007bff';

        try {
            $pdf = Pdf::loadView('rapports.pdf.ventes', compact('ventes', 'stats', 'dateDebut', 'dateFin', 'typeRapport', 'boutique', 'themeColor'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Helvetica'
                ]);

            $filename = 'Rapport_Ventes_' . $dateDebut . '_' . $dateFin . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Erreur export PDF ventes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF du stock
     */
    private function exportStockPDF($produits, $stats)
    {
        // Récupérer la boutique et son thème
        $boutique = $this->getBoutiqueFromProduits($produits);
        $themeColor = $boutique ? ($boutique->theme_color ?? '#007bff') : '#007bff';

        try {
            $pdf = Pdf::loadView('rapports.pdf.stock', compact('produits', 'stats', 'boutique', 'themeColor'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Helvetica'
                ]);

            $filename = 'Rapport_Stock_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Erreur export PDF stock: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF des dépenses
     */
    private function exportDepensesPDF($depenses, $stats, $dateDebut, $dateFin)
    {
        // Récupérer la boutique et son thème - méthode similaire aux autres exports
        $boutique = $this->getBoutiqueFromDepenses($depenses);

        // Récupérer le theme_color de manière fiable - même logique que pour les ventes
        $themeColor = '#007bff'; // Fallback par défaut

        if ($boutique) {
            // Vérifier si theme_color existe et n'est pas vide
            if (isset($boutique->theme_color) && !empty($boutique->theme_color) && $boutique->theme_color !== 'default') {
                $themeColor = $boutique->theme_color;
            } else {
                // Recharger la boutique avec theme_color
                $boutique = Boutique::select('id', 'nom', 'theme_color')->find($boutique->id);
                if ($boutique && !empty($boutique->theme_color) && $boutique->theme_color !== 'default') {
                    $themeColor = $boutique->theme_color;
                }
            }
        }

        // Si toujours pas de thème valide, essayer depuis la première dépense
        if (($themeColor === '#007bff' || empty($themeColor)) && $depenses->isNotEmpty()) {
            $firstDepense = $depenses->first();
            if ($firstDepense && $firstDepense->boutique_id) {
                $boutiqueFromDepense = Boutique::select('id', 'nom', 'theme_color')->find($firstDepense->boutique_id);
                if ($boutiqueFromDepense && !empty($boutiqueFromDepense->theme_color) && $boutiqueFromDepense->theme_color !== 'default') {
                    $themeColor = $boutiqueFromDepense->theme_color;
                    $boutique = $boutiqueFromDepense;
                }
            }
        }

        try {
            $pdf = Pdf::loadView('rapports.pdf.depenses', compact('depenses', 'stats', 'dateDebut', 'dateFin', 'boutique', 'themeColor'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Helvetica'
                ]);

            $filename = 'Rapport_Depenses_' . $dateDebut . '_' . $dateFin . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Erreur export PDF dépenses: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF financier
     */
    private function exportFinancierPDF($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin)
    {
        // Récupérer la boutique active et son thème
        $user = auth()->user();
        $boutiqueActive = session('boutique_active');

        if ($user->isEmploye() || $user->isOwner()) {
            $boutique = Boutique::select('id', 'nom', 'theme_color')->find($user->boutique_id);
        } elseif ($boutiqueActive) {
            $boutique = Boutique::select('id', 'nom', 'theme_color')->find($boutiqueActive);
        } else {
            // Pour le super admin, prendre la première boutique du rapport
            if ($rapportBoutiques->isNotEmpty() && isset($rapportBoutiques[0]['boutique'])) {
                $boutique = $rapportBoutiques[0]['boutique'];
                // S'assurer que theme_color est chargé
                if ($boutique && !isset($boutique->theme_color)) {
                    $boutique = Boutique::select('id', 'nom', 'theme_color')->find($boutique->id);
                }
            } else {
                $boutique = null;
            }
        }

        $themeColor = $boutique ? ($boutique->theme_color ?? '#007bff') : '#007bff';

        try {
            $pdf = Pdf::loadView('rapports.pdf.financier', compact('rapportBoutiques', 'statsGlobales', 'dateDebut', 'dateFin', 'boutique', 'themeColor'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Helvetica'
                ]);

            $filename = 'Rapport_Financier_' . $dateDebut . '_' . $dateFin . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Erreur export PDF financier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel des ventes
     */
    private function exportVentesExcel($ventes, $stats, $dateDebut, $dateFin, $typeRapport)
    {
        $filename = 'Rapport_Ventes_' . $dateDebut . '_' . $dateFin . '.xlsx';
        $export = new VentesExport($ventes, $stats, $dateDebut, $dateFin, $typeRapport);
        return $export->download($filename);
    }

    /**
     * Export Excel du stock
     */
    private function exportStockExcel($produits, $stats)
    {
        $filename = 'Rapport_Stock_' . date('Y-m-d') . '.xlsx';
        $export = new StockExport($produits, $stats);
        return $export->download($filename);
    }

    /**
     * Export Excel des dépenses
     */
    private function exportDepensesExcel($depenses, $stats, $dateDebut, $dateFin)
    {
        $filename = 'Rapport_Depenses_' . $dateDebut . '_' . $dateFin . '.xlsx';
        $export = new DepensesExport($depenses, $stats, $dateDebut, $dateFin);
        return $export->download($filename);
    }

    /**
     * Export Excel financier
     */
    private function exportFinancierExcel($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin)
    {
        $filename = 'Rapport_Financier_' . $dateDebut . '_' . $dateFin . '.xlsx';
        $export = new FinancierExport($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin);
        return $export->download($filename);
    }

    /**
     * Helper: Récupérer la boutique depuis une collection de ventes
     */
    private function getBoutiqueFromVentes($ventes)
    {
        if ($ventes->isEmpty()) {
            return null;
        }

        $user = auth()->user();
        $boutiqueActive = session('boutique_active');

        if ($user->isEmploye() || $user->isOwner()) {
            return Boutique::select('id', 'nom', 'theme_color')->find($user->boutique_id);
        } elseif ($boutiqueActive) {
            return Boutique::select('id', 'nom', 'theme_color')->find($boutiqueActive);
        } else {
            // Prendre la boutique de la première vente
            $firstVente = $ventes->first();
            if ($firstVente && $firstVente->boutique) {
                // S'assurer que theme_color est chargé
                $firstVente->load('boutique:id,nom,theme_color');
                return $firstVente->boutique;
            }
            return null;
        }
    }

    /**
     * Helper: Récupérer la boutique depuis une collection de produits
     */
    private function getBoutiqueFromProduits($produits)
    {
        if ($produits->isEmpty()) {
            return null;
        }

        $user = auth()->user();
        $boutiqueActive = session('boutique_active');

        if ($user->isEmploye() || $user->isOwner()) {
            return Boutique::select('id', 'nom', 'theme_color')->find($user->boutique_id);
        } elseif ($boutiqueActive) {
            return Boutique::select('id', 'nom', 'theme_color')->find($boutiqueActive);
        } else {
            // Prendre la boutique du premier produit
            $firstProduit = $produits->first();
            if ($firstProduit && $firstProduit->boutique) {
                // S'assurer que theme_color est chargé
                $firstProduit->load('boutique:id,nom,theme_color');
                return $firstProduit->boutique;
            }
            return null;
        }
    }

    /**
     * Helper: Récupérer la boutique depuis une collection de dépenses
     */
    private function getBoutiqueFromDepenses($depenses)
    {
        if ($depenses->isEmpty()) {
            return null;
        }

        $user = auth()->user();
        $boutiqueActive = session('boutique_active');

        if ($user->isEmploye() || $user->isOwner()) {
            return Boutique::select('id', 'nom', 'theme_color')->find($user->boutique_id);
        } elseif ($boutiqueActive) {
            return Boutique::select('id', 'nom', 'theme_color')->find($boutiqueActive);
        } else {
            // Prendre la boutique de la première dépense
            $firstDepense = $depenses->first();
            if ($firstDepense && $firstDepense->boutique_id) {
                // Charger directement depuis la base avec theme_color
                $boutique = Boutique::select('id', 'nom', 'theme_color')->find($firstDepense->boutique_id);
                return $boutique;
            }
            return null;
        }
    }
}
