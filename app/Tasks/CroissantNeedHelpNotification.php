<?php

namespace App\Tasks;

use App\Managers\NotificationManager;
use Illuminate\Support\Facades\Cache;

class CroissantNeedHelpNotification
{
    public static function run()
    {
        return;
        //////// toto case
        if (Cache::has('notify-lab-progress-100')) {
            return;
        }

        $lastCheck = collect(Cache::get('today-koto-logs', []))->last();

        if ($lastCheck['timestamp']->diffInMinutes(now()) < 20) {
            return;
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: 'มาดู croissant หน่อยยยย', sticker: 'warning');
        ///////
    }
}
