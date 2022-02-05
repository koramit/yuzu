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
        $enqueueSwabRemain = Visit::whereDatrVisit($today)->whereStatus(3);
        // ห้อง swab
        $swabRemain = Visit::whereDatrVisit($today)->whereStatus(7);

        if (!$enqueueSwabRemain && !$swabRemain) {
            return;
        }

        $text = '';
        if ($enqueueSwabRemain) {
            $text .= "ยังมีค้นไข้ค้างในห้องจัดกระติก {$enqueueSwabRemain} ราย\n";
        }
        if ($swabRemain) {
            $text .= "ยังมีค้นไข้ค้างในห้อง Swab {$swabRemain} ราย\n";
        }

        (new NotificationManager)->notifySubscribers(mode: 'notify_clear_patient', text: $text, sticker: 'warning');
    }
}
