<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TestPdfController extends Controller
{
    public function test()
    {
        try {
            $pdf = Pdf::loadView('rapports.pdf.ventes', [
                'ventes' => collect(),
                'stats' => [
                    'total_ventes' => 0,
                    'chiffre_affaires' => 0,
                    'moyenne_vente' => 0,
                    'meilleur_jour' => 0,
                    'produits_vendus' => collect(),
                    'ventes_par_mode_paiement' => collect(),
                ],
                'dateDebut' => '2025-01-01',
                'dateFin' => '2025-01-31',
                'typeRapport' => 'mensuel'
            ])->setPaper('a4', 'landscape');

            return $pdf->download('test_rapport.pdf');

        } catch (\Exception $e) {
            \Log::error('Erreur PDF test: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Impossible de générer le PDF de test.',
            ], 500);
        }
    }
}


























