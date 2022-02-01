<?php

namespace App\Managers;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;

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
            return [
                'stat' => $stat,
                'updated_at' => now()
            ];
        });
        $text = $data['stat'] . "\nข้อมูลเมื่อ" . $data['updated_at']->locale('th_TH')->diffForHumans(now());

        return [
            'text' => $text,
            'mode' => 'get_today_stat'
        ];
    }
}
