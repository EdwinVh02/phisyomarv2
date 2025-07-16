<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gates para roles
        Gate::define('admin-only', function ($user) {
            return $user->rol_id === 1;
        });

        Gate::define('admin-or-receptionist', function ($user) {
            return in_array($user->rol_id, [1, 3]);
        });

        Gate::define('admin-receptionist-therapist', function ($user) {
            return in_array($user->rol_id, [1, 2, 3]);
        });

        Gate::define('manage-patients', function ($user) {
            return in_array($user->rol_id, [1, 3]); // Admin y Recepcionista
        });
    }
}
