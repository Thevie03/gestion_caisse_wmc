<?php

namespace App\Exports;

class FinancierExport extends BaseExport
{
    protected $rapportBoutiques;
    protected $statsGlobales;
    protected $dateDebut;
    protected $dateFin;

    public function __construct($rapportBoutiques, $statsGlobales, $dateDebut, $dateFin)
    {
        $this->rapportBoutiques = $rapportBoutiques;
        $this->statsGlobales = $statsGlobales;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function export()
    {
        $spreadsheet = $this->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Rapport Financier Global');
        
        $headers = [
            'Boutique',
            'Chiffre d\'Affaires (FCFA)',
            'Dépenses (FCFA)',
            'Bénéfice (FCFA)',
            'Marge (%)',
            'Nombre de Ventes',
            'Nombre de Dépenses'
        ];
        
        $this->setHeaders($sheet, $headers);
        $this->applyHeaderStyle($sheet, 'A1:G1');
        
        $row = 2;
        foreach ($this->rapportBoutiques as $rapport) {
            $marge = $rapport['chiffre_affaires'] > 0 
                ? ($rapport['benefice'] / $rapport['chiffre_affaires']) * 100 
                : 0;

            $sheet->setCellValue('A' . $row, $rapport['boutique']->nom);
            $sheet->setCellValue('B' . $row, number_format($rapport['chiffre_affaires'], 0, ',', ' '));
            $sheet->setCellValue('C' . $row, number_format($rapport['depenses'], 0, ',', ' '));
            $sheet->setCellValue('D' . $row, number_format($rapport['benefice'], 0, ',', ' '));
            $sheet->setCellValue('E' . $row, number_format($marge, 2) . '%');
            $sheet->setCellValue('F' . $row, $rapport['nombre_ventes']);
            $sheet->setCellValue('G' . $row, $rapport['nombre_depenses']);
            $row++;
        }
        
        $this->applyDataStyle($sheet, 'A2:G' . ($row - 1));
        
        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);
        
        return $spreadsheet;
    }
}