<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProduitImportTemplateExport extends BaseExport
{
    public function export()
    {
        $spreadsheet = $this->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import produits');

        $headers = [
            'Nom du produit',
            'Catégorie',
            'Quantité',
            'Prix de vente',
        ];

        $this->setHeaders($sheet, $headers);
        $this->applyHeaderStyle($sheet, 'A1:D1');

        $examples = [
            ['Savon noir', 'Cosmétiques', 50, 2500],
            ['T-shirt coton', 'Vêtements', 30, 7500],
            ['Chargeur USB', 'Électronique', 15, 12000],
        ];

        $row = 2;
        foreach ($examples as $example) {
            $sheet->fromArray($example, null, 'A' . $row);
            $row++;
        }

        $this->applyDataStyle($sheet, 'A2:D' . ($row - 1));

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);

        $sheet->setCellValue('A6', 'Instructions :');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->setCellValue('A7', '• Seules les 4 colonnes ci-dessus sont obligatoires.');
        $sheet->setCellValue('A8', '• SKU, code-barres, prix d\'achat et description sont générés automatiquement si absents.');
        $sheet->setCellValue('A9', '• Les catégories inexistantes seront créées automatiquement.');
        $sheet->setCellValue('A10', '• Supprimez les lignes d\'exemple avant l\'import si vous ne les voulez pas.');

        $sheet->getStyle('A6:A10')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF7ED');

        return $spreadsheet;
    }
}
