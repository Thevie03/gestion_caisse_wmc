<?php

namespace App\Support;

class ProduitImportResult
{
    /** @var int */
    public $imported = 0;

    /** @var int */
    public $skipped = 0;

    /** @var int */
    public $categoriesCreated = 0;

    /** @var array<int, string> */
    public $errors = [];

    /** @var array<int, array<string, mixed>> */
    public $importedRows = [];

    public function addError(int $line, string $message): void
    {
        $this->errors[$line] = $message;
        $this->skipped++;
    }

    public function addImported(int $line, array $data): void
    {
        $this->importedRows[$line] = $data;
        $this->imported++;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
