<?php

namespace App\Tasks;

use App\Managers\NotificationManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;

class CroissantNeedHelpNotification
{
    public static function run()
    {
        $count = Visit::whereDateVisit(today()->format('Y-m-d'))
                        ->whereNull('form->management->np_swab_result')
                        ->whereSwabbed(true)
                        ->count();

        if ($count === 0) {
            return;
        }

        $text = 'ดึงแลปไม่ได้เกิน 30 นาที';

        $logs = Cache::get(key: 'lis-api-logs', default: collect([]));
        if (! $logs->last()) {
            (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: $text, sticker: 'warning');
            return;
        }

        $log = $logs->last();
        if (now()->diffInMinutes($log['timestamp']) > 30) {
            (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: $text, sticker: 'warning');
        }
    }
}
