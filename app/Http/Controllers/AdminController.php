<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Boutique;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('boutique')->where('role', 'admin');

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('boutique_id')) {
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $admins = $query->orderBy('name')->paginate(20);

        // Statistiques
        $stats = [
            'total_admins' => User::where('role', 'admin')->count(),
            'admins_actifs' => User::where('role', 'admin')->where('actif', true)->count(),
            'admins_inactifs' => User::where('role', 'admin')->where('actif', false)->count(),
        ];

        // Boutiques pour le filtre
        $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();

        return view('admins.index', compact('admins', 'stats', 'boutiques'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();

        return view('admins.create', compact('boutiques'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'boutique_id' => 'nullable|exists:boutiques,id',
            'actif' => 'boolean',
        ]);

        $boutiqueId = $request->filled('boutique_id') ? $request->boutique_id : null;
        $actif = $request->has('actif') ? $request->boolean('actif') : true;

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Les admins ont automatiquement toutes les permissions via isAdmin()
            'boutique_id' => $boutiqueId,
            'actif' => $actif,
        ]);

        // Note: Les admins ont automatiquement toutes les permissions
        // car isAdmin() retourne true pour role='admin', et hasPermission()/canAccess()
        // retournent true si isAdmin() est true. Aucune permission spécifique n'est nécessaire.

        return redirect()->route('admins.index')
            ->with('success', 'Administrateur créé avec succès ! Il a maintenant accès à toutes les fonctionnalités du système.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $admin)
    {
        // Vérifier que c'est bien un admin
        if ($admin->role !== 'admin') {
            abort(404, 'Admin non trouvé.');
        }

        // Statistiques de l'admin
        $stats = [
            'total_ventes' => $admin->ventes()->count(),
            'chiffre_affaires' => $admin->ventes()->sum('total_final'),
            'moyenne_vente' => $admin->ventes()->avg('total_final'),
            'derniere_vente' => $admin->ventes()->latest()->first(),
        ];

        // Ventes récentes
        $ventesRecentes = $admin->ventes()
            ->with('boutique')
            ->latest()
            ->take(10)
            ->get();

        return view('admins.show', compact('admin', 'stats', 'ventesRecentes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $admin)
    {
        // Vérifier que c'est bien un admin
        if ($admin->role !== 'admin') {
            abort(404, 'Admin non trouvé.');
        }

        $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();

        return view('admins.edit', compact('admin', 'boutiques'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $admin)
    {
        // Vérifier que c'est bien un admin
        if ($admin->role !== 'admin') {
            abort(404, 'Admin non trouvé.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'telephone' => 'required|string|max:20',
            'boutique_id' => 'nullable|exists:boutiques,id',
            'actif' => 'boolean',
        ]);

        $boutiqueId = $request->filled('boutique_id') ? $request->boutique_id : null;
        $actif = $request->has('actif') ? $request->boolean('actif') : $admin->actif;

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'boutique_id' => $boutiqueId,
            'actif' => $actif,
        ]);

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $admin->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admins.index')
            ->with('success', 'Admin modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        // Vérifier que c'est bien un admin
        if ($admin->role !== 'admin') {
            abort(404, 'Admin non trouvé.');
        }

        // Vérifier s'il a des ventes
        if ($admin->ventes()->count() > 0) {
            return redirect()->route('admins.index')
                ->with('error', 'Impossible de supprimer cet admin car il a des ventes associées. Désactivez-le plutôt.');
        }

        $admin->delete();

        return redirect()->route('admins.index')
            ->with('success', 'Admin supprimé avec succès !');
    }

    /**
     * Activer/Désactiver un admin
     */
    public function toggleStatus(User $admin)
    {
        // Vérifier que c'est bien un admin
        if ($admin->role !== 'admin') {
            abort(404, 'Admin non trouvé.');
        }

        $admin->update([
            'actif' => !$admin->actif
        ]);

        $status = $admin->actif ? 'activé' : 'désactivé';

        return redirect()->route('admins.index')
            ->with('success', "Admin {$status} avec succès !");
    }
}

