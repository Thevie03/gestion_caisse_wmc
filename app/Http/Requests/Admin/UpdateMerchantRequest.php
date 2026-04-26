<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($user?->id ?? 'NULL'),
            'telephone' => 'required|string|max:25',
            'boutique_nom' => 'required|string|max:255',
            'boutique_adresse' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:10',
            'password' => 'nullable|string|min:8|confirmed',
            'actif' => 'nullable|boolean',
        ];
    }
}
