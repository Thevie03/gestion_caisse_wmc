<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boutique;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BoutiqueController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentTheme = 'default';

        // Si c'est un propriétaire de boutique, montrer toutes ses boutiques
        if ($user->isOwner()) {
            // Récupérer toutes les boutiques du propriétaire (via relation many-to-many)
            $boutiques = $user->ownedBoutiques()->where('actif', true)->get();

            // Si aucune boutique via many-to-many, utiliser l'ancien système (rétrocompatibilité)
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)->where('actif', true)->get();
            }

            // Utiliser le thème de la boutique active ou de la première boutique
            $selectedBoutiqueId = session('boutique_active');
            if ($selectedBoutiqueId) {
                $selectedBoutique = $boutiques->firstWhere('id', $selectedBoutiqueId);
                if ($selectedBoutique) {
                    $currentTheme = $selectedBoutique->theme_color ?? $selectedBoutique->theme ?? 'default';
                }
            } elseif ($boutiques->isNotEmpty()) {
                $currentTheme = $boutiques->first()->theme_color ?? $boutiques->first()->theme ?? 'default';
            }
        } elseif ($user->isSuperAdmin()) {
            // Pour le super admin uniquement, montrer toutes les boutiques actives (avec cache)
            $boutiques = \App\Services\CacheService::getActiveBoutiques();
            // Si une boutique est déjà sélectionnée en session, utiliser son thème
            $selectedBoutiqueId = session('boutique_active');
            if ($selectedBoutiqueId) {
                $selectedBoutique = \App\Services\CacheService::getBoutique($selectedBoutiqueId);
                if ($selectedBoutique) {
                    $currentTheme = $selectedBoutique->theme_color ?? 'default';
                }
            }
        } else {
            // Pour les autres utilisateurs (employés), ne montrer que leur boutique assignée
            if ($user->boutique_id) {
                $boutiques = Boutique::where('id', $user->boutique_id)->where('actif', true)->get();
                if ($user->boutique) {
                    $currentTheme = $user->boutique->theme;
                }
            } else {
                $boutiques = collect();
            }
        }

        return view('boutiques.index', compact('boutiques', 'currentTheme'));
    }

    public function select($id)
    {
        $user = auth()->user();
        $boutique = \App\Services\CacheService::getBoutique($id);

        if (!$boutique) {
            abort(404, 'Boutique introuvable');
        }

        // Vérifier que l'utilisateur a accès à cette boutique
        if ($user->isOwner()) {
            // Vérifier que c'est une de ses boutiques
            if (!$user->ownsBoutique($boutique->id)) {
                abort(403, 'Vous n\'avez pas accès à cette boutique.');
            }
        } elseif (!$user->isSuperAdmin()) {
            // Pour les employés, vérifier que c'est leur boutique assignée
            if ($user->boutique_id != $boutique->id) {
                abort(403, 'Vous n\'avez pas accès à cette boutique.');
            }
        }

        session(['boutique_active' => $boutique->id]);

        return redirect()->route('dashboard')->with('success', 'Boutique sélectionnée avec succès');
    }

    /**
     * Afficher le formulaire d'édition d'une boutique
     */
    public function edit(Boutique $boutique)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur peut éditer cette boutique
        // Un propriétaire ne peut éditer que ses propres boutiques
        if ($user->isOwner() && !$user->isSuperAdmin() && !$user->ownsBoutique($boutique->id)) {
            abort(403, 'Vous ne pouvez modifier que vos propres boutiques.');
        }

        // Un admin peut éditer toutes les boutiques
        // Un propriétaire peut éditer sa boutique

        return view('boutiques.edit', compact('boutique'));
    }

    /**
     * Mettre à jour une boutique
     */
    public function update(Request $request, Boutique $boutique)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur peut modifier cette boutique
        if ($user->isOwner() && !$user->isSuperAdmin() && !$user->ownsBoutique($boutique->id)) {
            abort(403, 'Vous ne pouvez modifier que vos propres boutiques.');
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'devise' => 'required|string|in:FCFA,EUR,USD,XOF',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'shared_hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'pos_banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'pos_stock_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'pos_payment_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'theme_color' => 'nullable|string|in:default,blue,green,purple,orange,abaya',
            'theme_style' => 'nullable|string|in:modern,minimal,classic,compact,elegant',
            'mail_mailer' => 'nullable|string|in:smtp,sendmail,mailgun,ses,postmark,log,array',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'nom', 'description', 'adresse', 'telephone', 'email', 'devise',
            'theme_color', 'theme_style',
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
        ]);

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

        // Les propriétaires ne peuvent pas modifier certains champs sensibles
        if ($user->isOwner() && !$user->isAdmin()) {
            // Ne pas permettre la modification de owner_id et actif
            unset($data['owner_id'], $data['actif']);
        }

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($boutique->logo && file_exists(public_path('images/logos/' . $boutique->logo))) {
                unlink(public_path('images/logos/' . $boutique->logo));
            }

            $logo = $request->file('logo');
            $logoName = Str::slug($boutique->nom) . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images/logos'), $logoName);
            $data['logo'] = $logoName;
        }

        if ($request->hasFile('shared_hero_image')) {
            $deletePosImageIfExists($boutique->pos_banner_image);
            $deletePosImageIfExists($boutique->pos_stock_image);
            $deletePosImageIfExists($boutique->pos_payment_image);

            $image = $request->file('shared_hero_image');
            $name = Str::slug($boutique->nom) . '_hero_shared_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/pos'), $name);

            $data['pos_banner_image'] = $name;
            $data['pos_stock_image'] = $name;
            $data['pos_payment_image'] = $name;
        }

        if (!$request->hasFile('shared_hero_image') && $request->hasFile('pos_banner_image')) {
            $deletePosImageIfExists($boutique->pos_banner_image);
            $image = $request->file('pos_banner_image');
            $name = Str::slug($boutique->nom) . '_pos_banner_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/pos'), $name);
            $data['pos_banner_image'] = $name;
        }

        if (!$request->hasFile('shared_hero_image') && $request->hasFile('pos_stock_image')) {
            $deletePosImageIfExists($boutique->pos_stock_image);
            $image = $request->file('pos_stock_image');
            $name = Str::slug($boutique->nom) . '_pos_stock_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/pos'), $name);
            $data['pos_stock_image'] = $name;
        }

        if (!$request->hasFile('shared_hero_image') && $request->hasFile('pos_payment_image')) {
            $deletePosImageIfExists($boutique->pos_payment_image);
            $image = $request->file('pos_payment_image');
            $name = Str::slug($boutique->nom) . '_pos_payment_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/pos'), $name);
            $data['pos_payment_image'] = $name;
        }

        // Sauvegarder les données
        $boutique->update($data);
        
        // Rafraîchir la boutique depuis la base de données pour avoir les dernières valeurs
        $boutique->refresh();

        // Invalider TOUS les caches liés à cette boutique IMMÉDIATEMENT
        \App\Services\CacheService::forgetBoutique($boutique->id);
        
        // Invalider le cache spécifique du middleware (très important!)
        \Illuminate\Support\Facades\Cache::forget("boutique_theme_{$boutique->id}");
        
        // Eviter les invalidations globales coûteuses.
        // On invalide uniquement la boutique concernée.
        
        // Si c'est la boutique active, forcer le rechargement
        if (session('boutique_active') == $boutique->id) {
            // Forcer le rechargement en vidant le cache de session
            session()->forget('boutique_theme_' . $boutique->id);
        }

        // Rediriger vers le dashboard après mise à jour
        $dashboardRoute = $user->isSuperAdmin() ? 'admin.dashboard' : 'dashboard';

        return redirect()->route($dashboardRoute)
            ->with('success', 'Thème de la boutique mis à jour avec succès !')
            ->with('theme_updated', true)
            ->with('theme_color', $boutique->theme_color)
            ->with('theme_style', $boutique->theme_style);
    }
}
