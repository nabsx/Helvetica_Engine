<?php

namespace App\Providers;

use App\Models\CashMovement;
use App\Models\Shift;
use App\Models\User;
use App\Policies\CashMovementPolicy;
use App\Policies\ShiftPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(CashMovement::class, CashMovementPolicy::class);

        Gate::define('view-cash-monitoring', fn (User $user): bool => in_array($user->role, ['admin', 'owner', 'manager'], true));
        Gate::define('view-any-shift', fn (User $user): bool => in_array($user->role, ['admin', 'owner', 'manager'], true));
        Gate::define('manage-cash-movements', fn (User $user): bool => in_array($user->role, ['admin', 'owner', 'manager', 'cashier'], true));
        Gate::define('close-shift', fn (User $user): bool => in_array($user->role, ['admin', 'owner', 'manager', 'cashier'], true));
        Gate::define('approve-shift', fn (User $user): bool => in_array($user->role, ['admin', 'owner', 'manager'], true));
    }
}
