<?php

namespace App\Policies;

use App\Models\Route;
use App\Models\User;

class RoutePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Route $route): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Route $route): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Route $route): bool
    {
        return $user->isAdmin();
    }
}
