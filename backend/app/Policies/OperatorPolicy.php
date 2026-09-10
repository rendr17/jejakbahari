<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Operator $operator): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Operator $operator): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Operator $operator): bool
    {
        return $user->isAdmin();
    }
}
