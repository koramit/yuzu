<?php

namespace App\Listeners;

use App\Events\Registered;
use Illuminate\Support\Facades\Session;

class InitRole
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $profile = Session::get('profile');

        Session::forget('profile');
    }
}
