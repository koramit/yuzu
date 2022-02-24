<?php

namespace App\Managers;

use App\Models\User;
use App\Models\Visit;
use App\Traits\LabStatReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BotCommandsManager
{
    use LabStatReport;

    public function handleCommand(string $cmd, User &$user)
    {
        $cmd = strtolower($cmd);

        $cmds = collect([
            ['cmd' => 'คิวตรวจ', 'ability' => null],
            ['cmd' => 'ยอด', 'ability' => 'view_any_visits'],
            ['cmd' => 'แลป', 'ability' => 'view_lab_list'],
            ['cmd' => 'scc', 'ability' => 'view_lab_list'],
            ['cmd' => 'croissant', 'ability' => 'view_lab_list'],
        ]);

        if (! $cmds->pluck('cmd')->contains($cmd)) {
            return false;
        }

        $index = $cmds->search(fn ($c) => $c['cmd'] === $cmd);
        if ($cmds[$index]['ability'] && !$user->can($cmds[$index]['ability'])) {
            return false;
        }

        if ($cmd === 'คิวตรวจ') {
            return $this->handleSwabQueue(user: $user);
        } elseif ($cmd === 'ยอด') {
            return $this->handleTodayStat();
        } elseif ($cmd === 'แลป') {
            return $this->handleTodayLab();
        } elseif ($cmd === 'scc') {
            return $this->handleSccTodayStat();
        } elseif ($cmd === 'croissant') {
            return $this->handleCroissantFeedback();
        } else {
            return false;
        }
    }

    protected function handleSwabQueue(User &$user)
    {
        if (!$user->patient_linked) {
            return [
                'text' => 'กรุณาทำการยืนยัน HN ในระบบก่อน',
                'mode' => 'get_queue_number'
            ];
        }
        $today = now('asia/bangkok')->format('Y-m-d');
        $visit = Visit::whereDateVisit($today)->wherePatientId($user->profile['patient_id'])->first();
        if (!$visit) {
            $text = 'ยังไม่มีการตรวจของ :username: สำหรับวันนี้';
        } elseif (!($visit->form['management']['specimen_no'] ?? null)) {
            $text = ':username: ยังไม่ได้คิวสำหรับวันนี้ กรุณาลองใหม่ในอีกสักครู่';
        } else {
            $text = 'คิวของ :username: ในวันนี้คือ #'.$visit->form['management']['specimen_no'];
        }

        return [
            'text' => $text,
            'mode' => 'get_queue_number',
        ];
    }

    protected function handleTodayStat()
    {
        $data = Cache::remember('bot-cmd-today-stat', now()->addMinutes(10), function () {
            $today = now('asia/bangkok')->format('Y-m-d');
            $stat  = 'คัดกรอง ' . Visit::whereDateVisit($today)->whereNotNull('enqueued_at')->count(). " ราย\n";
            $stat .= 'ส่งพบแพทย์ ' . Visit::whereDateVisit($today)->whereNotNull('enlisted_exam_at')->count(). " ราย\n";
            $stat .= 'ส่ง swab ' . Visit::whereDateVisit($today)->whereNotNull('enlisted_swab_at')->count(). " ราย\n";
            $stat .= 'ทำ swab ' . Visit::whereDateVisit($today)->whereSwabbed(true)->count(). " ราย\n";
            $stat .= 'จำหน่าย ' . Visit::whereDateVisit($today)->whereNotNull('discharged_at')->count(). " ราย\n";
            $stat .= 'คิวที่ระบบแจก ' . Visit::whereDateVisit($today)->whereNotNull('form->management->specimen_no')->count(). " ราย\n";
            $stat .= 'เลขคิวสูงสุด #' . Visit::selectRaw("MAX(CAST(JSON_EXTRACT(`form`, '$.management.specimen_no') AS INT )) AS max")->whereDateVisit($today)->value('max') ?? 0;
            return [
                'stat' => $stat,
                'updated_at' => now()
            ];
        });
        $text = $data['stat'] . "\n\nข้อมูลเมื่อ " . $data['updated_at']->locale('th_TH')->diffForHumans(now()) . 'จ๊ะ :username:';

        return [
            'text' => $text,
            'mode' => 'get_today_stat',
            'sticker' => 'cheerup',
        ];
    }

    protected function handleTodayLab()
    {
        $data = Cache::remember('bot-cmd-today-lab', now()->addMinutes(5), function () {
            return [
                'stat' => $this->labStatNowText(),
                'updated_at' => now()
            ];
        });

        $text = $data['stat'] . "\nข้อมูลเมื่อ " . $data['updated_at']->locale('th_TH')->diffForHumans(now()) . 'จ๊ะ :username:';
        return [
            'text' => $text,
            'mode' => 'get_today_lab',
            'sticker' => 'cheerup',
        ];
    }

    protected function handleSccTodayStat()
    {
        $data = (new TotoMedicineManager)->manage(dateReff: now('asia/bangkok')->format('Y-m-d'));

        if (!$data) {
            return [
                'text' => 'การเชื่อมต่อขัดข้อง',
                'mode' => 'get_today_lab',
            ];
        }

        $text = "เปรียบเทียบข้อมูล SCC (Yuzu) ตอนนี้\n\n";
        foreach ($data['scc'] as $key => $value) {
            $text .= "{$key} => {$value} ({$data['yuzu'][$key]})\n";
        }

        $text .= "\nเคส Yuzu ที่ผลยังไม่ออกแต่ที่ scc ออกแล้ว {$data['yuzu']['scc_ahead_count']} เคส";

        return [
            'text' => $text,
            'mode' => 'get_today_lab',
        ];
    }

    protected function handleCroissantFeedback()
    {
        $logs = Cache::get(key: 'today-koto-logs', default: []);
        if (!count($logs)) {
            return [
                'text' => 'ยังไม่มีแลปเข้าสำหรับวันนี้',
                'mode' => 'get_today_lab',
            ];
        }

        $log = end($logs);
        $text  = 'แลปเข้าเมื่อ ' . $log['timestamp']->locale('th_TH')->diffForHumans(now()) . "\n";
        $text .= 'เข้าใหม่ ' . $log['updated_count'] . " ราย\n";
        $text .= 'เหลืออีก ' . $log['remains'] . " ราย\n";

        return [
            'text' => $text,
            'mode' => 'get_today_lab',
        ];
    }
}
