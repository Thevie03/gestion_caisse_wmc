<?php

namespace App\Exports;

class StockExport extends BaseExport
{
    protected $produits;
    protected $stats;

    public function __construct($produits, $stats)
    {
        $this->produits = $produits;
        $this->stats = $stats;
    }

    public function export()
    {
        $spreadsheet = $this->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Rapport Stock');

        $headers = [
            'Produit',
            'Description',
            'Catégorie',
            'Boutique',
            'Stock Actuel',
            'Stock Minimum',
            'Prix d\'Achat (FCFA)',
            'Prix de Vente (FCFA)',
            'Valeur Stock (FCFA)',
            'Statut'
        ];

        $this->setHeaders($sheet, $headers);
        $this->applyHeaderStyle($sheet, 'A1:J1');

        $row = 2;
        foreach ($this->produits as $produit) {
            $statut = '';
            if ($produit->quantite_stock == 0) {
                $statut = 'Rupture';
            } elseif ($produit->quantite_stock <= $produit->stock_minimum) {
                $statut = 'Stock Faible';
            } else {
                $statut = 'Normal';
            }

            $sheet->setCellValue('A' . $row, $produit->nom);
            $sheet->setCellValue('B' . $row, $produit->description ?? '');
            $sheet->setCellValue('C' . $row, $produit->categorie);
            $sheet->setCellValue('D' . $row, $produit->boutique ? $produit->boutique->nom : 'N/A');
            $sheet->setCellValue('E' . $row, $produit->quantite_stock);
            $sheet->setCellValue('F' . $row, $produit->stock_minimum);
            $sheet->setCellValue('G' . $row, number_format($produit->prix_achat, 0, ',', ' '));
            $sheet->setCellValue('H' . $row, number_format($produit->prix_vente, 0, ',', ' '));
            $sheet->setCellValue('I' . $row, number_format($produit->quantite_stock * $produit->prix_achat, 0, ',', ' '));
            $sheet->setCellValue('J' . $row, $statut);
            $row++;
        }

        $this->applyDataStyle($sheet, 'A2:J' . ($row - 1));

        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(15);

        return $spreadsheet;
    }
}
