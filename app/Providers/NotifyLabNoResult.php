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
            $text .= ("\n" . $visit->hn);
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: $text, sticker: 'warning');
    }
}
