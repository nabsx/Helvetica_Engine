<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $admin): ?bool
    {
        return $admin->isAdmin() ? null : false;
    }

    public function viewAny(User $admin): bool
    {
        return $admin->isAdmin();
    }

    public function update(User $admin, User $user): bool
    {
        return $user->role !== 'admin' && $user->id !== $admin->id;
    }

    public function delete(User $admin, User $user): bool
    {
        return $user->role !== 'admin' && $user->id !== $admin->id;
    }

    public function resetPin(User $admin, User $user): bool
    {
        return $user->role !== 'admin' && $user->id !== $admin->id;
    }
}
