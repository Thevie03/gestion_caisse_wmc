<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'telephone' => 'required|string|max:25',
            'password' => 'required|string|min:8|confirmed',
            'boutique_nom' => 'required|string|max:255',
            'boutique_adresse' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:10',
            'type_abonnement' => 'required|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive',
            'montant' => 'nullable|numeric|min:0',
            'date_debut' => 'nullable|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_debut',
        ];
    }
}
