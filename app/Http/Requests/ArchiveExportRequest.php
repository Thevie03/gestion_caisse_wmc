<?php

namespace App\Http\Requests;

use App\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Archive::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'encrypt' => ['nullable', 'boolean'],
            'boutique_id' => ['nullable', 'integer', 'exists:boutiques,id'],
            'archive_all_boutiques' => ['nullable', 'boolean'],
            'archive_system_data' => ['nullable', 'boolean'], // Nouveau : archivage des données système
        ];
    }

    public function encrypt(): bool
    {
        return (bool) $this->boolean('encrypt');
    }
}














