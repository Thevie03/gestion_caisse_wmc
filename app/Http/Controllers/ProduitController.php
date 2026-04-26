<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Produit;
use App\Models\Boutique;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Construire la requête
        $baseQuery = Produit::with('boutique');

        // Filtrer par boutique
        // Les employés voient uniquement les produits de leur boutique
        // Les propriétaires voient les produits de la boutique active (session)
        if ($user->isEmploye()) {
            $baseQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $baseQuery->where('boutique_id', $boutiqueId);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('categorie', 'like', "%{$search}%")
                  ->orWhere('code_produit', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($request->filled('categorie')) {
            $baseQuery->where('categorie', $request->categorie);
        }

        // Filtre par statut de stock
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'faible':
                    $baseQuery->whereRaw('quantite_stock <= stock_minimum');
                    break;
                case 'rupture':
                    $baseQuery->where('quantite_stock', 0);
                    break;
                case 'disponible':
                    $baseQuery->where('quantite_stock', '>', 0);
                    break;
            }
        }

        // Optimisation : Select uniquement les colonnes nécessaires
        $produits = (clone $baseQuery)
            ->select('produits.id', 'produits.nom', 'produits.code_produit', 'produits.categorie',
                     'produits.prix_vente', 'produits.quantite_stock', 'produits.stock_minimum',
                     'produits.boutique_id', 'produits.image', 'produits.created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Optimisation : Calculer toutes les statistiques en une seule requête
        $statsQuery = clone $baseQuery;
        $statsData = $statsQuery
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN quantite_stock <= stock_minimum THEN 1 ELSE 0 END) as stock_faible,
                SUM(CASE WHEN quantite_stock = 0 THEN 1 ELSE 0 END) as rupture,
                COALESCE(SUM(quantite_stock * prix_achat), 0) as valeur_stock
            ')
            ->first();

        $stats = [
            'total' => $statsData->total ?? 0,
            'stock_faible' => $statsData->stock_faible ?? 0,
            'rupture' => $statsData->rupture ?? 0,
            'valeur_stock' => $statsData->valeur_stock ?? 0,
        ];

        // Statistiques par catégorie
        $statsParCategorie = Produit::select('categorie',
                DB::raw('COUNT(*) as total_produits'),
                DB::raw('SUM(quantite_stock) as total_stock'),
                DB::raw('SUM(quantite_stock * prix_achat) as valeur_stock'),
                DB::raw('SUM(CASE WHEN quantite_stock <= stock_minimum THEN 1 ELSE 0 END) as stock_faible'),
                DB::raw('SUM(CASE WHEN quantite_stock = 0 THEN 1 ELSE 0 END) as rupture')
            )
            ->when($user->isEmploye(), function($q) use ($user) {
                $q->where('boutique_id', $user->boutique_id);
            })
            ->when($boutiqueId && !$user->isEmploye(), function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            })
            ->groupBy('categorie')
            ->orderBy('total_produits', 'desc')
            ->get();

        // Catégories pour le filtre avec icônes
        $categories = Produit::select('categorie')
            ->when($user->isEmploye(), function($q) use ($user) {
                $q->where('boutique_id', $user->boutique_id);
            })
            ->when($boutiqueId && !$user->isEmploye(), function($q) use ($boutiqueId) {
                $q->where('boutique_id', $boutiqueId);
            })
            ->distinct()
            ->pluck('categorie')
            ->filter();

        // Produits groupés par catégorie (si pas de filtre spécifique)
        $produitsParCategorie = [];
        if (!$request->filled('categorie') && !$request->filled('search')) {
            $produitsParCategorie = Produit::with('boutique')
                ->when($user->isEmploye(), function($q) use ($user) {
                    $q->where('boutique_id', $user->boutique_id);
                })
                ->when($boutiqueId, function($q) use ($boutiqueId) {
                    $q->where('boutique_id', $boutiqueId);
                })
                ->orderBy('categorie')
                ->orderBy('nom')
                ->get()
                ->groupBy('categorie');
        }

        return view('produits.index', compact('produits', 'stats', 'categories', 'statsParCategorie', 'produitsParCategorie'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        $boutiquesQuery = Boutique::where('actif', true)->orderBy('nom');
        $boutiqueId = session('boutique_active');

        // Les employés voient uniquement leur boutique
        // Les propriétaires voient la boutique active de la session
        if ($user->isEmploye()) {
            $boutiques = $boutiquesQuery->where('id', $user->boutique_id)->get();
            $boutiqueId = $user->boutique_id;
        } elseif ($user->isOwner()) {
            // Pour les propriétaires, récupérer toutes leurs boutiques
            $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                $ownedBoutiques = collect([$user->boutique]);
            }
            $boutiques = $ownedBoutiques;
            // Utiliser la boutique active de la session, ou fallback sur la première boutique
            if (!$boutiqueId && $ownedBoutiques->isNotEmpty()) {
                $boutiqueId = $ownedBoutiques->first()->id;
            } elseif (!$boutiqueId && $user->boutique_id) {
                $boutiqueId = $user->boutique_id;
            }
        } elseif ($user->isSuperAdmin()) {
            // Seul le super admin voit toutes les boutiques
            $boutiques = $boutiquesQuery->get();
            if (!$boutiqueId && $boutiques->isNotEmpty()) {
                $boutiqueId = $boutiques->first()->id;
            }
        } else {
            // Pour les autres utilisateurs (non propriétaires, non super admin), pas de boutiques
            $boutiques = collect();
        }

        // Récupérer les catégories en désactivant temporairement le scope global
        // pour pouvoir les grouper par boutique, puis filtrer par la boutique active
        $categoriesParBoutique = Category::withoutGlobalScopes()
            ->active()
            ->orderBy('nom')
            ->get()
            ->groupBy('boutique_id')
            ->map(function($categories) {
                return $categories->map(fn($categorie) => [
                    'id' => $categorie->id,
                    'nom' => $categorie->nom,
                ]);
            })
            ->mapWithKeys(function($categories, $key) {
                return [(string) ($key ?? '') => $categories];
            });

        // Filtrer les catégories pour la boutique active
        $categories = $boutiqueId
            ? $categoriesParBoutique->get((string) $boutiqueId, collect())
            : collect();

        // Récupérer les fournisseurs actifs de la boutique
        $fournisseursQuery = \App\Models\Fournisseur::where('actif', true);
        if ($user->isEmploye()) {
            $fournisseursQuery->where('boutique_id', $user->boutique_id);
        } elseif ($boutiqueId) {
            $fournisseursQuery->where('boutique_id', $boutiqueId);
        }
        $fournisseurs = $fournisseursQuery->orderBy('nom')->get();

        return view('produits.create', compact(
            'boutiques',
            'categories',
            'categoriesParBoutique',
            'fournisseurs',
            'boutiqueId',
            'user'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'categorie' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prix_achat' => 'nullable|numeric|min:0',
                'prix_vente' => 'required|numeric|min:0',
                'quantite_stock' => 'nullable|integer|min:0',
                'stock_minimum' => 'nullable|integer|min:0',
                'boutique_id' => 'required|exists:boutiques,id',
                'barcode' => 'nullable|string|max:50|unique:produits,barcode',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'nom.required' => 'Le nom du produit est obligatoire.',
                'nom.max' => 'Le nom du produit ne doit pas dépasser 255 caractères.',
                'categorie.required' => 'La catégorie est obligatoire.',
                'categorie.max' => 'La catégorie ne doit pas dépasser 255 caractères.',
                'prix_achat.numeric' => 'Le prix d\'achat doit être un nombre.',
                'prix_achat.min' => 'Le prix d\'achat doit être supérieur ou égal à 0.',
                'prix_vente.required' => 'Le prix de vente est obligatoire.',
                'prix_vente.numeric' => 'Le prix de vente doit être un nombre.',
                'prix_vente.min' => 'Le prix de vente doit être supérieur ou égal à 0.',
                'quantite_stock.integer' => 'La quantité en stock doit être un nombre entier.',
                'quantite_stock.min' => 'La quantité en stock doit être supérieure ou égale à 0.',
                'stock_minimum.integer' => 'Le stock minimum doit être un nombre entier.',
                'stock_minimum.min' => 'Le stock minimum doit être supérieur ou égal à 0.',
                'boutique_id.required' => 'La boutique est obligatoire.',
                'boutique_id.exists' => 'La boutique sélectionnée n\'existe pas.',
                'barcode.max' => 'Le code-barres ne doit pas dépasser 50 caractères.',
                'barcode.unique' => 'Ce code-barres est déjà utilisé par un autre produit.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
                'image.max' => 'L\'image ne doit pas dépasser 2 Mo.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $data = $request->all();

        // Générer un code produit unique
        $data['code_produit'] = 'PRD-' . strtoupper(Str::random(8));

        // Générer un code-barres unique automatiquement si non fourni
        if (empty($data['barcode']) || $data['barcode'] === null || $data['barcode'] === '') {
            $data['barcode'] = $this->genererCodeBarresUnique();
        }

        // Si le prix d'achat n'est pas renseigné, le mettre à 0
        if (empty($data['prix_achat'])) {
            $data['prix_achat'] = 0;
        }

        // Si la quantité en stock n'est pas renseignée, la mettre à 0
        if (!isset($data['quantite_stock']) || $data['quantite_stock'] === '' || $data['quantite_stock'] === null) {
            $data['quantite_stock'] = 0;
        }

        // Si le stock minimum n'est pas renseigné, le mettre à 0
        if (!isset($data['stock_minimum']) || $data['stock_minimum'] === '' || $data['stock_minimum'] === null) {
            $data['stock_minimum'] = 0;
        }

        // Gérer l'upload d'image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/produits'), $imageName);
            $data['image'] = 'images/produits/' . $imageName;
        }

        $produit = Produit::create($data);

        // Créer un mouvement de stock pour l'ajout initial seulement si la quantité est > 0
        // Utiliser la valeur normalisée de $data au lieu de $request pour éviter les valeurs null
        $quantiteStock = $data['quantite_stock'] ?? 0;
        if ($quantiteStock > 0) {
            $mouvement = $produit->mouvementsStock()->create([
                'type' => 'entree',
                'quantite' => $quantiteStock,
                'motif' => 'Stock initial',
                'user_id' => auth()->id(),
                'boutique_id' => $produit->boutique_id
            ]);

            // Déclencher la notification
            NotificationService::notifierMouvementStock($mouvement, $produit, auth()->user());
        }

        // Si c'est une requête AJAX (depuis le POS), retourner JSON
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès !',
                'produit' => [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'categorie' => $produit->categorie,
                    'prix_vente' => $produit->prix_vente,
                    'quantite_stock' => $produit->quantite_stock,
                    'image' => $produit->image
                ]
            ]);
        }

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer le produit sans le scope global pour vérifier son existence
        $produit = Produit::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès au produit selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            // Les employés ne peuvent voir que les produits de leur boutique
            if ($user->isEmploye() && $produit->boutique_id != $user->boutique_id) {
                abort(404, 'Produit introuvable.');
            }
            // Les propriétaires peuvent voir les produits de toutes leurs boutiques
            if ($user->isOwner() && !$user->ownsBoutique($produit->boutique_id)) {
                abort(404, 'Produit introuvable.');
            }
        } elseif ($boutiqueId) {
            // Pour les autres utilisateurs (admins), vérifier que le produit appartient à la boutique active
            if ($produit->boutique_id != $boutiqueId) {
                abort(404, 'Produit introuvable.');
            }
        }

        $produit->load(['boutique', 'mouvementsStock.user']);

        // Statistiques du produit
        $stats = [
            'ventes_total' => $produit->venteDetails()->sum('quantite'),
            'chiffre_affaires' => $produit->venteDetails()->sum(DB::raw('quantite * prix_unitaire')),
            'derniere_vente' => $produit->venteDetails()
                ->join('ventes', 'vente_details.vente_id', '=', 'ventes.id')
                ->orderBy('ventes.created_at', 'desc')
                ->first()
        ];

        return view('produits.show', compact('produit', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer le produit sans le scope global pour vérifier son existence
        $produit = Produit::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès au produit selon les permissions
        // Les employés ne peuvent accéder qu'à leur boutique
        if ($user->isEmploye() && $produit->boutique_id != $user->boutique_id) {
            abort(404, 'Produit introuvable.');
        }
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isOwner() && !$user->ownsBoutique($produit->boutique_id)) {
            abort(404, 'Produit introuvable.');
        }
        // Pour les autres utilisateurs (admins), vérifier que le produit appartient à la boutique active
        if (!$user->isEmploye() && !$user->isOwner() && $boutiqueId && $produit->boutique_id != $boutiqueId) {
            abort(404, 'Produit introuvable.');
        }

        $boutiquesQuery = Boutique::where('actif', true)->orderBy('nom');

        // Les employés voient uniquement leur boutique
        // Les propriétaires voient toutes leurs boutiques
        if ($user->isEmploye()) {
            $boutiques = $boutiquesQuery->where('id', $user->boutique_id)->get();
        } elseif ($user->isOwner()) {
            $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                $ownedBoutiques = collect([$user->boutique]);
            }
            $boutiques = $ownedBoutiques;
        } elseif ($user->isSuperAdmin()) {
            // Seul le super admin voit toutes les boutiques
            $boutiques = $boutiquesQuery->get();
        } else {
            // Pour les autres utilisateurs (non propriétaires, non super admin), pas de boutiques
            $boutiques = collect();
        }

        // Récupérer les catégories en désactivant temporairement le scope global
        // pour pouvoir les grouper par boutique, puis filtrer par la boutique du produit
        $categoriesParBoutique = Category::withoutGlobalScopes()
            ->active()
            ->orderBy('nom')
            ->get()
            ->groupBy('boutique_id')
            ->map(function($categories) {
                return $categories->map(fn($categorie) => [
                    'id' => $categorie->id,
                    'nom' => $categorie->nom,
                ]);
            })
            ->mapWithKeys(function($categories, $key) {
                return [(string) ($key ?? '') => $categories];
            });

        $categories = $categoriesParBoutique->get((string) $produit->boutique_id, collect());

        return view('produits.edit', compact(
            'produit',
            'boutiques',
            'categories',
            'categoriesParBoutique',
            'user'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer le produit sans le scope global pour vérifier son existence
        $produit = Produit::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès au produit selon les permissions
        // Les employés ne peuvent accéder qu'à leur boutique
        if ($user->isEmploye() && $produit->boutique_id != $user->boutique_id) {
            abort(404, 'Produit introuvable.');
        }
        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isOwner() && !$user->ownsBoutique($produit->boutique_id)) {
            abort(404, 'Produit introuvable.');
        }
        // Pour les autres utilisateurs (admins), vérifier que le produit appartient à la boutique active
        if (!$user->isEmploye() && !$user->isOwner() && $boutiqueId && $produit->boutique_id != $boutiqueId) {
            abort(404, 'Produit introuvable.');
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'quantite_stock' => 'nullable|integer|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
            'boutique_id' => 'required|exists:boutiques,id',
            'barcode' => 'nullable|string|max:50|unique:produits,barcode,' . $produit->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.max' => 'Le nom du produit ne doit pas dépasser 255 caractères.',
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.max' => 'La catégorie ne doit pas dépasser 255 caractères.',
            'prix_achat.numeric' => 'Le prix d\'achat doit être un nombre.',
            'prix_achat.min' => 'Le prix d\'achat doit être supérieur ou égal à 0.',
            'prix_vente.required' => 'Le prix de vente est obligatoire.',
            'prix_vente.numeric' => 'Le prix de vente doit être un nombre.',
            'prix_vente.min' => 'Le prix de vente doit être supérieur ou égal à 0.',
            'quantite_stock.integer' => 'La quantité en stock doit être un nombre entier.',
            'quantite_stock.min' => 'La quantité en stock doit être supérieure ou égale à 0.',
            'stock_minimum.integer' => 'Le stock minimum doit être un nombre entier.',
            'stock_minimum.min' => 'Le stock minimum doit être supérieur ou égal à 0.',
            'boutique_id.required' => 'La boutique est obligatoire.',
            'boutique_id.exists' => 'La boutique sélectionnée n\'existe pas.',
            'barcode.max' => 'Le code-barres ne doit pas dépasser 50 caractères.',
            'barcode.unique' => 'Ce code-barres est déjà utilisé par un autre produit.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.'
        ]);

        $data = $request->all();

        // Si la quantité en stock n'est pas renseignée, la mettre à 0
        if (!isset($data['quantite_stock']) || $data['quantite_stock'] === '' || $data['quantite_stock'] === null) {
            $data['quantite_stock'] = 0;
        }

        // Si le stock minimum n'est pas renseigné, le mettre à 0
        if (!isset($data['stock_minimum']) || $data['stock_minimum'] === '' || $data['stock_minimum'] === null) {
            $data['stock_minimum'] = 0;
        }

        // Gérer l'upload d'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($produit->image && file_exists(public_path($produit->image))) {
                unlink(public_path($produit->image));
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/produits'), $imageName);
            $data['image'] = 'images/produits/' . $imageName;
        }

        $produit->update($data);

        return redirect()->route('produits.index')
            ->with('success', 'Produit modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer le produit sans le scope global pour vérifier son existence
        $produit = Produit::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès au produit selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($produit->boutique_id != $user->boutique_id) {
                abort(404, 'Produit introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($produit->boutique_id != $boutiqueId) {
                abort(404, 'Produit introuvable.');
            }
        }

        // Vérifier si le produit a des ventes
        // L'administrateur/propriétaire de la boutique peut supprimer même avec des ventes associées
        $canDeleteWithSales = $user->ownsBoutique($produit->boutique_id);
        
        if ($produit->venteDetails()->exists() && !$canDeleteWithSales) {
            return redirect()->route('produits.index')
                ->with('error', 'Impossible de supprimer ce produit car il a des ventes associées.');
        }

        // Supprimer l'image si elle existe
        if ($produit->image && file_exists(public_path($produit->image))) {
            unlink(public_path($produit->image));
        }

        $produit->delete();

        return redirect()->route('produits.index')
            ->with('success', 'Produit supprimé avec succès !');
    }

    /**
     * Ajuster le stock d'un produit
     */
    public function ajusterStock(Request $request, $id)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Récupérer le produit sans le scope global pour vérifier son existence
        $produit = Produit::withoutGlobalScopes()->findOrFail($id);

        // Vérifier l'accès au produit selon les permissions
        if ($user->isEmploye() || $user->isOwner()) {
            if ($produit->boutique_id != $user->boutique_id) {
                abort(404, 'Produit introuvable.');
            }
        } elseif ($boutiqueId) {
            if ($produit->boutique_id != $boutiqueId) {
                abort(404, 'Produit introuvable.');
            }
        }

        $request->validate([
            'type' => 'required|in:entree,sortie',
            'quantite' => 'required|integer|min:1',
            'motif' => 'required|string|max:255'
        ]);

        $quantite = $request->type === 'entree' ? $request->quantite : -$request->quantite;

        // Mettre à jour le stock
        $produit->increment('quantite_stock', $quantite);

        // Créer un mouvement de stock
        $mouvement = $produit->mouvementsStock()->create([
            'type' => $request->type,
            'quantite' => $request->quantite,
            'motif' => $request->motif,
            'user_id' => auth()->id(),
            'boutique_id' => $produit->boutique_id
        ]);

        // Déclencher la notification
        NotificationService::notifierMouvementStock($mouvement, $produit, auth()->user());

        return redirect()->route('produits.show', $produit)
            ->with('success', 'Stock ajusté avec succès !');
    }

    /**
     * Générer un code-barres unique
     * Format : EAN-13 (13 chiffres) pour compatibilité avec les scanners standards
     *
     * @return string
     */
    private function genererCodeBarresUnique(): string
    {
        $maxTentatives = 100; // Limite de sécurité pour éviter les boucles infinies
        $tentative = 0;

        do {
            // Générer un code-barres EAN-13 (13 chiffres)
            // Format : 8XXXXXXXXXXX (8 = code pays fictif pour produits internes)
            $codeBarres = '8' . str_pad((string) mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);

            // Vérifier l'unicité
            $existe = Produit::where('barcode', $codeBarres)->exists();
            $tentative++;

            if ($tentative >= $maxTentatives) {
                // En cas d'échec après plusieurs tentatives, utiliser un format avec timestamp
                $codeBarres = '8' . str_pad((string) ((time() % 100000000000) + mt_rand(0, 999)), 11, '0', STR_PAD_LEFT);
                // Vérifier une dernière fois
                if (Produit::where('barcode', $codeBarres)->exists()) {
                    // Dernier recours : format avec microtime
                    $codeBarres = '8' . str_pad((string) ((int)(microtime(true) * 1000) % 100000000000), 11, '0', STR_PAD_LEFT);
                }
                break;
            }
        } while ($existe);

        return $codeBarres;
    }
}
