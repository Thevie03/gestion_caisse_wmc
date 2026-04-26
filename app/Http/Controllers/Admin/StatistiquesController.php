<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\PaiementAbonnement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiquesController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee

        // Top boutiques par CA
        $topBoutiques = Boutique::with('owner')
            ->withSum('ventes as chiffre_affaires', 'total_final')
            ->orderByDesc('chiffre_affaires')
            ->limit(10)
            ->get();

        // Utilisateurs actifs par mois (12 derniers mois)
        $utilisateursActifs = $this->getUtilisateursActifsParMois();

        // Revenu total par mois (12 derniers mois)
        $revenusParMois = $this->getRevenusParMois();

        // Abonnements expirés
        $abonnementsExpires = Abonnement::where('statut', Abonnement::STATUT_EXPIRE)
            ->orWhere(function ($query) {
                $query->where('statut', Abonnement::STATUT_ACTIF)
                    ->whereDate('date_expiration', '<', now());
            })
            ->with('user')
            ->latest('date_expiration')
            ->limit(20)
            ->get();

        // Statistiques globales
        $stats = [
            'total_commercants' => User::whereIn('role', ['employe'])->count(),
            'commercants_actifs' => User::whereIn('role', ['employe'])->where('actif', true)->count(),
            'total_boutiques' => Boutique::count(),
            'boutiques_actives' => Boutique::where('actif', true)->count(),
            'abonnements_actifs' => Abonnement::where('statut', Abonnement::STATUT_ACTIF)
                ->whereDate('date_expiration', '>=', now())
                ->count(),
            'abonnements_expires' => Abonnement::where('statut', Abonnement::STATUT_EXPIRE)
                ->orWhere(function ($query) {
                    $query->where('statut', Abonnement::STATUT_ACTIF)
                        ->whereDate('date_expiration', '<', now());
                })
                ->count(),
            'revenus_totaux' => PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME)->sum('montant'),
        ];

        return view('admin.statistiques.index', compact(
            'topBoutiques',
            'utilisateursActifs',
            'revenusParMois',
            'abonnementsExpires',
            'stats',
            'periode'
        ));
    }

    private function getUtilisateursActifsParMois(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $count = User::whereIn('role', ['employe'])
                ->where('actif', true)
                ->whereYear('created_at', '<=', $date->year)
                ->whereMonth('created_at', '<=', $date->month)
                ->count();
            
            $data[] = $count;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getRevenusParMois(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $revenu = PaiementAbonnement::where('statut', PaiementAbonnement::STATUT_CONFIRME)
                ->whereYear('date_paiement', $date->year)
                ->whereMonth('date_paiement', $date->month)
                ->sum('montant');
            
            $data[] = $revenu;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
