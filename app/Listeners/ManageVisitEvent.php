<?php

namespace App\Listeners;

use App\Events\VisitUpdated;
use App\Managers\SiITManager;
use App\Models\Visit;
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
        if ($event->visit->status === 'exam') {
            Cache::put('exam-list-new', time());
        }

        if ($event->visit->status !== 'screen') {
            Cache::put('mr-list-new', time());
        } elseif ($event->visit->status === 'screen') {
            Cache::put('screen-list-new', time());
        }

        if ($event->visit->status === 'discharged' && config('app.env') === 'production') {
            (new SiITManager)->manage($event->visit);
        }
    }
}
