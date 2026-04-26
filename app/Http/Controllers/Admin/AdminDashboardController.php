<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\PaiementAbonnement;
use App\Models\User;
use App\Models\Vente;
use App\Models\Depense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $merchantRoles = ['employe']; // Les commerçants utilisent le rôle 'employe'

        $now = now();

        // Statistiques principales
        $stats = [
            'commercants_total' => User::whereIn('role', $merchantRoles)->count(),
            'commercants_actifs' => User::whereIn('role', $merchantRoles)->where('actif', true)->count(),
            'boutiques_total' => Boutique::count(),
            'boutiques_actives' => Boutique::where('actif', true)->count(),
            'abonnements_actifs' => Abonnement::where('statut', Abonnement::STATUT_ACTIF)
                ->whereDate('date_expiration', '>=', now())
                ->count(),
            'abonnements_expire' => Abonnement::where(function ($query) {
                    $query->where('statut', Abonnement::STATUT_EXPIRE)
                        ->orWhere(function ($q) {
                            $q->where('statut', Abonnement::STATUT_ACTIF)
                                ->whereDate('date_expiration', '<', now());
                        });
                })
                ->count(),
            'abonnements_suspendus' => Abonnement::where('statut', Abonnement::STATUT_SUSPENDU)->count(),
        ];

        // Revenus plateforme (paiements abonnements confirmés)
        $revenusPlateformeQuery = PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME);
        $stats['revenus_plateforme'] = (clone $revenusPlateformeQuery)->sum('montant') ?? 0;
        $stats['revenus_plateforme_mois'] = (clone $revenusPlateformeQuery)
            ->whereYear('date_paiement', $now->year)
            ->whereMonth('date_paiement', $now->month)
            ->sum('montant') ?? 0;
        // Compatibilité avec les autres vues
        $stats['revenus_totaux'] = $stats['revenus_plateforme'];

        // Dépenses plateforme (dépenses sans boutique associée)
        $depensesPlateformeQuery = Depense::withoutGlobalScopes()->whereNull('boutique_id');
        $stats['depenses_plateforme'] = (clone $depensesPlateformeQuery)->sum('montant') ?? 0;
        $stats['depenses_plateforme_mois'] = (clone $depensesPlateformeQuery)
            ->whereYear('date_depense', $now->year)
            ->whereMonth('date_depense', $now->month)
            ->sum('montant') ?? 0;
        $stats['benefice_plateforme'] = $stats['revenus_plateforme'] - $stats['depenses_plateforme'];
        $stats['benefice_plateforme_mois'] = $stats['revenus_plateforme_mois'] - $stats['depenses_plateforme_mois'];

        // Chiffre d'affaires total de tous les commerçants (ventes réelles)
        // Utiliser withoutGlobalScopes pour éviter les filtres de tenant
        $stats['chiffre_affaires_total'] = Vente::withoutGlobalScopes()
            ->sum('total_final') ?? 0;

        // Dépenses totales de tous les commerçants
        $stats['depenses_totales'] = Depense::withoutGlobalScopes()
            ->sum('montant') ?? 0;

        // Nombre total de ventes
        $stats['ventes_totales'] = Vente::withoutGlobalScopes()->count();

        // Bénéfice net (CA - Dépenses)
        $stats['benefice_net'] = $stats['chiffre_affaires_total'] - $stats['depenses_totales'];

        // Nouveaux inscrits ce mois
        $stats['nouveaux_ce_mois'] = User::whereIn('role', $merchantRoles)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Alertes abonnements (expiration dans 7 jours)
        $alertQuery = Abonnement::where('statut', Abonnement::STATUT_ACTIF)
            ->whereBetween('date_expiration', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
        $stats['abonnements_alertes'] = (clone $alertQuery)->count();
        $stats['abonnements_expirent_bientot'] = $stats['abonnements_alertes'];

        // Top 5 commerçants par chiffre d'affaires (somme réelle des ventes)
        $topCommercants = User::whereIn('role', $merchantRoles)
            ->with('boutique')
            ->get()
            ->map(function ($user) {
                // Calculer le CA réel sans les global scopes
                $user->chiffre_affaires = Vente::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->sum('total_final') ?? 0;
                return $user;
            })
            ->sortByDesc('chiffre_affaires')
            ->take(5)
            ->values();

        // Top 5 boutiques par chiffre d'affaires (somme réelle des ventes)
        $topBoutiques = Boutique::with('owner')
            ->get()
            ->map(function ($boutique) {
                // Calculer le CA réel sans les global scopes
                $boutique->chiffre_affaires = Vente::withoutGlobalScopes()
                    ->where('boutique_id', $boutique->id)
                    ->sum('total_final') ?? 0;
                return $boutique;
            })
            ->sortByDesc('chiffre_affaires')
            ->take(5)
            ->values();

        // Abonnements récents
        $abonnementsRecents = Abonnement::with('user')
            ->latest('date_debut')
            ->limit(8)
            ->get();

        // Abonnements à expirer
        $abonnementsAExpirer = (clone $alertQuery)
            ->with('user')
            ->orderBy('date_expiration')
            ->limit(8)
            ->get();

        // Répartition par devise (nombre de boutiques par devise)
        $revenuParDevise = Boutique::select('devise', DB::raw('COUNT(*) as total'))
            ->groupBy('devise')
            ->get();

        // Graphiques : Évolution des abonnements (6 derniers mois)
        $evolutionAbonnements = $this->getEvolutionAbonnements();

        // Graphiques : Évolution des revenus (6 derniers mois)
        $evolutionRevenus = $this->getEvolutionRevenus();

        // Paiements en attente
        $paiementsEnAttente = PaiementAbonnement::with(['abonnement.user', 'user'])
            ->where('statut', PaiementAbonnement::STATUT_EN_ATTENTE)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'topCommercants',
            'topBoutiques',
            'abonnementsRecents',
            'revenuParDevise',
            'abonnementsAExpirer',
            'evolutionAbonnements',
            'evolutionRevenus',
            'paiementsEnAttente'
        ));
    }

    private function getEvolutionAbonnements(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $count = Abonnement::whereYear('date_debut', $date->year)
                ->whereMonth('date_debut', $date->month)
                ->count();

            $data[] = $count;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getEvolutionRevenus(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $revenu = PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME)
                ->whereYear('date_paiement', $date->year)
                ->whereMonth('date_paiement', $date->month)
                ->sum('montant');

            $data[] = $revenu ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
