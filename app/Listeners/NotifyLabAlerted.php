<?php

namespace App\Listeners;

use App\Events\LabAlerted;
use App\Managers\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyLabAlerted
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
     * @param  \App\Events\LabAlerted  $event
     * @return void
     */
    public function handle(LabAlerted $event)
    {
        $lab = $event->visit->form['management'];
        $text  = "ผล Invalid\n";
        $text .= (substr($event->visit->hn, 0, 3) . "*****\n");
        $text .= $lab['np_swab_result_note'];

        (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: $text, sticker: 'warning');
    }
}
