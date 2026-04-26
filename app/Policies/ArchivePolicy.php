<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArchivePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        // Super admin voit tout, propriétaires voient leurs archives
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Archive $archive): bool
    {
        // Super admin voit tout, propriétaires voient leurs propres archives
        if ($user->isAdmin()) {
            return true;
        }

        return $archive->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Super admin et propriétaires de boutique peuvent créer des archives
        return $user->isAdmin() || $user->isOwner();
    }

    public function download(User $user, Archive $archive): bool
    {
        // Super admin peut tout télécharger, propriétaires leurs propres archives
        if ($user->isAdmin()) {
            return true;
        }

        return $archive->user_id === $user->id;
    }

    public function delete(User $user, Archive $archive): bool
    {
        // Super admin peut tout supprimer, propriétaires leurs propres archives
        if ($user->isAdmin()) {
            return true;
        }

        return $archive->user_id === $user->id;
    }

    public function import(User $user): bool
    {
        // Super admin et propriétaires peuvent importer
        return $user->isAdmin() || $user->isOwner();
    }
}














