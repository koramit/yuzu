<?php

namespace App\Tasks;

use App\Managers\NotificationManager;

class DrinkWaterNotification
{
    public static function run()
    {
        $text = 'ดื่มน้ำด้วยน๊าาา';
        (new NotificationManager)->notifySubscribers(mode: 'notify_drink_water', text: $text);
    }
}
