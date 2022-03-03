<?php

namespace App\Tasks;

use App\Managers\NotificationManager;
use Illuminate\Support\Facades\Cache;

class CroissantNeedHelpNotification
{
    public static function run()
    {
        return;
        ////////
        $status = Cache::get('today-koto-logs', []);
        ///////
        $status = Cache::get('croissant-message', 'fetch');
        if (str_starts_with($status, 'fetch') || Cache::has('notify-lab-progress-100')) {
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

        (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: 'มาดู croissant หน่อยยยย', sticker: 'warning');
    }
}
