<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Visit' => 'App\Policies\VisitPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->abilities->contains($ability) || $user->role_names->contains('root')) {
                return true;
            }
        });

        Gate::define('refer', fn ($user) => $user->can('evaluate') && $user->mocktail_token);

        Gate::define('line_notify', fn ($user) => $user->line_active);
    }
}
