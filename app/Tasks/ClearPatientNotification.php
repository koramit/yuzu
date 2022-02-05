<?php

namespace App\Tasks;

use App\Managers\NotificationManager;
use App\Models\Visit;

class ClearPatientNotification
{
    public static function run()
    {
        $today = now('asia/bangkok')->format('Y-m-d');
        // ห้องจัดกระติก
        $enqueueSwabRemain = Visit::whereDateVisit($today)->whereStatus(3)->count();
        // ห้อง swab
        $swabRemain = Visit::whereDateVisit($today)->whereStatus(7)->count();

        if (!$enqueueSwabRemain && !$swabRemain) {
            return;
        }

        $text = '';
        if ($enqueueSwabRemain) {
            $text .= "ยังมีคนไข้ค้างในห้องจัดกระติก {$enqueueSwabRemain} ราย\n";
        }
        if ($swabRemain) {
            $text .= "ยังมีคนไข้ค้างในห้อง Swab {$swabRemain} ราย\n";
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_clear_patient', text: $text, sticker: 'warning');
    }
}
