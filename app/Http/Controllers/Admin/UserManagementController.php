<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMerchantRequest;
use App\Http\Requests\Admin\UpdateMerchantRequest;
use App\Models\Boutique;
use App\Models\ParametresBoutique;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $merchantRoles = ['employe']; // Les commerçants utilisent le rôle 'employe'

        $usersQuery = User::query()
            ->with(['boutique', 'abonnementActif'])
            ->whereIn('role', $merchantRoles);

        if ($request->filled('search')) {
            $search = $request->search;
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $usersQuery->where('actif', $request->statut === 'actif');
        }

        $users = $usersQuery->latest()->paginate(20);

        $stats = [
            'total' => User::whereIn('role', $merchantRoles)->count(),
            'actifs' => User::whereIn('role', $merchantRoles)->where('actif', true)->count(),
            'suspendus' => User::whereIn('role', $merchantRoles)->where('actif', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreMerchantRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::createWithRole([
                'name' => $data['name'],
                'email' => $data['email'],
                'telephone' => $data['telephone'],
                'password' => Hash::make($data['password']),
                'actif' => true,
            ], User::ROLE_ADMIN, ['tenant_key' => Str::uuid()]);

            $boutique = Boutique::create([
                'nom' => $data['boutique_nom'],
                'adresse' => $data['boutique_adresse'] ?? null,
                'telephone' => $data['telephone'],
                'email' => $data['email'],
                'devise' => $data['devise'] ?? 'FCFA',
                'owner_id' => $user->id,
            ]);

            $user->update(['boutique_id' => $boutique->id]);

            ParametresBoutique::updateOrCreate(
                ['user_id' => $user->id, 'boutique_id' => $boutique->id],
                [
                    'nom_affichage' => $data['boutique_nom'],
                    'devise' => $boutique->devise,
                    'timezone' => config('app.timezone'),
                ]
            );

            $this->subscriptionService->renew($user, [
                'type_abonnement' => $data['type_abonnement'],
                'date_debut' => $data['date_debut'] ?? now(),
                'date_expiration' => $data['date_expiration'] ?? null,
                'montant' => $data['montant'] ?? 0,
            ]);

            return $user;
        });

        // Notifier le Super Admin du nouveau client
        NotificationService::notifierNouveauClient($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Compte client et boutique créés avec succès !');
    }

    public function show(User $user)
    {
        $this->ensureMerchant($user);

        $user->load(['boutique', 'abonnements' => fn ($query) => $query->latest('date_debut')->limit(6)]);

        $stats = [
            'ventes_total' => $user->ventes()->sum('total_final'),
            'depenses_total' => $user->depenses()->sum('montant'),
            'produits_actifs' => $user->produits()->count(),
            'stock_total' => $user->produits()->sum('quantite_stock'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        $this->ensureMerchant($user);

        $user->load(['boutique', 'parametresBoutique']);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateMerchantRequest $request, User $user)
    {
        $this->ensureMerchant($user);
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $user) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'telephone' => $data['telephone'],
                'actif' => $request->boolean('actif', $user->actif),
            ]);

            if (!empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            if ($user->boutique) {
                $user->boutique->update([
                    'nom' => $data['boutique_nom'],
                    'adresse' => $data['boutique_adresse'],
                    'devise' => $data['devise'] ?? $user->boutique->devise,
                ]);
            }

            ParametresBoutique::updateOrCreate(
                ['user_id' => $user->id, 'boutique_id' => $user->boutique_id],
                [
                    'nom_affichage' => $data['boutique_nom'],
                    'devise' => $data['devise'] ?? ($user->boutique->devise ?? 'FCFA'),
                ]
            );
        });

        return redirect()->route('admin.dashboard')->with('success', 'Profil client mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        $this->ensureMerchant($user);

        // Vérifier s'il y a des données associées
        $hasVentes = $user->ventes()->count() > 0;
        $hasProduits = $user->produits()->count() > 0;
        $hasDepenses = $user->depenses()->count() > 0;

        if ($hasVentes || $hasProduits || $hasDepenses) {
            return back()->with('error', 
                'Impossible de supprimer cet utilisateur car il possède des données associées (ventes, produits, dépenses). ' .
                'Veuillez d\'abord supprimer ou transférer ces données.');
        }

        DB::transaction(function () use ($user) {
            // Supprimer les abonnements
            $user->abonnements()->delete();

            // Supprimer les paramètres boutique
            ParametresBoutique::where('user_id', $user->id)->delete();

            // Supprimer la boutique associée si elle existe
            if ($user->boutique) {
                $user->boutique->delete();
            }

            // Supprimer l'utilisateur
            $user->delete();
        });

        // Notifier le Super Admin
        NotificationService::notifierAlerteSysteme(
            'Utilisateur supprimé',
            "L'utilisateur {$user->name} a été supprimé du système.",
            'normale'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function suspend(User $user)
    {
        $this->ensureMerchant($user);

        $ancienStatut = $user->actif;
        $user->update(['actif' => !$user->actif]);

        // Notifier le Super Admin
        NotificationService::notifierCompteSuspendu($user, $user->actif ? 'reactive' : 'suspendu');

        return back()->with('success', $user->actif ? 'Compte réactivé avec succès.' : 'Compte suspendu avec succès.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureMerchant($user);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    protected function ensureMerchant(User $user): void
    {
        if (!$user->isEmploye()) {
            abort(403, 'Cette action est réservée aux comptes commerçants.');
        }
    }
}

