<?php

namespace App\Managers;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BotCommandsManager
{
    public function handleCommand(string $cmd, User &$user)
    {
        $cmds = collect([
            ['cmd' => 'คิวตรวจ', 'ability' => null],
            ['cmd' => 'ยอด', 'ability' => 'view_any_visits'],
            ['cmd' => 'แลป', 'ability' => 'view_lab_list'],
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
        } else {
            return false;
        }
    }

    protected function handleSwabQueue(User &$user)
    {
        if (!$user->patient_linked) {
            return ['text' => 'กรุณาทำการยืนยัน HN ในระบบก่อน'];
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
            $today = now('asia/bangkok')->format('Y-m-d');
            $labs = DB::table('visits')
                        ->whereDateVisit($today)
                        ->whereSwabbed(true)
                        ->whereStatus(4)->selectRaw("count(JSON_EXTRACT(`form`, '$.management.np_swab_result')) as count_lab, JSON_EXTRACT(`form`, '$.management.np_swab_result') as lab, patient_type")
                        ->groupBy('lab', 'patient_type')
                        ->get();

            $results = [
                'pending' => $labs->filter(fn ($l) => $l->lab === 'null')->flatten(),
                'detected' => $labs->filter(fn ($l) => $l->lab === '"Detected"')->flatten(),
                'not_detected' => $labs->filter(fn ($l) => $l->lab === '"Not detected"')->flatten(),
                'inconclusive' => $labs->filter(fn ($l) => $l->lab === '"Inconclusive"')->flatten(),
            ];

            $stat  = "ผล => รวม (ทั่วไป/บุคลากร)\n";

            foreach ($results as $key => $value) {
                $stat .= $key.' => ';
                $pub = $value->search(fn ($l) => $l->patient_type === 1);
                $pub = $pub === false ? 0 : $value[$pub]->count_lab;
                $hcw = $value->search(fn ($l) => $l->patient_type === 2);
                $hcw = $hcw === false ? 0 : $value[$hcw]->count_lab;
                $stat .= $pub+$hcw . ' (' . $pub . '/' . $hcw . ")\n";
            }

            return [
                'stat' => $stat,
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
}
