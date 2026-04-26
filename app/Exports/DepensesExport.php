<?php

namespace App\Exports;

class DepensesExport extends BaseExport
{
    protected $depenses;
    protected $stats;
    protected $dateDebut;
    protected $dateFin;

    public function __construct($depenses, $stats, $dateDebut, $dateFin)
    {
        $this->depenses = $depenses;
        $this->stats = $stats;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function export()
    {
        $spreadsheet = $this->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Rapport Dépenses');

        $headers = [
            'N° Dépense',
            'Date',
            'Description',
            'Catégorie',
            'Boutique',
            'Utilisateur',
            'Montant (FCFA)',
            'Mode de Paiement',
            'Référence'
        ];

        $this->setHeaders($sheet, $headers);
        $this->applyHeaderStyle($sheet, 'A1:I1');

        $row = 2;
        foreach ($this->depenses as $depense) {
            $sheet->setCellValue('A' . $row, $depense->id);
            $sheet->setCellValue('B' . $row, $depense->date_depense->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $depense->description);
            $sheet->setCellValue('D' . $row, $depense->categorie);
            $sheet->setCellValue('E' . $row, $depense->boutique ? $depense->boutique->nom : 'N/A');
            $sheet->setCellValue('F' . $row, $depense->user ? $depense->user->name : 'N/A');
            $sheet->setCellValue('G' . $row, number_format($depense->montant, 0, ',', ' '));
            $sheet->setCellValue('H' . $row, ucfirst(str_replace('_', ' ', $depense->mode_paiement ?? 'Non spécifié')));
            $sheet->setCellValue('I' . $row, $depense->reference ?? '');
            $row++;
        }

        $this->applyDataStyle($sheet, 'A2:I' . ($row - 1));

        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);

        return $spreadsheet;
    }
}
