<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParametreSysteme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametresSystemeController extends Controller
{
    public function __construct()
    {
        // Seul le super admin peut accéder aux paramètres système
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isSuperAdmin()) {
                abort(403, 'Accès non autorisé. Seul le super administrateur peut modifier les paramètres système.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $parametres = ParametreSysteme::orderBy('cle')->get();
        return view('admin.parametres.index', compact('parametres'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_nom' => 'required|string|max:255',
            'devise_defaut' => 'required|string|max:10',
            'periode_essai_jours' => 'required|integer|min:0',
            'rappel_abonnement_jours' => 'required|integer|min:1',
            'message_rappel_abonnement' => 'required|string',
            'cgu' => 'required|string', // Les CGU sont maintenant obligatoires
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Mettre à jour les paramètres
        ParametreSysteme::set('app_nom', $request->app_nom);
        ParametreSysteme::set('devise_defaut', $request->devise_defaut);
        ParametreSysteme::set('periode_essai_jours', $request->periode_essai_jours, 'integer');
        ParametreSysteme::set('rappel_abonnement_jours', $request->rappel_abonnement_jours, 'integer');
        ParametreSysteme::set('message_rappel_abonnement', $request->message_rappel_abonnement);
        
        // Mettre à jour les CGU - cela sera visible dans toutes les boutiques
        ParametreSysteme::set('cgu', $request->cgu);

        // Gérer l'upload du logo
        if ($request->hasFile('app_logo')) {
            $logo = $request->file('app_logo');
            $logoName = 'logo_systeme_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/logos'), $logoName);
            ParametreSysteme::set('app_logo', 'images/logos/' . $logoName);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Paramètres mis à jour avec succès. Les nouvelles CGU sont maintenant visibles dans toutes les boutiques.');
    }
}
