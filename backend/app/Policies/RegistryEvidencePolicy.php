<?php

namespace App\Policies;

use App\Models\RegistryEvidence;
use App\Models\User;

class RegistryEvidencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RegistryEvidence $evidence): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isReviewer();
    }

    public function update(User $user, RegistryEvidence $evidence): bool
    {
        return $user->isAdmin() || $user->isReviewer();
    }

    public function delete(User $user, RegistryEvidence $evidence): bool
    {
        return $user->isAdmin();
    }
}
