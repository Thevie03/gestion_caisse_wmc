<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Produit;

class NotificationService
{
    /**
     * Créer une notification
     */
    public static function creer($titre, $message, $type = 'info', $priorite = 'normale', $user_id = null, $module = null, $data = null)
    {
        return Notification::create([
            'titre' => $titre,
            'message' => $message,
            'type' => $type,
            'priorite' => $priorite,
            'user_id' => $user_id,
            'module' => $module,
            'data' => $data,
        ]);
    }

    /**
     * Notifier tous les utilisateurs
     */
    public static function notifierTous($titre, $message, $type = 'info', $priorite = 'normale', $module = null, $data = null)
    {
        return self::creer($titre, $message, $type, $priorite, null, $module, $data);
    }

    /**
     * Notifier un utilisateur spécifique
     */
    public static function notifierUser($user_id, $titre, $message, $type = 'info', $priorite = 'normale', $module = null, $data = null)
    {
        return self::creer($titre, $message, $type, $priorite, $user_id, $module, $data);
    }

    /**
     * Notifier les administrateurs
     * Crée une notification globale pour tous les admins
     */
    public static function notifierAdmins($titre, $message, $type = 'info', $priorite = 'normale', $module = null, $data = null)
    {
        // Créer une notification globale (user_id = null) visible par tous les admins
        return self::creer($titre, $message, $type, $priorite, null, $module, $data);
    }

    /**
     * Notifier uniquement le Super Admin (WMC)
     * Crée une notification globale (user_id = null) visible par tous les super admins
     */
    public static function notifierSuperAdmin($titre, $message, $type = 'info', $priorite = 'normale', $module = null, $data = null)
    {
        // Créer une notification globale (user_id = null) pour la plateforme WMC
        // Tous les super admins verront cette notification unique
        return self::creer($titre, $message, $type, $priorite, null, $module, $data);
    }

    /**
     * Notification : Abonnement expiré ou proche de l'expiration
     */
    public static function notifierAbonnementExpire($abonnement, $joursRestants = null)
    {
        $user = $abonnement->user;
        $message = $joursRestants
            ? "L'abonnement de {$user->name} expire dans {$joursRestants} jour(s)."
            : "L'abonnement de {$user->name} a expiré.";

        $type = $joursRestants && $joursRestants > 0 ? 'warning' : 'error';
        $priorite = $joursRestants && $joursRestants <= 3 ? 'haute' : 'normale';

        self::notifierSuperAdmin(
            $joursRestants ? 'Abonnement expirant bientôt' : 'Abonnement expiré',
            $message,
            $type,
            $priorite,
            'abonnements',
            [
                'abonnement_id' => $abonnement->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'date_expiration' => $abonnement->date_expiration->format('Y-m-d'),
                'jours_restants' => $joursRestants,
            ]
        );
    }

    /**
     * Notification : Paiement reçu ou rejeté
     */
    public static function notifierPaiement($paiement, $statut = 'confirme')
    {
        $user = $paiement->user;
        $message = $statut === 'confirme'
            ? "Un paiement de " . number_format($paiement->montant, 0, ',', ' ') . " FCFA a été confirmé pour {$user->name}."
            : "Un paiement de " . number_format($paiement->montant, 0, ',', ' ') . " FCFA a été rejeté pour {$user->name}.";

        self::notifierSuperAdmin(
            $statut === 'confirme' ? 'Paiement confirmé' : 'Paiement rejeté',
            $message,
            $statut === 'confirme' ? 'success' : 'error',
            'normale',
            'paiements',
            [
                'paiement_id' => $paiement->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'montant' => $paiement->montant,
                'statut' => $statut,
            ]
        );
    }

