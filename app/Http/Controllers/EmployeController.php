<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Boutique;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Afficher les employés et les admins (pas les super admins)
        // Exclure explicitement le super admin unique
        $query = User::with('boutique')
            ->whereIn('role', ['employe', 'admin'])
            ->where('email', '!=', User::SUPER_ADMIN_EMAIL);

        // Si c'est un propriétaire de boutique (pas un super admin), filtrer par sa boutique
        if ($user->isOwner() && !$user->isSuperAdmin()) {
            $query->where('boutique_id', $user->boutique_id);
        }

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
            // Si c'est un propriétaire, vérifier qu'il peut voir les employés de ses boutiques
            if ($user->isOwner() && !$user->isSuperAdmin() && !$user->ownsBoutique($request->boutique_id)) {
                abort(403, 'Vous ne pouvez voir que les employés de vos boutiques.');
            }
            $query->where('boutique_id', $request->boutique_id);
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        // Filtre par rôle si fourni
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $employes = $query->orderBy('role')->orderBy('name')->paginate(20);

        // Statistiques (filtrées par boutique si propriétaire)
        // Exclure explicitement le super admin unique
        $boutiqueId = session('boutique_active');
        $statsQuery = User::whereIn('role', ['employe', 'admin'])
            ->where('email', '!=', User::SUPER_ADMIN_EMAIL);
        if ($user->isOwner() && !$user->isSuperAdmin() && $boutiqueId) {
            $statsQuery->where('boutique_id', $boutiqueId);
        }

        $stats = [
            'total_employes' => (clone $statsQuery)->count(),
            'employes_actifs' => (clone $statsQuery)->where('actif', true)->count(),
            'employes_inactifs' => (clone $statsQuery)->where('actif', false)->count(),
        ];

        // Boutiques pour le filtre
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)->get();
            }
        } else {
            $boutiques = collect();
        }

        return view('employes.index', compact('employes', 'stats', 'boutiques'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Récupérer les boutiques selon le rôle
        // Seul le super admin voit toutes les boutiques
        // Les propriétaires voient uniquement leurs boutiques assignées
        if ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();
        } elseif ($user->isOwner()) {
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)->get();
            }
        } else {
            $boutiques = collect();
        }

        $permissions = Permission::active()
            ->where('module', '!=', 'sidebar')
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        // Permissions de sidebar séparées
        $sidebarPermissions = Permission::active()
            ->where('module', 'sidebar')
            ->orderBy('action')
            ->get();

        // Compter le nombre d'employés actuels pour les propriétaires de boutique
        // Exclure le propriétaire de la boutique du comptage
        $nombreEmployesActuels = 0;
        $boutiqueId = session('boutique_active') ?? ($user->isOwner() ? $user->boutique_id : null);
        if ($user->isOwner() && $boutiqueId) {
            $boutique = Boutique::find($boutiqueId);
            $ownerId = $boutique ? $boutique->owner_id : null;

            $nombreEmployesActuels = User::where('boutique_id', $boutiqueId)
                ->where('role', 'employe')
                ->where('email', '!=', User::SUPER_ADMIN_EMAIL) // Exclure le super admin
                ->when($ownerId, function($query) use ($ownerId) {
                    return $query->where('id', '!=', $ownerId);
                })
                ->count();
        }

        return view('employes.create', compact('boutiques', 'permissions', 'sidebarPermissions', 'nombreEmployesActuels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Déterminer le rôle selon l'utilisateur connecté
        $user = auth()->user();
        $defaultRole = 'employe';

        // Si c'est un propriétaire de boutique, il ne peut créer que des employés (pas des admins)
        if ($user->isOwner()) {
            $defaultRole = 'employe';
            // Forcer la boutique à la boutique active de la session
            $boutiqueId = session('boutique_active') ?? $user->boutique_id;
            if ($boutiqueId && $user->ownsBoutique($boutiqueId)) {
                $request->merge(['boutique_id' => $boutiqueId]);
            } else {
                $request->merge(['boutique_id' => $user->boutique_id]);
            }
        } elseif ($user->isSuperAdmin() && $request->has('role')) {
            // Si c'est le super admin unique, il peut choisir le rôle
            $selectedRole = $request->role;
            if ($selectedRole === 'admin' || $selectedRole === 'employe') {
                $defaultRole = $selectedRole;
            }
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ];

        // Boutique obligatoire seulement pour les employés
        if ($defaultRole === 'employe') {
            $rules['boutique_id'] = 'required|exists:boutiques,id';
        } else {
            $rules['boutique_id'] = 'nullable|exists:boutiques,id';
        }

        // Validation du rôle si c'est le super admin unique
        // Les propriétaires de boutique ne peuvent créer que des employés
        if ($user->isSuperAdmin()) {
            $rules['role'] = 'required|in:admin,employe';
        } else {
            // Forcer le rôle à 'employe' pour les propriétaires
            $defaultRole = 'employe';
        }

        $request->validate($rules);

        // Vérifier que le propriétaire ne peut créer que pour ses boutiques
        if ($user->isOwner() && !$user->ownsBoutique($request->boutique_id)) {
            return back()->withErrors(['boutique_id' => 'Vous ne pouvez créer des employés que pour vos boutiques.'])->withInput();
        }

        // Empêcher les propriétaires de créer des employés avec le rôle 'admin'
        if ($user->isOwner() && $defaultRole === 'admin') {
            return back()->withErrors(['role' => 'Un administrateur de boutique ne peut créer que des employés, pas des administrateurs.'])->withInput();
        }

        // Vérifier la limite de 2 employés maximum par boutique (uniquement pour les propriétaires de boutique)
        // Exclure le propriétaire de la boutique du comptage
        if ($user->isOwner() && $defaultRole === 'employe' && $request->boutique_id) {
            $boutique = Boutique::find($request->boutique_id);
            $ownerId = $boutique ? $boutique->owner_id : null;

            $nombreEmployes = User::where('boutique_id', $request->boutique_id)
                ->where('role', 'employe')
                ->where('email', '!=', User::SUPER_ADMIN_EMAIL) // Exclure le super admin
                ->when($ownerId, function($query) use ($ownerId) {
                    return $query->where('id', '!=', $ownerId);
                })
                ->count();

            if ($nombreEmployes >= 2) {
                $nomBoutique = $boutique ? $boutique->nom : 'cette boutique';
                return back()->withErrors(['boutique_id' => "Vous avez atteint la limite de 2 employés pour {$nomBoutique}. Un administrateur de boutique ne peut créer au maximum que 2 employés."])->withInput();
            }
        }

        $boutiqueId = $defaultRole === 'admin' ? null : $request->boutique_id;

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => $defaultRole,
            'boutique_id' => $boutiqueId,
            'actif' => true,
        ];

        $newUser = User::create($userData);

        // Attribuer les permissions seulement pour les employés (les admins ont toutes les permissions automatiquement)
        if ($defaultRole === 'employe' && $request->has('permissions')) {
            $newUser->permissions()->sync($request->permissions);
        }

        $roleLabel = $defaultRole === 'admin' ? 'administrateur' : 'employé';
        return redirect()->route('employes.index')
            ->with('success', ucfirst($roleLabel) . ' créé avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $employe)
    {
        $user = auth()->user();

        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        // Si c'est un propriétaire, vérifier que l'employé appartient à sa boutique
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($employe->boutique_id)) {
            abort(403, 'Vous ne pouvez voir que les employés de votre boutique.');
        }

        // Statistiques de l'employé
        $stats = [
            'total_ventes' => $employe->ventes()->count(),
            'chiffre_affaires' => $employe->ventes()->sum('total_final'),
            'moyenne_vente' => $employe->ventes()->avg('total_final'),
            'derniere_vente' => $employe->ventes()->latest()->first(),
        ];

        // Ventes récentes
        $ventesRecentes = $employe->ventes()
            ->with('boutique')
            ->latest()
            ->take(10)
            ->get();

        // Ventes par mois (6 derniers mois)
        $ventesParMois = $employe->ventes()
            ->select(DB::raw('MONTH(created_at) as mois'), DB::raw('YEAR(created_at) as annee'), DB::raw('COUNT(*) as nombre_ventes'), DB::raw('SUM(total_final) as chiffre_affaires'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mois', 'annee')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        return view('employes.show', compact('employe', 'stats', 'ventesRecentes', 'ventesParMois'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $employe)
    {
        $user = auth()->user();

        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        // Si c'est un propriétaire, vérifier que l'employé appartient à sa boutique
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($employe->boutique_id)) {
            abort(403, 'Vous ne pouvez modifier que les employés de votre boutique.');
        }

        // Si c'est un propriétaire, il ne peut modifier que pour sa boutique
        if ($user->isOwner() && !$user->isAdmin()) {
            $boutiques = Boutique::where('id', $user->boutique_id)->get();
        } elseif ($user->isSuperAdmin()) {
            $boutiques = Boutique::where('actif', true)->orderBy('nom')->get();
        } else {
            $boutiques = collect();
        }

        $permissions = Permission::active()
            ->where('module', '!=', 'sidebar')
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        // Permissions de sidebar séparées
        $sidebarPermissions = Permission::active()
            ->where('module', 'sidebar')
            ->orderBy('action')
            ->get();

        $userPermissions = $employe->permissions->pluck('id')->toArray();

        return view('employes.edit', compact('employe', 'boutiques', 'permissions', 'sidebarPermissions', 'userPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employe)
    {
        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        $user = auth()->user();

        // Si c'est un propriétaire, vérifier que l'employé appartient à sa boutique
        if ($user->isOwner() && !$user->ownsBoutique($employe->boutique_id)) {
            abort(403, 'Vous ne pouvez modifier que les employés de votre boutique.');
        }

        $currentRole = $employe->role;
        $newRole = $currentRole;

        // Si c'est un propriétaire, il ne peut créer que des employés (pas changer le rôle en admin)
        if ($user->isOwner()) {
            $newRole = 'employe';
            // Forcer la boutique à celle du propriétaire
            $request->merge(['boutique_id' => $user->boutique_id]);
        } elseif ($user->isSuperAdmin() && $request->has('role')) {
            // Déterminer le nouveau rôle si l'utilisateur connecté est le super admin unique
            $selectedRole = $request->role;
            // Le super admin peut changer le rôle entre admin et employé
            if ($selectedRole === 'admin' || $selectedRole === 'employe') {
                $newRole = $selectedRole;
            }
        }

        // Empêcher les propriétaires de modifier un employé en admin
        if ($user->isOwner() && $newRole === 'admin') {
            return back()->withErrors(['role' => 'Un administrateur de boutique ne peut créer que des employés, pas des administrateurs.'])->withInput();
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employe->id,
            'telephone' => 'required|string|max:20',
            'actif' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ];

        // Boutique obligatoire seulement pour les employés
        if ($newRole === 'employe') {
            $rules['boutique_id'] = 'required|exists:boutiques,id';
        } else {
            $rules['boutique_id'] = 'nullable|exists:boutiques,id';
        }

        // Validation du rôle si c'est le super admin unique
        if ($user->isSuperAdmin()) {
            $rules['role'] = 'required|in:admin,employe';
        }

        $request->validate($rules);

        // Vérifier que le propriétaire ne peut modifier que pour sa boutique
        if ($user->isOwner() && !$user->ownsBoutique($request->boutique_id)) {
            return back()->withErrors(['boutique_id' => 'Vous ne pouvez modifier que les employés de votre boutique.'])->withInput();
        }

        // Vérifier la limite de 2 employés maximum par boutique (uniquement pour les propriétaires de boutique)
        // Si on change la boutique ou si on passe d'admin à employé, vérifier la limite
        // Exclure le propriétaire de la boutique du comptage
        if ($user->isOwner() && $newRole === 'employe' && $request->boutique_id) {
            $boutique = Boutique::find($request->boutique_id);
            $ownerId = $boutique ? $boutique->owner_id : null;

            $nombreEmployes = User::where('boutique_id', $request->boutique_id)
                ->where('role', 'employe')
                ->where('email', '!=', User::SUPER_ADMIN_EMAIL) // Exclure le super admin
                ->where('id', '!=', $employe->id) // Exclure l'employé actuel du comptage
                ->when($ownerId, function($query) use ($ownerId) {
                    return $query->where('id', '!=', $ownerId);
                })
                ->count();

            // Si l'employé actuel n'est pas déjà dans cette boutique ou si ce n'est pas déjà un employé
            if ($employe->boutique_id != $request->boutique_id || $employe->role !== 'employe') {
                if ($nombreEmployes >= 2) {
                    $nomBoutique = $boutique ? $boutique->nom : 'cette boutique';
                    return back()->withErrors(['boutique_id' => "Vous avez atteint la limite de 2 employés pour {$nomBoutique}. Un administrateur de boutique ne peut créer au maximum que 2 employés."])->withInput();
                }
            }
        }

        $boutiqueId = $newRole === 'admin' ? null : $request->boutique_id;

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'boutique_id' => $boutiqueId,
            'actif' => $request->has('actif'),
        ];

        // Mettre à jour le rôle si changé
        if ($newRole !== $currentRole) {
            $updateData['role'] = $newRole;
        }

        $employe->update($updateData);

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $employe->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Mettre à jour les permissions seulement pour les employés (les admins ont toutes les permissions automatiquement)
        if ($newRole === 'employe') {
            if ($request->has('permissions')) {
                $employe->permissions()->sync($request->permissions);
            } else {
                $employe->permissions()->detach();
            }
        } else {
            // Si c'est un admin, supprimer toutes les permissions (il a déjà accès complet)
            $employe->permissions()->detach();
        }

        $roleLabel = $newRole === 'admin' ? 'administrateur' : 'employé';
        return redirect()->route('employes.index')
            ->with('success', ucfirst($roleLabel) . ' modifié avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employe)
    {
        $user = auth()->user();

        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        // Si c'est un propriétaire, vérifier que l'employé appartient à sa boutique
        if ($user->isOwner() && !$user->isAdmin() && !$user->ownsBoutique($employe->boutique_id)) {
            abort(403, 'Vous ne pouvez supprimer que les employés de votre boutique.');
        }

        // Vérifier s'il a des ventes
        if ($employe->ventes()->count() > 0) {
            $roleLabel = $employe->role === 'admin' ? 'cet administrateur' : 'cet employé';
            return redirect()->route('employes.index')
                ->with('error', "Impossible de supprimer {$roleLabel} car il a des ventes associées. Désactivez-le plutôt.");
        }

        $roleLabel = $employe->role === 'admin' ? 'Administrateur' : 'Employé';
        $employe->delete();

        return redirect()->route('employes.index')
            ->with('success', "{$roleLabel} supprimé avec succès !");
    }

    /**
     * Activer/Désactiver un employé
     */
    public function toggleStatus(User $employe)
    {
        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        $employe->update([
            'actif' => !$employe->actif
        ]);

        $status = $employe->actif ? 'activé' : 'désactivé';
        $roleLabel = $employe->role === 'admin' ? 'Administrateur' : 'Employé';

        return redirect()->route('employes.index')
            ->with('success', "{$roleLabel} {$status} avec succès !");
    }

    /**
     * Historique des ventes d'un employé
     */
    public function ventes(User $employe, Request $request)
    {
        // Vérifier que c'est bien un employé ou un admin (pas super admin)
        if ($employe->role === 'super_admin') {
            abort(404, 'Utilisateur non trouvé.');
        }

        $query = $employe->ventes()->with(['boutique', 'venteDetails.produit']);

        // Filtres
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('montant_min')) {
            $query->where('total_final', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $query->where('total_final', '<=', $request->montant_max);
        }

        $ventes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('employes.ventes', compact('employe', 'ventes'));
    }

    /**
     * Gérer les permissions d'un employé
     */
    public function permissions(User $employe)
    {
        // Les permissions ne sont applicables qu'aux employés
        if ($employe->role !== 'employe') {
            return redirect()->route('employes.index')
                ->with('error', 'Les permissions ne sont applicables qu\'aux employés. Les administrateurs ont automatiquement toutes les permissions.');
        }

        $permissions = Permission::active()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        $userPermissions = $employe->permissions->pluck('id')->toArray();

        return view('employes.permissions', compact('employe', 'permissions', 'userPermissions'));
    }

    /**
     * Mettre à jour les permissions d'un employé
     */
    public function updatePermissions(Request $request, User $employe)
    {
        // Les permissions ne sont applicables qu'aux employés
        if ($employe->role !== 'employe') {
            return redirect()->route('employes.index')
                ->with('error', 'Les permissions ne sont applicables qu\'aux employés. Les administrateurs ont automatiquement toutes les permissions.');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Synchroniser les permissions
        $employe->permissions()->sync($request->permissions ?? []);

        return redirect()->route('employes.permissions', $employe)
            ->with('success', 'Permissions mises à jour avec succès !');
    }
}
