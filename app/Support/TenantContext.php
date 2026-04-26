<?php

namespace App\Support;

use App\Models\User;

class TenantContext
{
    protected ?int $userId = null;
    protected ?int $boutiqueId = null;
    protected bool $shouldScope = true;

    /**
     * Définir le contexte à partir d'un utilisateur
     */
    public function setFromUser(?User $user): void
    {
        if ($user) {
            $this->userId = $user->id;

            // Si l'utilisateur n'est pas admin, définir la boutique par défaut
            if (!$user->isAdmin() && $user->boutique_id) {
                $this->boutiqueId = $user->boutique_id;
            }
        } else {
            $this->userId = null;
            $this->boutiqueId = null;
        }
    }

    /**
     * Définir l'ID de la boutique
     */
    public function setBoutiqueId(?int $boutiqueId): void
    {
        $this->boutiqueId = $boutiqueId;
    }

    /**
     * Obtenir l'ID de l'utilisateur
     */
    public function userId(): ?int
    {
        return $this->userId;
    }

    /**
     * Obtenir l'ID de la boutique
     */
    public function boutiqueId(): ?int
    {
        return $this->boutiqueId;
    }

    /**
     * Déterminer si le scope doit être appliqué
     */
    public function shouldScope(): bool
    {
        return $this->shouldScope;
    }

    /**
     * Activer ou désactiver le scope
     */
    public function setShouldScope(bool $shouldScope): void
    {
        $this->shouldScope = $shouldScope;
    }

    /**
     * Réinitialiser le contexte
     */
    public function reset(): void
    {
        $this->userId = null;
        $this->boutiqueId = null;
        $this->shouldScope = true;
    }
}


