<?php

namespace App\Http\Controllers;

use App\Exports\ProduitImportTemplateExport;
use App\Http\Requests\ProduitImportRequest;
use App\Models\Boutique;
use App\Services\ProduitCodeGenerator;
use App\Services\ProduitImportService;

class ProduitImportController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        $boutiqueId = $this->resolveBoutiqueId($user);

        if (! $boutiqueId) {
            return redirect()
                ->route('produits.index')
                ->with('error', 'Veuillez sélectionner une boutique active avant d\'importer des produits.');
        }

        $boutique = Boutique::find($boutiqueId);
        $lastReport = session('produit_import_report');

        return view('produits.import', compact('boutique', 'lastReport'));
    }

    public function downloadTemplate()
    {
        if (! class_exists(\ZipArchive::class)) {
            return $this->downloadCsvTemplate();
        }

        return (new ProduitImportTemplateExport())->download('modele-import-produits-wmc.xlsx');
    }

    public function downloadCsvTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele-import-produits-wmc.csv"',
        ];

        $callback = static function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Nom du produit', 'Catégorie', 'Quantité', 'Prix de vente'], ';');
            fputcsv($out, ['Savon noir', 'Cosmétiques', '50', '2500'], ';');
            fputcsv($out, ['T-shirt coton', 'Vêtements', '30', '7500'], ';');
            fputcsv($out, ['Chargeur USB', 'Électronique', '15', '12000'], ';');
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(ProduitImportRequest $request)
    {
        $user = auth()->user();
        $boutiqueId = $this->resolveBoutiqueId($user);

        if (! $boutiqueId) {
            return back()
                ->withInput()
                ->with('error', 'Veuillez sélectionner une boutique active avant d\'importer des produits.');
        }

        if ($user->isEmploye() && (int) $user->boutique_id !== (int) $boutiqueId) {
            abort(403, 'Vous ne pouvez importer que pour votre boutique.');
        }

        $importService = new ProduitImportService(ProduitCodeGenerator::fromDatabase());

        try {
            $result = $importService->importFromFile(
                $request->file('fichier'),
                $boutiqueId,
                $user
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Impossible de lire le fichier Excel : ' . $exception->getMessage());
        }

        session()->flash('produit_import_report', [
            'imported' => $result->imported,
            'skipped' => $result->skipped,
            'categories_created' => $result->categoriesCreated,
            'errors' => $result->errors,
            'imported_rows' => array_slice($result->importedRows, 0, 20, true),
        ]);

        if ($result->imported === 0 && $result->hasErrors()) {
            return redirect()
                ->route('produits.import.create')
                ->with('warning', 'Aucun produit importé. Consultez le rapport d\'erreurs ci-dessous.');
        }

        $message = "{$result->imported} produit(s) importé(s) avec succès.";
        if ($result->categoriesCreated > 0) {
            $message .= " {$result->categoriesCreated} catégorie(s) créée(s).";
        }
        if ($result->skipped > 0) {
            $message .= " {$result->skipped} ligne(s) ignorée(s).";
        }

        return redirect()
            ->route('produits.import.create')
            ->with($result->hasErrors() ? 'warning' : 'success', $message);
    }

    private function resolveBoutiqueId($user): ?int
    {
        if ($user->isEmploye()) {
            return $user->boutique_id;
        }

        return session('boutique_active');
    }
}
