<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Facture;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\FactureEmail;
use App\Services\MailConfigService;

class FactureController extends Controller
{
    /**
     * Afficher la liste des ventes avec recherche et statistiques
     */
    public function index(Request $request)
    {
        $boutiqueActive = session('boutique_active');
        $user = auth()->user();

        // Construire la requête sur les ventes
        $query = Vente::with(['user', 'boutique', 'venteDetails.produit', 'client', 'facture']);

        // Filtrage par boutique
        // Les employés voient uniquement les ventes de leur boutique
        // Les propriétaires voient les ventes de la boutique active (session)
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $query->where('boutique_id', $boutiqueActive);
        }

        // Filtres de recherche par dates (obligatoires pour les stats)
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        } else {
            // Par défaut, dernier mois
            $query->whereDate('created_at', '>=', now()->subMonth());
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Filtres de recherche supplémentaires
        if ($request->filled('numero_vente')) {
            $query->where('numero_vente', 'like', '%' . $request->numero_vente . '%');
        }

        if ($request->filled('client')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->client . '%')
                  ->orWhere('prenom', 'like', '%' . $request->client . '%');
            });
        }

        if ($request->filled('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->mode_paiement);
        }

        if ($request->filled('statut_paiement')) {
            $query->where('statut_paiement', $request->statut_paiement);
        }

        // Tri par défaut
        $query->orderBy('created_at', 'desc');

        $ventes = $query->paginate(20);

        // Statistiques
        $stats = $this->getStatistiques($request, $user, $boutiqueActive);

        // Boutiques pour le filtre
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->get();
            }
        } else {
            $boutiques = collect();
        }

        return view('factures.index', compact('ventes', 'stats', 'boutiques'));
    }

    /**
     * Afficher les détails d'une facture
     */
    public function show($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer la facture sans le scope global pour vérifier son existence
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès à la facture selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($facture->vente->boutique_id != $user->boutique_id) {
                abort(404, 'Facture introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($facture->vente->boutique_id != $boutiqueId) {
                abort(404, 'Facture introuvable.');
            }
        }

        $facture->load(['vente.user', 'vente.boutique', 'vente.details.produit']);

        return view('factures.show', compact('facture'));
    }

    /**
     * Générer et télécharger une facture PDF
     */
    public function telecharger($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès à la facture selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($facture->vente->boutique_id != $user->boutique_id) {
                abort(404, 'Facture introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($facture->vente->boutique_id != $boutiqueId) {
                abort(404, 'Facture introuvable.');
            }
        }

        $facture->load(['vente.user', 'vente.boutique:id,nom,theme_color,logo,adresse,telephone,email', 'vente.details.produit', 'vente.paiements']);

        // Récupérer la boutique et son thème
        $boutique = $facture->vente->boutique;
        // S'assurer que theme_color est chargé
        if ($boutique && !isset($boutique->theme_color)) {
            $boutique = Boutique::select('id', 'nom', 'theme_color', 'logo', 'adresse', 'telephone', 'email')->find($boutique->id);
        }
        $themeColor = $boutique ? ($boutique->theme_color ?? '#475569') : '#475569';

        $pdf = Pdf::loadView('factures.pdf', compact('facture', 'boutique', 'themeColor'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $filename = 'Facture_' . $facture->numero_facture . '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Afficher une facture dans le navigateur
     */
    public function afficher($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès à la facture selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($facture->vente->boutique_id != $user->boutique_id) {
                abort(404, 'Facture introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($facture->vente->boutique_id != $boutiqueId) {
                abort(404, 'Facture introuvable.');
            }
        }

        $facture->load(['vente.user', 'vente.boutique:id,nom,theme_color,logo,adresse,telephone,email', 'vente.details.produit', 'vente.paiements']);

        // Ajouter le logo de la boutique
        $logo = $facture->vente->boutique->logo;

        // Récupérer la boutique et son thème
        $boutique = $facture->vente->boutique;
        // S'assurer que theme_color est chargé
        if ($boutique && !isset($boutique->theme_color)) {
            $boutique = Boutique::select('id', 'nom', 'theme_color', 'logo', 'adresse', 'telephone', 'email')->find($boutique->id);
        }
        $themeColor = $boutique ? ($boutique->theme_color ?? '#475569') : '#475569';

        return view('factures.pdf', compact('facture', 'logo', 'boutique', 'themeColor'));
    }

    /**
     * Générer le prochain numéro de facture
     */
    public function genererNumeroFacture($boutiqueId = null)
    {
        $prefixe = 'FAC';
        $annee = date('Y');
        $mois = date('m');

        // Compter les factures de ce mois
        $query = Vente::whereYear('created_at', $annee)
                     ->whereMonth('created_at', $mois);

        if ($boutiqueId) {
            $query->where('boutique_id', $boutiqueId);
        }

        $nombre = $query->count() + 1;

        return $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir les statistiques des ventes
     */
    private function getStatistiques($request, $user, $boutiqueActive)
    {
        $query = Vente::query();

        // Filtrage par boutique
        if ($user->isEmploye()) {
            $query->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $query->where('boutique_id', $boutiqueActive);
        }

        // Filtres de date
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        } else {
            $query->whereDate('created_at', '>=', now()->subMonth());
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Appliquer les mêmes filtres que pour la liste
        if ($request->filled('numero_vente')) {
            $query->where('numero_vente', 'like', '%' . $request->numero_vente . '%');
        }

        if ($request->filled('client')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->client . '%')
                  ->orWhere('prenom', 'like', '%' . $request->client . '%');
            });
        }

        if ($request->filled('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->mode_paiement);
        }

        if ($request->filled('statut_paiement')) {
            $query->where('statut_paiement', $request->statut_paiement);
        }

        // Optimisation : Calculer toutes les statistiques en une seule requête au lieu de charger toutes les ventes
        $statsData = (clone $query)
            ->selectRaw('
                COUNT(*) as total_ventes,
                COALESCE(SUM(total_final), 0) as chiffre_affaires,
                COALESCE(SUM(CASE WHEN mode_paiement = "especes" THEN total_final ELSE 0 END), 0) as especes,
                COALESCE(SUM(CASE WHEN mode_paiement IN ("mobile_money", "wave", "orange_money", "mtn_money") THEN total_final ELSE 0 END), 0) as mobile_money,
                COALESCE(SUM(CASE WHEN mode_paiement = "carte" THEN total_final ELSE 0 END), 0) as carte,
                COALESCE(SUM(CASE WHEN statut_paiement = "complet" THEN total_final ELSE 0 END), 0) as soldes,
                COALESCE(SUM(CASE WHEN statut_paiement = "partiel" THEN total_final ELSE 0 END), 0) as partiels,
                COALESCE(SUM(CASE WHEN statut_paiement = "impaye" THEN total_final ELSE 0 END), 0) as impayes
            ')
            ->first();

        // Calculer le nombre de produits vendus (optimisé avec une sous-requête)
        $produitsVendus = DB::table('vente_details')
            ->join('ventes', 'vente_details.vente_id', '=', 'ventes.id')
            ->when($user->isEmploye(), function($q) use ($user) {
                $q->where('ventes.boutique_id', $user->boutique_id);
            })
            ->when($boutiqueActive && !$user->isEmploye(), function($q) use ($boutiqueActive) {
                $q->where('ventes.boutique_id', $boutiqueActive);
            })
            ->when($request->filled('date_debut'), function($q) use ($request) {
                $q->whereDate('ventes.created_at', '>=', $request->date_debut);
            })
            ->when($request->filled('date_fin'), function($q) use ($request) {
                $q->whereDate('ventes.created_at', '<=', $request->date_fin);
            })
            ->sum('vente_details.quantite');

        // Statistiques aujourd'hui (optimisé)
        $queryAujourdhui = Vente::query();
        if ($user->isEmploye()) {
            $queryAujourdhui->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueActive) {
            $queryAujourdhui->where('boutique_id', $boutiqueActive);
        }
        $statsAujourdhui = $queryAujourdhui
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as total_ventes_aujourd_hui,
                COALESCE(SUM(total_final), 0) as chiffre_affaires_aujourd_hui
            ')
            ->first();

        $totalVentes = $statsData->total_ventes ?? 0;
        $chiffreAffaires = $statsData->chiffre_affaires ?? 0;
        $moyenneVente = $totalVentes > 0 ? $chiffreAffaires / $totalVentes : 0;
        $totalVentesAujourdhui = $statsAujourdhui->total_ventes_aujourd_hui ?? 0;
        $chiffreAffairesAujourdhui = $statsAujourdhui->chiffre_affaires_aujourd_hui ?? 0;

        $stats = [
            'total_ventes' => $totalVentes,
            'chiffre_affaires' => $chiffreAffaires,
            'moyenne_vente' => $moyenneVente,
            'total_ventes_aujourd_hui' => $totalVentesAujourdhui,
            'chiffre_affaires_aujourd_hui' => $chiffreAffairesAujourdhui,
            'especes' => $statsData->especes ?? 0,
            'mobile_money' => $statsData->mobile_money ?? 0,
            'carte' => $statsData->carte ?? 0,
            'soldes' => $statsData->soldes ?? 0,
            'partiels' => $statsData->partiels ?? 0,
            'impayes' => $statsData->impayes ?? 0,
            'produits_vendus' => $produitsVendus,
        ];

        return $stats;
    }

    /**
     * Afficher le formulaire de modification (Admin seulement)
     */
    public function edit($id)
    {
        // Vérifier que seul un admin peut modifier une facture
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent modifier une facture.');
        }

        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        $facture->load(['vente.user', 'vente.boutique', 'vente.details.produit', 'vente.client']);

        // Récupérer les clients actifs de la boutique
        $boutiqueId = session('boutique_active') ?? $facture->vente->boutique_id;
        $clientsQuery = \App\Models\Client::where('actif', true);
        if ($user->isEmploye() || $user->isOwner()) {
            $clientsQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $clientsQuery->where('boutique_id', $boutiqueId);
        }
        $clients = $clientsQuery->orderBy('nom')->get();

        return view('factures.edit', compact('facture', 'clients'));
    }

    /**
     * Mettre à jour une facture (Admin seulement)
     */
    public function update(Request $request, $id)
    {
        // Vérifier que seul un admin peut modifier une facture
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent modifier une facture.');
        }

        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        $vente = $facture->vente;

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'remise' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,carte,mobile_money',
            'statut_paiement' => 'required|in:complet,partiel,impaye',
        ]);

        DB::beginTransaction();
        try {
            // Calculer le nouveau total final si la remise change
            $remise = $request->remise ?? 0;
            $totalSansRemise = $vente->details->sum('sous_total');
            $nouveauTotalFinal = $totalSansRemise - $remise;

            // Mettre à jour la vente
            $vente->update([
                'client_id' => $request->client_id,
                'remise' => $remise,
                'total' => $totalSansRemise,
                'total_final' => $nouveauTotalFinal,
                'mode_paiement' => $request->mode_paiement,
                'statut_paiement' => $request->statut_paiement,
                'notes' => $request->notes,
            ]);

            // Recalculer le solde restant selon le statut de paiement
            $montantTotalPaye = $vente->paiements()->sum('montant');

            if ($request->statut_paiement === 'complet') {
                $vente->montant_paye = $nouveauTotalFinal;
                $vente->solde_restant = 0;
            } elseif ($request->statut_paiement === 'partiel') {
                // Conserver les paiements existants ou utiliser le montant payé actuel
                $vente->montant_paye = $montantTotalPaye > 0 ? $montantTotalPaye : ($nouveauTotalFinal / 2);
                $vente->solde_restant = $nouveauTotalFinal - $vente->montant_paye;
            } else {
                // impaye
                $vente->montant_paye = 0;
                $vente->solde_restant = $nouveauTotalFinal;
            }

            $vente->save();

            DB::commit();

            return redirect()->route('factures.show', $facture)
                ->with('success', 'Facture modifiée avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la modification : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprimer une facture (Admin seulement)
     */
    public function destroy($id)
    {
        // Vérifier que seul un admin peut supprimer une facture
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent supprimer une facture.');
        }

        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        // Supprimer les détails de vente
        $facture->vente->details()->delete();

        // Supprimer la vente
        $facture->vente->delete();

        // Supprimer la facture
        $facture->delete();

        return redirect()->route('factures.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Envoyer la facture par email au client
     *
     * @param Facture $facture
     * @return \Illuminate\Http\RedirectResponse
     */
    public function envoyerEmail($id)
    {
        // Récupérer la facture sans le scope global
        $facture = Facture::withoutGlobalScopes()->findOrFail($id);

        // Toujours recharger les relations nécessaires pour l'email avec toutes les colonnes requises
        // Cela garantit que toutes les données sont disponibles même si la facture vient de la page show()
        $facture->load([
            'vente.user:id,name',
            'vente.boutique:id,nom,theme_color,logo,adresse,telephone,email,mail_mailer,mail_host,mail_port,mail_username,mail_password,mail_encryption,mail_from_address,mail_from_name',
            'vente.details.produit:id,nom',
            'vente.client:id,nom,prenom,email,telephone'
        ]);

        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Vérifier l'accès à la facture selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($facture->vente->boutique_id != $user->boutique_id) {
                abort(404, 'Facture introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($facture->vente->boutique_id != $boutiqueId) {
                abort(404, 'Facture introuvable.');
            }
        }

        // Vérifier que le client a un email
        if (!$facture->vente->client) {
            return back()->with('error', 'Cette vente n\'a pas de client associé. Impossible d\'envoyer l\'email.');
        }

        $client = $facture->vente->client;

        if (empty($client->email)) {
            return back()->with('error', 'Le client n\'a pas d\'adresse email renseignée. Veuillez d\'abord ajouter l\'email du client.');
        }

        try {
            // Configurer le mailer avec les paramètres de la boutique
            $boutique = $facture->vente->boutique;
            MailConfigService::configureForBoutique($boutique);

            // Envoyer l'email avec la facture en pièce jointe
            Mail::to($client->email)
                ->send(new FactureEmail($facture));

            return back()->with('success', "Facture envoyée avec succès à {$client->email} !");
        } catch (\Exception $e) {
            // Logger l'erreur pour le débogage
            Log::error('Erreur lors de l\'envoi de la facture par email', [
                'facture_id' => $facture->id,
                'client_email' => $client->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
    }
}
