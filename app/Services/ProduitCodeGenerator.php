<?php

namespace App\Services;

use App\Models\Produit;

class ProduitCodeGenerator
{
    /** @var array<string, true> */
    private array $usedBarcodes;

    /** @var array<string, true> */
    private array $usedSkus;

    private int $skuSequence;

    public function __construct(array $usedBarcodes = [], array $usedSkus = [], int $skuSequence = 0)
    {
        $this->usedBarcodes = $usedBarcodes;
        $this->usedSkus = $usedSkus;
        $this->skuSequence = $skuSequence;
    }

    public static function fromDatabase(): self
    {
        $barcodes = Produit::withoutGlobalScopes()
            ->whereNotNull('barcode')
            ->pluck('barcode')
            ->filter()
            ->mapWithKeys(fn ($code) => [(string) $code => true])
            ->all();

        $skus = Produit::withoutGlobalScopes()
            ->whereNotNull('code_produit')
            ->pluck('code_produit')
            ->filter()
            ->mapWithKeys(fn ($code) => [(string) $code => true])
            ->all();

        return new self($barcodes, $skus, self::resolveMaxSkuSequence(array_keys($skus)));
    }

    /**
     * @param  array<int, string>  $existingSkus
     */
    public static function resolveMaxSkuSequence(array $existingSkus): int
    {
        $max = 0;

        foreach ($existingSkus as $sku) {
            if (preg_match('/^PRD0*(\d+)$/i', (string) $sku, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }

    public function barcodeExists(string $barcode): bool
    {
        return isset($this->usedBarcodes[$barcode]);
    }

    public function skuExists(string $sku): bool
    {
        return isset($this->usedSkus[$sku]);
    }

    public function reserveBarcode(?string $barcode = null): string
    {
        if ($barcode !== null && $barcode !== '') {
            $this->usedBarcodes[$barcode] = true;

            return $barcode;
        }

        do {
            $candidate = $this->generateEan13Candidate();
        } while ($this->barcodeExists($candidate));

        $this->usedBarcodes[$candidate] = true;

        return $candidate;
    }

    public function reserveSku(?string $sku = null): string
    {
        if ($sku !== null && $sku !== '') {
            $this->usedSkus[$sku] = true;

            return $sku;
        }

        do {
            $this->skuSequence++;
            $candidate = 'PRD' . str_pad((string) $this->skuSequence, 6, '0', STR_PAD_LEFT);
        } while ($this->skuExists($candidate));

        $this->usedSkus[$candidate] = true;

        return $candidate;
    }

    private function generateEan13Candidate(): string
    {
        return '8' . str_pad((string) mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
    }
}