    /**
     * Notification : Nouveau client inscrit
     */
    public static function notifierNouveauClient($user)
    {
        self::notifierSuperAdmin(
            'Nouveau client inscrit',
            "Un nouveau client s'est inscrit : {$user->name} ({$user->email})",
            'success',
            'normale',
            'users',
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'boutique_id' => $user->boutique_id,
            ]
        );
    }

    /**
     * Notification : Nouvelle boutique active
     */
    public static function notifierNouvelleBoutique($boutique)
    {
        self::notifierSuperAdmin(
            'Nouvelle boutique créée',
            "Une nouvelle boutique a été créée : {$boutique->nom}",
            'success',
            'normale',
            'boutiques',
            [
                'boutique_id' => $boutique->id,
                'boutique_nom' => $boutique->nom,
                'owner_id' => $boutique->owner_id,
            ]
        );
    }

    /**
     * Notification : Compte utilisateur suspendu ou inactif
     */
    public static function notifierCompteSuspendu($user, $action = 'suspendu')
    {
        $message = $action === 'suspendu'
            ? "Le compte de {$user->name} a été suspendu."
            : "Le compte de {$user->name} a été réactivé.";

        self::notifierSuperAdmin(
            $action === 'suspendu' ? 'Compte suspendu' : 'Compte réactivé',
            $message,
            $action === 'suspendu' ? 'warning' : 'success',
            'normale',
            'users',
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => $action,
            ]
        );
    }

    /**
     * Notification : Alerte système
     *
     * @param string $titre Titre de la notification
     * @param string $message Message de la notification
     * @param string $priorite Priorité : 'basse', 'normale', 'haute', 'critique' (défaut: 'normale')
     * @param array|null $data Données supplémentaires
     */
    public static function notifierAlerteSysteme($titre, $message, $priorite = 'normale', $data = null)
    {
        // Valider la priorité
        $prioritesValides = ['basse', 'normale', 'haute', 'critique'];
        if (!in_array($priorite, $prioritesValides)) {
            $priorite = 'normale';
        }

        self::notifierSuperAdmin(
            $titre,
            $message,
            'info',
            $priorite,
            'systeme',
            $data
        );
    }

    /**
     * Déterminer le niveau de stock et sa couleur associée
     */
    private static function getStockLevel($produit)
    {
        $stockActuel = $produit->quantite_stock;
        $stockMinimum = $produit->stock_minimum ?? 0;

        // Stock en rupture
        if ($stockActuel == 0) {
            return [
                'niveau' => 'rupture',
                'type' => 'error',
                'couleur' => 'danger',
                'icone' => '❌',
                'priorite' => 'critique'
            ];
        }

        // Stock bas (inférieur ou égal au minimum)
        if ($stockActuel <= $stockMinimum) {
            return [
                'niveau' => 'bas',
                'type' => 'warning',
                'couleur' => 'warning',
                'icone' => '⚠️',
                'priorite' => 'haute'
            ];
        }

        // Stock moyen (entre minimum et 2x minimum)
        if ($stockActuel <= ($stockMinimum * 2)) {
            return [
                'niveau' => 'moyen',
                'type' => 'warning',
                'couleur' => 'warning',
                'icone' => '🟡',
                'priorite' => 'normale'
            ];
        }

        // Stock haut (supérieur à 2x minimum)
        return [
            'niveau' => 'haut',
            'type' => 'success',
            'couleur' => 'success',
            'icone' => '✅',
            'priorite' => 'normale'
        ];
    }

    /**
     * Notifier un mouvement de stock
     */
    public static function notifierMouvementStock($mouvement, $produit, $user)
    {
        // Recharger le produit pour obtenir le stock à jour après le mouvement
        $produit->refresh();

        // Déterminer le niveau de stock et sa couleur
        $stockLevel = self::getStockLevel($produit);

        // Icône selon le type de mouvement
        $iconeMouvement = match($mouvement->type) {
            'entree' => '📦',
            'sortie' => '📤',
            'ajustement' => '⚖️',
            default => '📊'
        };

        $titre = "{$iconeMouvement} {$stockLevel['icone']} Mouvement de stock - {$produit->nom}";
        $message = "Mouvement de type '{$mouvement->type}' de {$mouvement->quantite} unités. Motif: {$mouvement->motif}. Stock actuel: {$produit->quantite_stock} (Niveau: " . ucfirst($stockLevel['niveau']) . ")";

        // Notifier l'utilisateur qui a effectué le mouvement (notification personnelle pour la boutique)
        self::notifierUser(
            $user->id,
            $titre,
            $message,
            $stockLevel['type'],
            $stockLevel['priorite'],
            'stock',
            [
                'mouvement_id' => $mouvement->id,
                'produit_id' => $produit->id,
                'type_mouvement' => $mouvement->type,
                'quantite' => $mouvement->quantite,
                'stock_actuel' => $produit->quantite_stock,
                'stock_minimum' => $produit->stock_minimum,
                'niveau_stock' => $stockLevel['niveau']
            ]
        );
    }

    /**
     * Vérifier les alertes de stock
     */
    public static function verifierAlertesStock()
    {
        // Vérifier tous les produits actifs
        $produits = Produit::where('actif', true)->get();

        foreach ($produits as $produit) {
            $stockLevel = self::getStockLevel($produit);

            // Ne créer des notifications que pour les stocks bas, moyens ou en rupture
            // Ignorer les stocks hauts
            if ($stockLevel['niveau'] === 'haut') {
                continue;
            }

            // Messages selon le niveau
            $message = match($stockLevel['niveau']) {
                'rupture' => "Le produit '{$produit->nom}' est en rupture de stock",
                'bas' => "Le produit '{$produit->nom}' a un stock bas ({$produit->quantite_stock} unités restantes, minimum: {$produit->stock_minimum})",
                'moyen' => "Le produit '{$produit->nom}' a un stock moyen ({$produit->quantite_stock} unités, minimum: {$produit->stock_minimum})",
                default => "Le produit '{$produit->nom}' nécessite une attention (Stock: {$produit->quantite_stock})"
            };

            $titre = "{$stockLevel['icone']} Stock {$stockLevel['niveau']} - {$produit->nom}";

            // Notifier le propriétaire de la boutique concernée (notification personnelle)
            $boutique = $produit->boutique;
            if ($boutique && $boutique->owner) {
                // Ne pas créer de doublons pour les mêmes produits
                $existeDeja = Notification::where('module', 'stock')
                    ->where('user_id', $boutique->owner->id)
                    ->where('data->produit_id', $produit->id)
                    ->where('data->niveau_stock', $stockLevel['niveau'])
                    ->where('lue', false)
                    ->exists();

                if (!$existeDeja) {
                    self::notifierUser(
                        $boutique->owner->id,
                        $titre,
                        $message,
                        $stockLevel['type'],
                        $stockLevel['priorite'],
                        'stock',
                        [
                            'produit_id' => $produit->id,
                            'quantite' => $produit->quantite_stock,
                            'stock_minimum' => $produit->stock_minimum,
                            'niveau_stock' => $stockLevel['niveau']
                        ]
                    );
                }
            }
        }
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public static function marquerToutesCommeLues($user_id = null)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');
        $query = Notification::query()->where('lue', false);

        // Si l'utilisateur est le Super Admin, marquer uniquement les notifications globales
        if ($user && $user->isSuperAdmin()) {
            $query->whereNull('user_id');
        } else {
            // Pour les boutiques/clients, marquer uniquement leurs notifications personnelles
            // Filtrées par la boutique active pour les propriétaires
            if ($user_id) {
                $query->where('user_id', $user_id);

                // Filtrer par boutique active pour les propriétaires
                // Les employés voient uniquement les notifications de leur boutique
                if ($user && $user->isEmploye()) {
                    $query->where('boutique_id', $user->boutique_id);
                } elseif ($boutiqueId) {
                    // Pour les propriétaires, filtrer par la boutique active
                    $query->where('boutique_id', $boutiqueId);
                }
            }
        }

        return $query->update([
            'lue' => true,
            'lue_at' => now()
        ]);
    }

    /**
     * Supprimer les anciennes notifications
     */
    public static function nettoyerAnciennes($jours = 30)
    {
        return Notification::where('created_at', '<', now()->subDays($jours))
            ->where('lue', true)
            ->delete();
    }

    /**
     * Obtenir les notifications pour un utilisateur
     */
    public static function getNotifications($user_id = null, $limite = 10)
    {
        $user = auth()->user();

        // Si l'utilisateur est le Super Admin, retourner uniquement les notifications globales (WMC)
        if ($user && $user->isSuperAdmin()) {
            return Notification::whereNull('user_id')
                ->orderBy('priorite', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        }

        // Pour les boutiques/clients, retourner UNIQUEMENT leurs notifications personnelles
        // Filtrées par la boutique active pour les propriétaires
        // (pas les notifications globales du Super Admin)
        if ($user_id) {
            $boutiqueId = session('boutique_active');
            $query = Notification::where('user_id', $user_id);

            // Filtrer par boutique active pour les propriétaires
            // Les employés voient uniquement les notifications de leur boutique
            if ($user && $user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($boutiqueId) {
                // Pour les propriétaires, filtrer par la boutique active
                $query->where('boutique_id', $boutiqueId);
            }

            return $query->orderBy('priorite', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        }

        return collect();
    }

    /**
     * Compter les notifications non lues
     */
    public static function compterNonLues($user_id = null)
    {
        $user = auth()->user();
        $boutiqueId = session('boutique_active');

        // Si l'utilisateur est le Super Admin, compter uniquement les notifications globales (WMC)
        if ($user && $user->isSuperAdmin()) {
            return Notification::whereNull('user_id')
                ->where('lue', false)
                ->count();
        }

        // Pour les boutiques/clients, compter UNIQUEMENT leurs notifications personnelles
        // Filtrées par la boutique active pour les propriétaires
        if ($user_id) {
            $query = Notification::where('user_id', $user_id)
                ->where('lue', false);

            // Filtrer par boutique active pour les propriétaires
            // Les employés voient uniquement les notifications de leur boutique
            if ($user && $user->isEmploye()) {
                $query->where('boutique_id', $user->boutique_id);
            } elseif ($boutiqueId) {
                // Pour les propriétaires, filtrer par la boutique active
                $query->where('boutique_id', $boutiqueId);
            }

            return $query->count();
        }

        return 0;
    }

    /**
     * Notification : Nouveau ticket créé par un propriétaire de boutique
     */
    public static function notifierNouveauTicket($ticket)
    {
        $user = $ticket->user;
        $boutique = $ticket->boutique;

        // Déterminer le type et la priorité selon la priorité du ticket
        $type = match($ticket->priorite) {
            'urgente' => 'error',
            'elevee' => 'warning',
            default => 'info'
        };

        $priorite = match($ticket->priorite) {
            'urgente' => 'critique',
            'elevee' => 'haute',
            default => 'normale'
        };

        $message = "Nouvelle demande d'assistance de {$boutique->nom} : \"{$ticket->sujet}\"";
        if ($ticket->priorite === 'urgente') {
            $message .= " [URGENT]";
        }

        self::notifierSuperAdmin(
            'Nouvelle demande d\'assistance',
            $message,
            $type,
            $priorite,
            'support',
            [
                'ticket_id' => $ticket->id,
                'boutique_id' => $boutique->id,
                'boutique_nom' => $boutique->nom,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'sujet' => $ticket->sujet,
                'priorite' => $ticket->priorite,
            ]
        );
    }

    /**
     * Notification : Réponse reçue sur un ticket (pour le propriétaire)
     */
    public static function notifierReponseTicket($ticket)
    {
        $user = $ticket->user; // Le propriétaire qui a créé le ticket

        if (!$user) {
            return;
        }

        $message = "Vous avez reçu une réponse de WMC concernant votre demande : \"{$ticket->sujet}\"";

        self::notifierUser(
            $user->id,
            'Réponse à votre demande d\'assistance',
            $message,
            'success',
            'normale',
            'support',
            [
                'ticket_id' => $ticket->id,
                'boutique_id' => $ticket->boutique_id,
                'sujet' => $ticket->sujet,
                'statut' => $ticket->statut,
            ]
        );
    }
}






