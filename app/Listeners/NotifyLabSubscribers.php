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

        if (now()->hour >= 10) { // after 17:00 only
            // รายงานถ่ายทอดสด
            $today = $event->date_visit->format('Y-m-d');
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
            \Log::notice('progress '.$progress);
            if (collect([30, 40, 50, 60, 70, 80, 90, 100])->contains($progress) && !Cache::has("notify-lab-progress-{$progress}")) {
                $text = $this->labStatNowText() . "\n\n{$progress}% แล้วจ๊ะ :username:";
                $notifyCount = $bot->notifyLabSubscribers(mode: 'notify_lab_progress', text: $text, sticker: 'cheerup', force: str_contains($text, '100%'));
                if ($notifyCount) {
                    Cache::put(key: "notify-lab-progress-{$progress}", value: true, ttl: now()->addHours(12));
                }
            }

            // เมื่อผลครบตามกลุ่มผู้ป่วย
            $text = $this->labFinished();
            if ($text) {
                $bot->notifyLabSubscribers(mode: 'notify_lab_finished', text: $text.'จ๊ะ :username:', sticker: 'cheerup');
            }
        }

        // เมื่อมีผลบวก
        if ($event->visit->form['management']['np_swab_result'] === 'Detected') {
            $text = $this->labDetectedNowText() . "จ๊ะ :username:";
            $bot->notifyLabSubscribers(mode: 'notify_lab_detected', text: $text, sticker: 'warning');
        }
    }
}
