<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use App\Models\ParametresBoutique;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BoutiqueManagementController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request)
    {
        $this->validateListingFilters($request);

        $query = Boutique::with('owner')->withSum('ventes as chiffre_affaires', 'total_final');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('actif')) {
            $query->where('actif', $request->actif === '1');
        }

        $boutiques = $query->latest()->paginate(20);

        $stats = [
            'total' => Boutique::count(),
            'actives' => Boutique::where('actif', true)->count(),
            'inactives' => Boutique::where('actif', false)->count(),
        ];

        return view('admin.boutiques.index', compact('boutiques', 'stats'));
    }

    public function show(Boutique $boutique)
    {
        $boutique->load(['owner', 'produits', 'ventes' => fn($q) => $q->latest()->limit(10)]);

        $stats = [
            'chiffre_affaires' => $boutique->ventes()->sum('total_final'),
            'produits_total' => $boutique->produits()->count(),
            'ventes_total' => $boutique->ventes()->count(),
            'depenses_total' => $boutique->depenses()->sum('montant'),
        ];

        // Compter les autres données pour la vue
        $dataCount = [
            'ventes' => $boutique->ventes()->count(),
            'produits' => $boutique->produits()->count(),
            'depenses' => $boutique->depenses()->count(),
            'utilisateurs' => $boutique->users()->count(),
            'categories' => $boutique->categories()->count(),
            'clients' => $boutique->clients()->count(),
            'fournisseurs' => $boutique->fournisseurs()->count(),
        ];

        return view('admin.boutiques.show', compact('boutique', 'stats', 'dataCount'));
    }

    public function create()
    {
        return view('admin.boutiques.create');
    }

    public function supervision($boutiqueId)
    {
        $boutique = Boutique::findOrFail($boutiqueId);

        // Mettre la boutique en session pour la supervision
        session(['boutique_active' => $boutique->id, 'admin_supervision' => true]);

        return redirect()->route('dashboard')
            ->with('info', 'Mode supervision activé pour la boutique: ' . $boutique->nom);
    }

    public function store(Request $request)
    {
        // Déterminer le type de propriétaire
        $ownerType = $request->input('owner_type', 'new');

        // Validation personnalisée pour l'email du propriétaire (si nouveau)
        $existingUser = null;
        if ($ownerType === 'new' && $request->filled('owner_email') && empty($request->owner_id)) {
            $existingUser = User::where('email', $request->owner_email)->first();
            if ($existingUser) {
                // Si l'utilisateur existe déjà, suggérer d'utiliser le mode "existant"
                return back()->withErrors([
                    'owner_email' => "Cet email existe déjà. Veuillez utiliser l'option 'Propriétaire existant' pour assigner la boutique à cet utilisateur."
                ])->withInput();
            }
        }

        $rules = [
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'devise' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'shared_hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'owner_type' => 'required|in:new,existing',
        ];

        // Validation conditionnelle selon le type de propriétaire
        if ($ownerType === 'existing') {
            $rules['owner_id'] = 'required|exists:users,id';
        } else {
            $rules['owner_name'] = 'required|string|max:255';
            $rules['owner_telephone'] = 'required|string|max:20';
            $rules['owner_email'] = 'required|email|max:255|unique:users,email';
            $rules['owner_password'] = 'nullable|string|min:8';
            $rules['type_abonnement'] = 'required|in:mensuel,trimestriel,semestriel,annuel,acquisition_definitive';
            $rules['montant_abonnement'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        $data = $request->only([
            'nom', 'adresse', 'telephone', 'email', 'devise', 'owner_type',
            'owner_id', 'owner_name', 'owner_telephone', 'owner_email', 'owner_password',
            'type_abonnement', 'montant_abonnement',
        ]);

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = Str::slug($request->nom) . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/logos'), $logoName);
            $data['logo'] = $logoName;
        }

        if ($request->hasFile('shared_hero_image')) {
            $heroImage = $request->file('shared_hero_image');
            $heroImageName = Str::slug($request->nom) . '_hero_shared_' . time() . '.' . $heroImage->getClientOriginalExtension();
            $heroImage->move(public_path('images/pos'), $heroImageName);
            $data['pos_banner_image'] = $heroImageName;
            $data['pos_stock_image'] = $heroImageName;
            $data['pos_payment_image'] = $heroImageName;
        }

        $creation = DB::transaction(function () use ($data, $ownerType) {
            if ($ownerType === 'existing' && !empty($data['owner_id'])) {
                // Utiliser un propriétaire existant
                $owner = User::findOrFail($data['owner_id']);
                // S'assurer que le propriétaire a le rôle admin
                if ($owner->role !== User::ROLE_ADMIN && $owner->role !== 'super_admin') {
                    $owner->assignRole(User::ROLE_ADMIN);
                }
                $generatedPassword = null;
            } else {
                // Créer un nouvel utilisateur
                $plainPassword = $data['owner_password'] ?? Str::random(10);

                $owner = User::createWithRole([
                    'name' => $data['owner_name'],
                    'email' => $data['owner_email'],
                    'telephone' => $data['owner_telephone'],
                    'password' => Hash::make($plainPassword),
                    'actif' => true,
                ], User::ROLE_ADMIN, ['tenant_key' => Str::uuid()]);

                $generatedPassword = $plainPassword;
            }

            $boutique = Boutique::create([
                'nom' => $data['nom'],
                'adresse' => $data['adresse'] ?? null,
                'telephone' => $data['telephone'],
                'email' => $data['email'] ?? null,
                'devise' => $data['devise'],
                'logo' => $data['logo'] ?? null,
                'owner_id' => $owner->id,
            ]);

            // Assigner la boutique au propriétaire via la table pivot (nouveau système multi-boutique)
            $isPrimary = !$owner->ownedBoutiques()->exists(); // Première boutique = principale
            $owner->ownedBoutiques()->attach($boutique->id, [
                'is_primary' => $isPrimary,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mettre à jour boutique_id pour rétrocompatibilité (si c'est la première boutique)
            if ($isPrimary) {
                $owner->update(['boutique_id' => $boutique->id]);
            }

            ParametresBoutique::updateOrCreate(
                ['user_id' => $owner->id, 'boutique_id' => $boutique->id],
                [
                    'nom_affichage' => $boutique->nom,
                    'devise' => $boutique->devise,
                    'timezone' => config('app.timezone'),
                ]
            );

            // Créer l'abonnement uniquement pour les nouveaux propriétaires
            // Les propriétaires existants conservent leur abonnement actuel
            if ($ownerType === 'new') {
                $this->subscriptionService->renew($owner, [
                    'type_abonnement' => $data['type_abonnement'],
                    'montant' => $data['montant_abonnement'],
                ]);
            }

            NotificationService::notifierNouvelleBoutique($boutique);
            NotificationService::notifierNouveauClient($owner);

            return [
                'boutique' => $boutique,
                'owner' => $owner,
                'generated_password' => $generatedPassword,
            ];
        });

        if ($ownerType === 'existing') {
            $message = 'Boutique créée et assignée au propriétaire existant avec succès.';
        } else {
            $message = 'Boutique et administrateur créés avec succès.';
            // Sécurité : Ne pas afficher le mot de passe en clair dans les messages
            // Le mot de passe doit être transmis de manière sécurisée (email, SMS, etc.)
            if (!empty($creation['generated_password'])) {
                // Log sécurisé du mot de passe (ne pas l'afficher dans le message)
                \Illuminate\Support\Facades\Log::info('Mot de passe généré pour boutique', [
                    'boutique_id' => $creation['boutique']->id,
                    'user_id' => $creation['owner']->id,
                    'email' => $creation['owner']->email,
                ]);
                // Message générique pour l'utilisateur
                $message .= ' Un mot de passe temporaire a été généré. Veuillez le transmettre de manière sécurisée.';
            }
        }

        return redirect()->route('admin.boutiques.index')
            ->with('success', $message);
    }

    public function edit(Boutique $boutique)
    {
        return view('admin.boutiques.edit', compact('boutique'));
    }

    public function update(Request $request, Boutique $boutique)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'devise' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'shared_hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'actif' => 'boolean',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email,' . $boutique->owner_id,
            'owner_telephone' => 'required|string|max:20',
            'owner_password' => 'nullable|string|min:8',
        ]);

        $data = $request->only([
            'nom', 'adresse', 'telephone', 'email', 'devise', 'actif',
            'owner_name', 'owner_email', 'owner_telephone', 'owner_password',
        ]);

        if ($request->hasFile('logo')) {
            // Sécurité : Valider le chemin du fichier avant suppression
            if ($boutique->logo) {
                $logoPath = public_path('images/logos/' . basename($boutique->logo));
                // Vérifier que le chemin est dans le répertoire autorisé (protection contre path traversal)
                $realPath = realpath($logoPath);
                $allowedPath = realpath(public_path('images/logos'));
                if ($realPath && $allowedPath && str_starts_with($realPath, $allowedPath) && file_exists($realPath)) {
                    @unlink($realPath);
                }
            }

            $logo = $request->file('logo');
            $logoName = Str::slug($boutique->nom) . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/logos'), $logoName);
            $data['logo'] = $logoName;
        }

        if ($request->hasFile('shared_hero_image')) {
            $deletePosImageIfExists = static function (?string $filename): void {
                if (!$filename) {
                    return;
                }

                $imagePath = public_path('images/pos/' . basename($filename));
                $realPath = realpath($imagePath);
                $allowedPath = realpath(public_path('images/pos'));
                if ($realPath && $allowedPath && str_starts_with($realPath, $allowedPath) && file_exists($realPath)) {
                    @unlink($realPath);
                }
            };

            $deletePosImageIfExists($boutique->pos_banner_image);
            $deletePosImageIfExists($boutique->pos_stock_image);
            $deletePosImageIfExists($boutique->pos_payment_image);

            $heroImage = $request->file('shared_hero_image');
            $heroImageName = Str::slug($boutique->nom) . '_hero_shared_' . time() . '.' . $heroImage->getClientOriginalExtension();
            $heroImage->move(public_path('images/pos'), $heroImageName);
            $data['pos_banner_image'] = $heroImageName;
            $data['pos_stock_image'] = $heroImageName;
            $data['pos_payment_image'] = $heroImageName;
        }

        $boutique->update($data);

        // Notifier si c'est une nouvelle boutique active
        if ($boutique->actif && $boutique->wasRecentlyCreated) {
            NotificationService::notifierNouvelleBoutique($boutique);
        }

        if ($boutique->owner) {
            $boutique->owner->update([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'telephone' => $request->owner_telephone,
            ]);
            $boutique->owner->assignRole(User::ROLE_ADMIN);

            if ($request->filled('owner_password')) {
                $boutique->owner->update([
                    'password' => Hash::make($request->owner_password),
                ]);
            }
        }

        return redirect()->route('admin.boutiques.show', $boutique)
            ->with('success', 'Boutique mise à jour avec succès.');
    }

    public function toggleActif(Boutique $boutique)
    {
        $boutique->update(['actif' => !$boutique->actif]);

        return back()->with('success',
            $boutique->actif ? 'Boutique activée.' : 'Boutique désactivée.');
    }

    public function destroy(Request $request, Boutique $boutique)
    {
        $user = auth()->user();

        // Seul le super admin unique peut supprimer une boutique
        if (!$user->isSuperAdmin()) {
            abort(403, 'Seul le super administrateur peut supprimer une boutique.');
        }

        $force = $request->boolean('force', false);

        // Vérifier s'il y a des données associées
        $hasVentes = $boutique->ventes()->count() > 0;
        $hasProduits = $boutique->produits()->count() > 0;
        $hasDepenses = $boutique->depenses()->count() > 0;
        $hasUsers = $boutique->users()->count() > 0;
        $hasCategories = $boutique->categories()->count() > 0;
        $hasClients = $boutique->clients()->count() > 0;
        $hasFournisseurs = $boutique->fournisseurs()->count() > 0;

        $hasData = $hasVentes || $hasProduits || $hasDepenses || $hasUsers ||
                   $hasCategories || $hasClients || $hasFournisseurs;

        // Si des données existent et que la suppression forcée n'est pas demandée
        if ($hasData && !$force) {
            $dataCount = [
                'ventes' => $boutique->ventes()->count(),
                'produits' => $boutique->produits()->count(),
                'depenses' => $boutique->depenses()->count(),
                'utilisateurs' => $boutique->users()->count(),
                'categories' => $boutique->categories()->count(),
                'clients' => $boutique->clients()->count(),
                'fournisseurs' => $boutique->fournisseurs()->count(),
            ];

            return back()->with('error',
                'Cette boutique contient des données associées. ' .
                'Si vous souhaitez supprimer la boutique et toutes ses données, ' .
                'veuillez utiliser l\'option de suppression forcée.')
                ->with('boutique_data', $dataCount)
                ->with('boutique_id', $boutique->id);
        }

        // Sauvegarder le nom de la boutique avant suppression (pour la notification)
        $boutiqueNom = $boutique->nom;
        $boutiqueId = $boutique->id;

        // Suppression avec ou sans données
        DB::transaction(function () use ($boutique, $force, $boutiqueId) {
            if ($force) {
                // Suppression forcée : supprimer toutes les données associées
                // Supprimer les ventes et leurs détails
                $venteIds = $boutique->ventes()->pluck('id');
                if ($venteIds->isNotEmpty()) {
                    \App\Models\VenteDetail::whereIn('vente_id', $venteIds)->delete();
                    \App\Models\PaiementVente::whereIn('vente_id', $venteIds)->delete();
                    \App\Models\Facture::whereIn('vente_id', $venteIds)->delete();
                    $boutique->ventes()->delete();
                }

                // Supprimer les produits et mouvements de stock
                $produitIds = $boutique->produits()->pluck('id');
                if ($produitIds->isNotEmpty()) {
                    \App\Models\MouvementStock::whereIn('produit_id', $produitIds)->delete();
                    $boutique->produits()->delete();
                }

                // Supprimer les autres données
                $boutique->depenses()->delete();
                $boutique->categories()->delete();
                $boutique->clients()->delete();
                $boutique->fournisseurs()->delete();

                // Désassocier les utilisateurs (ne pas les supprimer, juste retirer la boutique)
                $boutique->users()->update(['boutique_id' => null]);
            }

            // Supprimer le propriétaire de la boutique (administrateur de la boutique)
            if ($boutique->owner_id) {
                $owner = User::find($boutique->owner_id);
                if ($owner) {
                    // Supprimer l'utilisateur propriétaire et tous ses abonnements
                    $owner->abonnements()->delete();
                    $owner->delete();
                }
            }

            // Supprimer les paramètres boutique
            \App\Models\ParametresBoutique::where('boutique_id', $boutiqueId)->delete();

            // Supprimer les contrats associés
            \App\Models\Contrat::where('boutique_id', $boutiqueId)->delete();

            // Supprimer le logo si il existe (avec validation de sécurité)
            if ($boutique->logo) {
                $logoPath = public_path('images/logos/' . basename($boutique->logo));
                // Vérifier que le chemin est dans le répertoire autorisé (protection contre path traversal)
                $realPath = realpath($logoPath);
                $allowedPath = realpath(public_path('images/logos'));
                if ($realPath && $allowedPath && str_starts_with($realPath, $allowedPath) && file_exists($realPath)) {
                    @unlink($realPath);
                }
            }

            // Invalider le cache
            \App\Services\CacheService::forgetBoutique($boutiqueId);

            // Supprimer la boutique
            $boutique->delete();
        });

        // Notifier le Super Admin (utiliser la variable sauvegardée)
        NotificationService::notifierAlerteSysteme(
            'Boutique supprimée',
            "La boutique {$boutiqueNom} a été supprimée du système" . ($force ? ' avec toutes ses données' : '') . ".",
            'normale'
        );

        return redirect()->route('admin.boutiques.index')
            ->with('success', 'Boutique supprimée avec succès' . ($force ? ' (avec toutes ses données)' : '') . '.');
    }
}
