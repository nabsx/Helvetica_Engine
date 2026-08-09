<?php

namespace App\Policies;

use App\Models\CashMovement;
use App\Models\Shift;
use App\Models\User;

class CashMovementPolicy
{
    public function create(User $user, Shift $shift): bool
    {
        return $shift->user_id === $user->id && $shift->status === 'open' && $user->can('manage-cash-movements');
    }

    public function view(User $user, CashMovement $movement): bool
    {
        return $user->can('view-any-shift') || $movement->user_id === $user->id;
    }
}
