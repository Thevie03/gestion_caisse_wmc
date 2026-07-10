<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProduitImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'fichier' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fichier.required' => 'Veuillez sélectionner un fichier Excel à importer.',
            'fichier.file' => 'Le fichier sélectionné est invalide.',
            'fichier.mimes' => 'Le fichier doit être au format Excel (.xlsx, .xls) ou CSV.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ];
    }
}
