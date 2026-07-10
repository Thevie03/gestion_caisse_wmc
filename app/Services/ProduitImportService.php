<?php

namespace App\Services;

use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\Produit;
use App\Models\User;
use App\Support\ProduitImportResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProduitImportService
{

    private const COLUMN_ALIASES = [
        'nom' => ['nom du produit', 'nom', 'produit', 'designation', 'désignation', 'designations'],
        'categorie' => ['categorie', 'catégorie', 'category'],
        'quantite' => [
            'quantite', 'quantité', 'qte', 'qty', 'quantite initiale', 'quantité initiale',
            'stock', 'quantite initiale stock',
        ],
        'prix_vente' => ['prix de vente', 'prix vente', 'prix_vente', 'prix', 'prix unitaire'],
        'barcode' => ['code-barres', 'code barres', 'barcode', 'ean', 'ean13', 'code barre'],
        'code_produit' => ['sku', 'code produit', 'code_produit', 'reference', 'référence', 'ref'],
        'prix_achat' => ['prix achat', 'prix d achat', 'prix d\'achat', 'prix_achat', 'cout', 'coût'],
        'description' => ['description', 'desc'],
        'stock_minimum' => ['stock minimum', 'stock_minimum', 'seuil', 'stock min'],
    ];

    private const REQUIRED_COLUMNS = ['nom', 'categorie', 'quantite', 'prix_vente'];

    public function __construct(
        private ProduitCodeGenerator $codeGenerator
    ) {
    }

    public function importFromFile(UploadedFile $file, int $boutiqueId, User $user): ProduitImportResult
    {
        $result = new ProduitImportResult();

        $rows = $this->loadSpreadsheetRows($file);

        if (count($rows) < 2) {
            $result->addError(1, 'Le fichier est vide ou ne contient aucune ligne de données.');

            return $result;
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->mapColumns($headerRow);

        if ($missing = $this->missingRequiredColumns($columnMap)) {
            $result->addError(1, 'Colonnes obligatoires manquantes : ' . implode(', ', $missing));

            return $result;
        }

        $categoryCache = [];

        foreach ($rows as $rowIndex => $row) {
            $line = (int) $rowIndex + 2;
            $data = $this->extractRow($row, $columnMap);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $validationError = $this->validateRow($data);
            if ($validationError !== null) {
                $result->addError($line, $validationError);
                continue;
            }

            $barcode = $this->normalizeOptionalString($data['barcode'] ?? null);
            if ($barcode !== null) {
                if ($this->codeGenerator->barcodeExists($barcode)) {
                    $result->addError($line, "Le code-barres « {$barcode} » est déjà utilisé.");
                    continue;
                }
            }

            $sku = $this->normalizeOptionalString($data['code_produit'] ?? null);
            if ($sku !== null) {
                if ($this->codeGenerator->skuExists($sku)) {
                    $result->addError($line, "Le SKU « {$sku} » est déjà utilisé.");
                    continue;
                }
            }

            try {
                $created = DB::transaction(function () use (
                    $data,
                    $boutiqueId,
                    $user,
                    $barcode,
                    $sku,
                    &$categoryCache,
                    $result
                ) {
                    $categoryName = $this->resolveCategory(
                        (string) $data['categorie'],
                        $boutiqueId,
                        $categoryCache,
                        $result
                    );

                    $produit = Produit::create([
                        'nom' => trim((string) $data['nom']),
                        'categorie' => $categoryName,
                        'description' => $this->normalizeOptionalString($data['description'] ?? null),
                        'prix_achat' => $this->parseOptionalAmount($data['prix_achat'] ?? null) ?? 0,
                        'prix_vente' => $this->parseAmount($data['prix_vente']),
                        'quantite_stock' => (int) $this->parseInteger($data['quantite']),
                        'stock_minimum' => $this->parseOptionalInteger($data['stock_minimum'] ?? null) ?? 0,
                        'code_produit' => $this->codeGenerator->reserveSku($sku),
                        'barcode' => $this->codeGenerator->reserveBarcode($barcode),
                        'boutique_id' => $boutiqueId,
                        'user_id' => $user->id,
                        'actif' => true,
                    ]);

                    if ($produit->quantite_stock > 0) {
                        $mouvement = $produit->mouvementsStock()->create([
                            'type' => 'entree',
                            'quantite' => $produit->quantite_stock,
                            'motif' => 'Import Excel — stock initial',
                            'user_id' => $user->id,
                            'boutique_id' => $boutiqueId,
                        ]);

                        NotificationService::notifierMouvementStock(
                            $mouvement,
                            $produit,
                            $user
                        );
                    }

                    return $produit;
                });

                $result->addImported($line, [
                    'nom' => $created->nom,
                    'categorie' => $created->categorie,
                    'code_produit' => $created->code_produit,
                    'barcode' => $created->barcode,
                    'quantite_stock' => $created->quantite_stock,
                    'prix_vente' => $created->prix_vente,
                ]);
            } catch (\Throwable $exception) {
                $result->addError($line, 'Erreur lors de la création : ' . $exception->getMessage());
            }
        }

        return $result;
    }

    /**
     * Lit un fichier Excel (.xlsx) ou CSV — pas de compression côté application.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadSpreadsheetRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->loadRowsFromCsv($file->getRealPath());
        }

        if (! class_exists(\ZipArchive::class)) {
            throw new \RuntimeException(
                'Impossible de lire le fichier Excel (.xlsx) : l\'extension PHP « zip » n\'est pas activée. ' .
                'Redémarrez XAMPP/Apache, ou importez un fichier .csv (modèle CSV disponible).'
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadRowsFromCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier CSV.');
        }

        $rows = [];
        $lineNum = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($data) === 1 && str_contains((string) $data[0], ',')) {
                $data = str_getcsv((string) $data[0], ',');
            }

            $lineNum++;
            $row = [];
            $colIndex = 0;
            foreach ($data as $cell) {
                $row[$this->columnLetter($colIndex)] = $cell;
                $colIndex++;
            }
            $rows[$lineNum] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        $index++;

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    /**
     * @param  array<string, mixed>  $headerRow
     * @return array<string, string>
     */
    private function mapColumns(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $columnKey => $headerValue) {
            $normalized = $this->normalizeHeader((string) $headerValue);
            if ($normalized === '') {
                continue;
            }

            foreach (self::COLUMN_ALIASES as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$field] = $columnKey;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $columnMap
     * @return array<int, string>
     */
    private function missingRequiredColumns(array $columnMap): array
    {
        $labels = [
            'nom' => 'Nom du produit',
            'categorie' => 'Catégorie',
            'quantite' => 'Quantité',
            'prix_vente' => 'Prix de vente',
        ];

        $missing = [];
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! isset($columnMap[$column])) {
                $missing[] = $labels[$column];
            }
        }

        return $missing;
    }

    /**
     * @param  array<int|string, mixed>  $row
     * @param  array<string, string>  $columnMap
     * @return array<string, mixed>
     */
    private function extractRow(array $row, array $columnMap): array
    {
        $data = [];

        foreach ($columnMap as $field => $columnKey) {
            $data[$field] = $row[$columnKey] ?? null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($this->normalizeOptionalString($value) !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateRow(array $data): ?string
    {
        if ($this->normalizeOptionalString($data['nom'] ?? null) === null) {
            return 'Le nom du produit est obligatoire.';
        }

        if ($this->normalizeOptionalString($data['categorie'] ?? null) === null) {
            return 'La catégorie est obligatoire.';
        }

        if (! $this->isValidInteger($data['quantite'] ?? null)) {
            return 'La quantité doit être un nombre entier valide (≥ 0).';
        }

        $quantite = (int) $this->parseInteger($data['quantite']);
        if ($quantite < 0) {
            return 'La quantité doit être supérieure ou égale à 0.';
        }

        $prixVente = $this->parseOptionalAmount($data['prix_vente'] ?? null);
        if ($prixVente === null) {
            return 'Le prix de vente doit être un nombre valide.';
        }

        if ($prixVente <= 0) {
            return 'Le prix de vente doit être supérieur à 0.';
        }

        if (isset($data['prix_achat']) && $this->normalizeOptionalString($data['prix_achat']) !== null) {
            $prixAchat = $this->parseOptionalAmount($data['prix_achat']);
            if ($prixAchat === null || $prixAchat < 0) {
                return 'Le prix d\'achat doit être un nombre valide (≥ 0).';
            }
        }

        if (isset($data['stock_minimum']) && $this->normalizeOptionalString($data['stock_minimum']) !== null) {
            if (! $this->isValidInteger($data['stock_minimum'])) {
                return 'Le stock minimum doit être un nombre entier valide (≥ 0).';
            }
        }

        return null;
    }

    /**
     * @param  array<string, true>  $categoryCache
     */
    private function resolveCategory(
        string $rawName,
        int $boutiqueId,
        array &$categoryCache,
        ProduitImportResult $result
    ): string {
        $cacheKey = mb_strtolower(trim($rawName));

        if (isset($categoryCache[$cacheKey])) {
            return $categoryCache[$cacheKey];
        }

        $existing = Category::withoutGlobalScopes()
            ->where('boutique_id', $boutiqueId)
            ->whereRaw('LOWER(TRIM(nom)) = ?', [$cacheKey])
            ->first();

        if ($existing) {
            $categoryCache[$cacheKey] = $existing->nom;

            return $existing->nom;
        }

        $category = Category::create([
            'nom' => trim($rawName),
            'icone' => CategoryHelper::getIcon(trim($rawName)),
            'couleur' => CategoryHelper::getColor(trim($rawName)),
            'boutique_id' => $boutiqueId,
            'active' => true,
        ]);

        $result->categoriesCreated++;
        $categoryCache[$cacheKey] = $category->nom;

        return $category->nom;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        $header = str_replace(['_', '-'], ' ', $header);
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ù', 'û', 'ô', 'î', 'ï', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'u', 'u', 'o', 'i', 'i', 'c'],
            $header
        );
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function isValidInteger(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_int($value)) {
            return true;
        }

        if (is_float($value)) {
            return abs($value - round($value)) < 0.00001;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        return is_numeric($normalized) && abs((float) $normalized - (int) round((float) $normalized)) < 0.00001;
    }

    private function parseInteger(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        return (int) round((float) $normalized);
    }

    private function parseOptionalInteger(mixed $value): ?int
    {
        if ($this->normalizeOptionalString($value) === null) {
            return null;
        }

        return $this->parseInteger($value);
    }

    private function parseOptionalAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));
        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized) ?? $normalized;

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function parseAmount(mixed $value): float
    {
        return $this->parseOptionalAmount($value) ?? 0.0;
    }
}
