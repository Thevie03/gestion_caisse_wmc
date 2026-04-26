<?php

namespace App\Http\Controllers;

use App\Models\ConditionAcceptation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConditionGeneraleController extends Controller
{
    /**
     * Afficher les conditions générales d'utilisation
     */
    public function index()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est un propriétaire de boutique
        if (!$user->isOwner()) {
            abort(403, 'Accès réservé aux administrateurs de boutique.');
        }

        // Vérifier si l'utilisateur a déjà accepté les conditions
        $acceptation = ConditionAcceptation::where('user_id', $user->id)->first();

        return view('conditions-generales.index', compact('acceptation'));
    }

    /**
     * Accepter les conditions générales
     */
    public function accept(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est un propriétaire de boutique
        if (!$user->isOwner()) {
            abort(403, 'Accès réservé aux administrateurs de boutique.');
        }

        // Vérifier si l'utilisateur a déjà accepté
        $existingAcceptation = ConditionAcceptation::where('user_id', $user->id)->first();

        if ($existingAcceptation) {
            // Mettre à jour l'acceptation existante
            $existingAcceptation->update([
                'version' => '1.0',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            // Créer une nouvelle acceptation
            ConditionAcceptation::create([
                'user_id' => $user->id,
                'version' => '1.0',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect()->route('conditions-generales.index')
            ->with('success', 'Vous avez accepté les conditions générales d\'utilisation avec succès.');
    }
}
