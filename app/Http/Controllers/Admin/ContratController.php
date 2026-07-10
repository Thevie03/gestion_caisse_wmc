<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $query = Contrat::with(['boutique', 'createur']);

        // Filtres
        if ($request->filled('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_contrat', 'like', "%{$search}%")
                  ->orWhereHas('boutique', function ($bq) use ($search) {
                      $bq->where('nom', 'like', "%{$search}%");
                  });
            });
        }

        $contrats = $query->latest('date_signature')->paginate(20);
        $boutiques = \App\Services\CacheService::getActiveBoutiques();

        $stats = [
            'total' => Contrat::count(),
            'actifs' => Contrat::where('statut', 'actif')->count(),
            'expires' => Contrat::where('statut', 'expire')->count(),
            'resilies' => Contrat::where('statut', 'resilie')->count(),
        ];

        return view('admin.contrats.index', compact('contrats', 'boutiques', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $boutiques = \App\Services\CacheService::getActiveBoutiques();
        return view('admin.contrats.create', compact('boutiques'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
            'type_contrat' => 'required|in:standard,premium,entreprise,personnalise',
            'date_signature' => 'required|date',
            'date_expiration' => 'nullable|date|after:date_signature',
            'fichier_contrat' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'montant' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Générer le numéro de contrat
        $numeroContrat = Contrat::genererNumeroContrat();

        // Upload du fichier
        $fichier = $request->file('fichier_contrat');
        $nomFichier = 'contrats/' . $numeroContrat . '_' . time() . '.' . $fichier->getClientOriginalExtension();
        $cheminFichier = $fichier->storeAs('public', $nomFichier);

        // Créer le contrat
        $contrat = Contrat::create([
            'boutique_id' => $validated['boutique_id'],
            'numero_contrat' => $numeroContrat,
            'type_contrat' => $validated['type_contrat'],
            'date_signature' => $validated['date_signature'],
            'date_expiration' => $validated['date_expiration'] ?? null,
            'fichier_contrat' => $nomFichier,
            'statut' => 'actif',
            'montant' => $validated['montant'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.contrats.show', $contrat)
            ->with('success', 'Contrat créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contrat $contrat)
    {
        $contrat->load(['boutique.owner', 'createur']);
        return view('admin.contrats.show', compact('contrat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrat $contrat)
    {
        $boutiques = \App\Services\CacheService::getActiveBoutiques();
        return view('admin.contrats.edit', compact('contrat', 'boutiques'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contrat $contrat)
    {
        $validated = $request->validate([
            'boutique_id' => 'required|exists:boutiques,id',
            'type_contrat' => 'required|in:standard,premium,entreprise,personnalise',
            'date_signature' => 'required|date',
            'date_expiration' => 'nullable|date|after:date_signature',
            'fichier_contrat' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'statut' => 'required|in:actif,expire,resilie,en_attente',
            'montant' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Si un nouveau fichier est uploadé
        if ($request->hasFile('fichier_contrat')) {
            // Supprimer l'ancien fichier
            if ($contrat->fichier_contrat && Storage::exists('public/' . $contrat->fichier_contrat)) {
                Storage::delete('public/' . $contrat->fichier_contrat);
            }

            // Upload du nouveau fichier
            $fichier = $request->file('fichier_contrat');
            $nomFichier = 'contrats/' . $contrat->numero_contrat . '_' . time() . '.' . $fichier->getClientOriginalExtension();
            $fichier->storeAs('public', $nomFichier);
            $validated['fichier_contrat'] = $nomFichier;
        } else {
            unset($validated['fichier_contrat']);
        }

        $contrat->update($validated);

        return redirect()->route('admin.contrats.show', $contrat)
            ->with('success', 'Contrat mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrat $contrat)
    {
        // Supprimer le fichier
        if ($contrat->fichier_contrat && Storage::exists('public/' . $contrat->fichier_contrat)) {
            Storage::delete('public/' . $contrat->fichier_contrat);
        }

        $contrat->delete();

        return redirect()->route('admin.contrats.index')
            ->with('success', 'Contrat supprimé avec succès.');
    }

    /**
     * Visualiser le fichier du contrat dans le navigateur
     */
    public function view(Contrat $contrat, Request $request)
    {
        if (!Storage::exists('public/' . $contrat->fichier_contrat)) {
            abort(404, 'Fichier introuvable.');
        }

        // Sécurité : Valider le chemin du fichier (protection contre path traversal)
        $cheminFichier = Storage::path('public/' . $contrat->fichier_contrat);
        $realPath = realpath($cheminFichier);
        $allowedPath = realpath(storage_path('app/public/contrats'));

        if (!$realPath || !$allowedPath || !str_starts_with($realPath, $allowedPath) || !file_exists($realPath)) {
            \Illuminate\Support\Facades\Log::warning('Tentative d\'accès à un fichier non autorisé', [
                'user_id' => auth()->id(),
                'contrat_id' => $contrat->id,
                'requested_path' => $cheminFichier
            ]);
            abort(403, 'Accès non autorisé à ce fichier.');
        }

        $extension = strtolower(pathinfo($contrat->fichier_contrat, PATHINFO_EXTENSION));

        // Si inline=1 est demandé, on retourne directement le fichier pour l'iframe
        if ($request->has('inline') && $extension === 'pdf') {
            return response()->file($realPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . htmlspecialchars(basename($contrat->fichier_contrat), ENT_QUOTES, 'UTF-8') . '"',
            ]);
        }

        // Sinon, on retourne la vue avec le fichier
        return view('admin.contrats.view', compact('contrat'));
    }

    /**
     * Télécharger le fichier du contrat
     */
    public function download(Contrat $contrat)
    {
        if (!Storage::exists('public/' . $contrat->fichier_contrat)) {
            abort(404, 'Fichier introuvable.');
        }

        // Sécurité : Valider le chemin du fichier (protection contre path traversal)
        $cheminFichier = Storage::path('public/' . $contrat->fichier_contrat);
        $realPath = realpath($cheminFichier);
        $allowedPath = realpath(storage_path('app/public/contrats'));

        if (!$realPath || !$allowedPath || !str_starts_with($realPath, $allowedPath) || !file_exists($realPath)) {
            \Illuminate\Support\Facades\Log::warning('Tentative de téléchargement d\'un fichier non autorisé', [
                'user_id' => auth()->id(),
                'contrat_id' => $contrat->id,
                'requested_path' => $cheminFichier
            ]);
            abort(403, 'Accès non autorisé à ce fichier.');
        }

        $nomFichier = $contrat->numero_contrat . '.' . pathinfo($contrat->fichier_contrat, PATHINFO_EXTENSION);

        return response()->download($realPath, $nomFichier, [
            'Content-Type' => Storage::mimeType('public/' . $contrat->fichier_contrat),
        ]);
    }
}
