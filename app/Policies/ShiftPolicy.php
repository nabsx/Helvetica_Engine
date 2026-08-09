<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function view(User $user, Shift $shift): bool
    {
        return $user->can('view-any-shift') || $shift->user_id === $user->id || $shift->opened_by === $user->id;
    }

    public function close(User $user, Shift $shift): bool
    {
        return $shift->user_id === $user->id && $shift->status === 'open' && $user->can('close-shift');
    }

    public function approve(User $user, Shift $shift): bool
    {
        return $shift->status === 'pending_close' && $user->can('approve-shift');
    }
}
