<?php

namespace Modules\RBAC\App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RBACServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // SUPERADMIN BYPASS (DB-driven)
        Gate::before(function ($user, $ability) {
            // opsi A: role name "superadmin"
            if ($user?->hasRole('superadmin')) {
                return true;
            }
            return null;
        });
    }
}
