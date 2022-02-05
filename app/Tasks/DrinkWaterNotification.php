<?php

namespace App\Tasks;

use App\Managers\NotificationManager;

class DrinkWaterNotification
{
    public static function run()
    {
        $text = collect(['ดื่มน้ำด้วยน๊าาา', 'ดื่มน้ำหรือยัง', 'ดื่มน้ำกันเถอะ', 'พักดื่มน้ำเดี๋ยวนึงนะ'])->random() . ' :username:';
        (new NotificationManager)->notifySubscribers(mode: 'notify_drink_water', text: $text, sticker: 'cheerful');
    }
}
