<?php

namespace App\Listeners;

use App\Events\VisitUpdated;
use Illuminate\Support\Facades\Cache;

class ManageVisitEvent
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
     * @param  VisitUpdated  $event
     * @return void
     */
    public function handle(VisitUpdated $event)
    {
        if (collect(['screen', 'discharged', 'swab', 'canceled'])->contains($event->visit->status)) {
            Cache::put('mr-list-new', time());
        } else {
            Cache::put('exam-list-new', time());
        }
    }
}
