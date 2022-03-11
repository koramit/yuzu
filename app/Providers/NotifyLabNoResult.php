<?php

namespace App\Providers;

use App\Managers\NotificationManager;
use App\Providers\LabNoResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyLabNoResult
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
     * @param  \App\Providers\LabNoResult  $event
     * @return void
     */
    public function handle(LabNoResult $event)
    {
        $text = "ไม่มีแลป\n";
        foreach ($event->visits as $visit) {
            $firstName = explode(' ', $visit->patient_name)[1] ?? '';
            $text .= (substr($visit->hn, 0, 3) . "**** | ");
            $text .= (substr($firstName, 0, 2) . "****\n\n");
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: $text, sticker: 'warning');
    }
}
