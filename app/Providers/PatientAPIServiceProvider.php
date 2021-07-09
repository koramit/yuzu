<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PatientAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\PatientAPI', config('app.patient_api_provider'));
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
