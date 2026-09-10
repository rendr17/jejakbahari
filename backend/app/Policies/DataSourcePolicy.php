<?php

namespace App\Policies;

use App\Models\DataSource;
use App\Models\User;

class DataSourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DataSource $dataSource): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, DataSource $dataSource): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, DataSource $dataSource): bool
    {
        return $user->isAdmin();
    }
}
