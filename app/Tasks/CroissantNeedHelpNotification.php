<?php

namespace App\Tasks;

use App\Managers\NotificationManager;
use Illuminate\Support\Facades\Cache;

class CroissantNeedHelpNotification
{
    public static function run()
    {
        if (Cache::get('croissant-message') === 'fetch') {
            return;
        }

        $needHelp = false;
        $lastUpdate = Cache::get('croissant-latest');
        if (now()->greaterThan($lastUpdate) && (now()->diffInMinutes($lastUpdate) > 5)) { // stopped for too long
            $needHelp = true;
        } elseif (count(Cache::get('croissant-not-found')) > 20) { // too many not found
            $needHelp = true;
        }

        if (!$needHelp) {
            return;
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_clear_patient', text: 'มาดู croissant หน่อยยยย', sticker: 'warning');
    }
}
