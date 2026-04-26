<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class VentesExport extends BaseExport
{
    protected $ventes;
    protected $stats;
    protected $dateDebut;
    protected $dateFin;
    protected $typeRapport;

    public function __construct($ventes, $stats, $dateDebut, $dateFin, $typeRapport)
    {
        $this->ventes = $ventes;
        $this->stats = $stats;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->typeRapport = $typeRapport;
    }

    public function export()
    {
        $spreadsheet = $this->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Titre du rapport
        $sheet->setTitle('Rapport Ventes ' . ucfirst($this->typeRapport));

        // En-têtes
        $headers = [
            'N° Facture',
            'Date',
            'Heure',
            'Client',
            'Téléphone Client',
            'Boutique',
            'Vendeur',
            'Mode de Paiement',
            'Total (FCFA)',
            'Remise (FCFA)',
            'Total Final (FCFA)'
        ];

        $this->setHeaders($sheet, $headers);
        $this->applyHeaderStyle($sheet, 'A1:K1');

        // Ajouter les données
        $row = 2;
        foreach ($this->ventes as $vente) {
            $sheet->setCellValue('A' . $row, $vente->numero_vente);
            $sheet->setCellValue('B' . $row, $vente->created_at->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $vente->created_at->format('H:i:s'));
            $sheet->setCellValue('D' . $row, $vente->client ? $vente->client->nom_complet : 'Client anonyme');
            $sheet->setCellValue('E' . $row, $vente->client ? $vente->client->telephone : '');
            $sheet->setCellValue('F' . $row, $vente->boutique ? $vente->boutique->nom : 'N/A');
            $sheet->setCellValue('G' . $row, $vente->user ? $vente->user->name : 'N/A');
            $modeAffiche = $vente->mode_paiement_display ?? $vente->mode_paiement;
            $sheet->setCellValue('H' . $row, ucfirst(str_replace('_', ' ', $modeAffiche)));
            $sheet->setCellValue('I' . $row, number_format($vente->total, 0, ',', ' '));
            $sheet->setCellValue('J' . $row, number_format($vente->remise, 0, ',', ' '));
            $sheet->setCellValue('K' . $row, number_format($vente->total_final, 0, ',', ' '));
            $row++;
        }

        $this->applyDataStyle($sheet, 'A2:K' . ($row - 1));

        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(18);

        return $spreadsheet;
    }
}
