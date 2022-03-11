<?php

namespace App\Listeners;

use App\Events\LabReported;
use App\Managers\NotificationManager;
use App\Models\Visit;
use App\Traits\LabStatReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class NotifyLabSubscribers
{
    use LabStatReport;

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
     * @param  \App\Events\LabReported  $event
     * @return void
     */
    public function handle(LabReported $event)
    {
        $bot = new NotificationManager;

        // เมื่อมีผลบวก
        if ($event->visit->form['management']['np_swab_result'] === 'Detected') {
            $text = $this->labDetectedNowText() . "จ๊ะ :username:";
            $bot->notifyLabSubscribers(mode: 'notify_lab_detected', text: $text, sticker: 'warning');
        }

        if (now()->hour < 10) { // after 17:00 only
            return;
        }

        // เมื่อผลครบตามกลุ่มผู้ป่วย
        $text = $this->labFinished();
        if ($text) {
            $bot->notifyLabSubscribers(mode: 'notify_lab_finished', text: $text.'จ๊ะ :username:', sticker: 'cheerup');
        }

        // รายงานถ่ายทอดสด
        $today = $event->visit->date_visit->format('Y-m-d');
        $total = Visit::whereDateVisit($today)
                        ->whereSwabbed(true)
                        ->whereStatus(4)
                        ->count();
        $reported = Visit::whereDateVisit($today)
                        ->whereSwabbed(true)
                        ->whereStatus(4)
                        ->whereNotNull('form->management->np_swab_result')
                        ->count();

        $progress = intval($reported / $total * 100);
        $progressRange = $progress - ($progress % 10);
        $text = $this->labStatNowText() . "\n\n{$progress}% แล้วจ๊ะ :username:";
        if (collect([30, 40, 50, 60, 70, 80, 90, 100])->contains($progressRange) && !Cache::has("notify-lab-progress-{$progressRange}")) {
            $bot->notifyLabSubscribers(mode: 'notify_lab_progress', text: $text, sticker: 'cheerup');
            Cache::put(key: "notify-lab-progress-{$progressRange}", value: true, ttl: now()->addHours(12));
        }
    }
}
