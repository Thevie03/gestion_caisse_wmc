<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $user = auth()->user();
        // Les employés voient uniquement les catégories de leur boutique
        // Les propriétaires voient les catégories de la boutique active (session)
        $boutiqueId = $user->isEmploye()
            ? $user->boutique_id
            : session('boutique_active');

        // Seul le super admin peut sélectionner une autre boutique
        if ($user->isSuperAdmin()) {
            if ($request->has('boutique_id')) {
                $boutiqueId = $request->input('boutique_id') ?: null;
            }
        }

        $categoriesQuery = Category::with(['boutique'])
            ->withCount('produits')
            ->orderBy('nom');

        if ($boutiqueId) {
            $categoriesQuery->where('boutique_id', $boutiqueId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $categoriesQuery->where(function($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $categoriesQuery->paginate(20);

        $statsQuery = Category::query();

        if ($boutiqueId) {
            $statsQuery->where('boutique_id', $boutiqueId);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'actives' => (clone $statsQuery)->active()->count(),
            'avec_produits' => (clone $statsQuery)->whereHas('produits')->count(),
        ];

        // Récupérer les boutiques selon le rôle
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->orderBy('nom')->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->orderBy('nom')
                    ->get();
            }
        } else {
            $boutiques = collect();
        }

        return view('categories.index', compact('categories', 'stats', 'boutiques', 'boutiqueId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        $icones = [
            'fas fa-tag' => 'Tag',
            'fas fa-palette' => 'Palette',
            'fas fa-tshirt' => 'T-shirt',
            'fas fa-gem' => 'Gemme',
            'fas fa-spa' => 'Spa',
            'fas fa-paint-brush' => 'Pinceau',
            'fas fa-wind' => 'Vent',
            'fas fa-shoe-prints' => 'Chaussures',
            'fas fa-ring' => 'Bague',
            'fas fa-laptop' => 'Laptop',
            'fas fa-home' => 'Maison',
            'fas fa-dumbbell' => 'Haltère',
            'fas fa-book' => 'Livre',
            'fas fa-utensils' => 'Couverts',
            'fas fa-heart' => 'Cœur',
            'fas fa-baby' => 'Bébé',
            'fas fa-box' => 'Boîte',
            'fas fa-star' => 'Étoile',
            'fas fa-crown' => 'Couronne',
            'fas fa-gift' => 'Cadeau',
        ];

        $couleurs = [
            'primary' => 'Bleu',
            'success' => 'Vert',
            'info' => 'Cyan',
            'warning' => 'Orange',
            'danger' => 'Rouge',
            'secondary' => 'Gris',
            'dark' => 'Noir',
            'light' => 'Blanc',
        ];

        // Les employés voient uniquement leur boutique
        // Les propriétaires voient toutes leurs boutiques
        if ($user->isEmploye()) {
            $boutiques = Boutique::where('id', $user->boutique_id)
                ->where('actif', true)
                ->orderBy('nom')
                ->get();
            $boutiqueId = $user->boutique_id;
        } elseif ($user->isOwner()) {
            // Pour les propriétaires, récupérer toutes leurs boutiques
            $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                $ownedBoutiques = collect([$user->boutique]);
            }
            $boutiques = $ownedBoutiques;
            // Utiliser la boutique active de la session, ou fallback sur la première boutique
            $boutiqueId = session('boutique_active');
            if (!$boutiqueId && $ownedBoutiques->isNotEmpty()) {
                $boutiqueId = $ownedBoutiques->first()->id;
            } elseif (!$boutiqueId && $user->boutique_id) {
                $boutiqueId = $user->boutique_id;
            }
        } else {
            // Pour les autres utilisateurs (non propriétaires), pas de boutiques
            $boutiques = collect();
            $boutiqueId = session('boutique_active');
        }

        return view('categories.create', compact('icones', 'couleurs', 'boutiques', 'boutiqueId', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Les employés utilisent toujours leur boutique
        // Les propriétaires utilisent la boutique active de la session
        $boutiqueId = $user->isEmploye()
            ? $user->boutique_id
            : $request->input('boutique_id', session('boutique_active'));

        if (!$boutiqueId) {
            return back()
                ->withInput()
                ->withErrors(['boutique_id' => 'Veuillez sélectionner une boutique pour cette catégorie.']);
        }

        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(fn($query) => $query->where('boutique_id', $boutiqueId)),
            ],
            'icone' => 'nullable|string',
            'couleur' => 'nullable|string',
            'description' => 'nullable|string',
            'boutique_id' => [
                'nullable',
                'exists:boutiques,id',
            ],
        ]);

        Category::create([
            'nom' => $request->nom,
            'icone' => $request->input('icone') ?: 'fas fa-tag',
            'couleur' => $request->input('couleur') ?: 'secondary',
            'description' => $request->description,
            'boutique_id' => $boutiqueId,
        ]);

        // Mettre à jour la session boutique_active pour synchroniser avec la boutique de la catégorie créée
        // Cela garantit que les catégories créées seront visibles lors de la création de produits
        // Seulement si l'utilisateur est propriétaire et que la boutique créée est différente de celle en session
        if ($boutiqueId && $user->isOwner() && session('boutique_active') != $boutiqueId) {
            session(['boutique_active' => $boutiqueId]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie créée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('produits.boutique');

        $this->authorizeBoutique($category);

        $stats = [
            'total_produits' => $category->produits->count(),
            'stock_total' => $category->produits->sum('quantite_stock'),
            'valeur_stock' => $category->produits->sum(function($produit) {
                return $produit->quantite_stock * $produit->prix_achat;
            }),
        ];

        return view('categories.show', compact('category', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $this->authorizeBoutique($category);

        $icones = [
            'fas fa-tag' => 'Tag',
            'fas fa-palette' => 'Palette',
            'fas fa-tshirt' => 'T-shirt',
            'fas fa-gem' => 'Gemme',
            'fas fa-spa' => 'Spa',
            'fas fa-paint-brush' => 'Pinceau',
            'fas fa-wind' => 'Vent',
            'fas fa-shoe-prints' => 'Chaussures',
            'fas fa-ring' => 'Bague',
            'fas fa-laptop' => 'Laptop',
            'fas fa-home' => 'Maison',
            'fas fa-dumbbell' => 'Haltère',
            'fas fa-book' => 'Livre',
            'fas fa-utensils' => 'Couverts',
            'fas fa-heart' => 'Cœur',
            'fas fa-baby' => 'Bébé',
            'fas fa-box' => 'Boîte',
            'fas fa-star' => 'Étoile',
            'fas fa-crown' => 'Couronne',
            'fas fa-gift' => 'Cadeau',
        ];

        $couleurs = [
            'primary' => 'Bleu',
            'success' => 'Vert',
            'info' => 'Cyan',
            'warning' => 'Orange',
            'danger' => 'Rouge',
            'secondary' => 'Gris',
            'dark' => 'Noir',
            'light' => 'Blanc',
        ];

        $user = auth()->user();

        // Récupérer les boutiques selon le rôle
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->orderBy('nom')->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->orderBy('nom')
                    ->get();
            }
        } else {
            $boutiques = collect();
        }

        return view('categories.edit', compact('category', 'icones', 'couleurs', 'boutiques', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeBoutique($category);

        $user = $request->user();

        // Les employés utilisent toujours leur boutique
        // Les propriétaires utilisent la boutique active de la session
        $boutiqueId = $user->isEmploye()
            ? $user->boutique_id
            : $request->input('boutique_id', $category->boutique_id);

        if (!$boutiqueId) {
            return back()
                ->withInput()
                ->withErrors(['boutique_id' => 'Veuillez sélectionner une boutique pour cette catégorie.']);
        }

        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where(fn($query) => $query->where('boutique_id', $boutiqueId))
                    ->ignore($category->id),
            ],
            'icone' => 'nullable|string',
            'couleur' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'boutique_id' => [
                'nullable',
                'exists:boutiques,id',
            ],
        ]);

        $category->update([
            'nom' => $request->nom,
            'icone' => $request->input('icone') ?: 'fas fa-tag',
            'couleur' => $request->input('couleur') ?: 'secondary',
            'description' => $request->description,
            'active' => $request->boolean('active'),
            'boutique_id' => $boutiqueId,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Récupérer la catégorie sans le scope global pour éviter les problèmes de 404
        $category = Category::withoutGlobalScopes()->findOrFail($id);

        $this->authorizeBoutique($category);

        // L'administrateur/propriétaire de la boutique peut supprimer même avec des produits associés
        $user = auth()->user();
        $canDeleteWithProducts = $user->ownsBoutique($category->boutique_id);
        
        if ($category->produits()->count() > 0 && !$canDeleteWithProducts) {
            return back()->with('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès !');
    }

    /**
     * Toggle active status
     */
    public function toggle(Category $category)
    {
        $this->authorizeBoutique($category);

        $category->update(['active' => !$category->active]);

        $status = $category->active ? 'activée' : 'désactivée';

        return back()->with('success', "Catégorie {$status} avec succès !");
    }

    /**
     * Vérifie que l'utilisateur a accès à la boutique liée.
     */
    protected function authorizeBoutique(Category $category): void
    {
        $user = auth()->user();

        // Les employés ne peuvent accéder qu'à leur boutique
        if ($user->isEmploye() && $user->boutique_id !== $category->boutique_id) {
            abort(403);
        }

        // Les propriétaires peuvent accéder à toutes leurs boutiques
        if ($user->isOwner() && !$user->ownsBoutique($category->boutique_id)) {
            abort(403);
        }

        // Pour les autres, vérifier la boutique active
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $category->boutique_id !== session('boutique_active')) {
            abort(403);
        }
    }
}
