<?php

namespace App\Listeners;

use App\Events\VisitUpdated;
use App\Managers\SiITManager;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        if ($event->visit->status === 'discharged' && $event->visit->atk_positive_case && $event->visit->patient_id) {
            try {
                (new SiITManager)->manage($event->visit);
            } catch (Exception $e) {
                $message = $e->getMessage();
                if (!str_starts_with($message, 'URL error 28: Connection timed out after')) {
                    Log::error('export error on discharge'.'@'.$e->getMessage());
                }
            }
        }
    }
}
