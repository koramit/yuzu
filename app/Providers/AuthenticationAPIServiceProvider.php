<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuthenticationAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\AuthenticationAPI', config('app.authentication_api_provider'));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
