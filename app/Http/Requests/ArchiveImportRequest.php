<?php

namespace App\Http\Requests;

use App\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', Archive::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'archive' => ['required', 'file', 'mimes:zip', 'max:512000'],
            'password' => ['nullable', 'string', 'max:191'],
            'confirm_overwrite' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'archive' => 'fichier d\'archive',
            'password' => 'mot de passe',
            'confirm_overwrite' => 'confirmation d\'écrasement',
        ];
    }
}














