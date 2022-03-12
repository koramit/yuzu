<?php

namespace App\Listeners;

use App\Managers\NotificationManager;
use App\Events\LabNoResult;
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
     * @param  \App\Events\LabNoResult  $event
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
