<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait LabStatReport
{
    protected function labStatNowText()
    {
        $today = now('asia/bangkok')->format('Y-m-d');
        $labs = DB::table('visits')
                ->whereDateVisit($today)
                ->whereSwabbed(true)
                ->whereStatus(4)
                ->selectRaw("count(JSON_EXTRACT(`form`, '$.management.np_swab_result')) as count_lab, JSON_EXTRACT(`form`, '$.management.np_swab_result') as lab, patient_type")
                ->groupBy('lab', 'patient_type')
                ->get();

        $results = [
            'pending' => $labs->filter(fn ($l) => $l->lab === 'null')->flatten(),
            'detected' => $labs->filter(fn ($l) => $l->lab === '"Detected"')->flatten(),
            'not_detected' => $labs->filter(fn ($l) => $l->lab === '"Not detected"')->flatten(),
            'inconclusive' => $labs->filter(fn ($l) => $l->lab === '"Inconclusive"')->flatten(),
        ];

        $stat = "ผล => รวม (ทั่วไป/บุคลากร)\n";
        $totPub = 0;
        $totHcw = 0;

        foreach ($results as $key => $value) {
            $stat .= $key.' => ';
            $pub = $value->search(fn ($l) => $l->patient_type === 1);
            $pub = $pub === false ? 0 : $value[$pub]->count_lab;
            $hcw = $value->search(fn ($l) => $l->patient_type === 2);
            $hcw = $hcw === false ? 0 : $value[$hcw]->count_lab;
            $stat .= $pub+$hcw . ' (' . $pub . '/' . $hcw . ")\n";
            $totPub += $pub;
            $totHcw += $hcw;
        }

        $stat .= "swab => ". ($totPub+$totHcw) ." ({$totPub}/{$totHcw})\n";

        return $stat;
    }

    protected function labDetectedNowText()
    {
        $today = now('asia/bangkok')->format('Y-m-d');
        $labs = DB::table('visits')
                ->whereDateVisit($today)
                ->whereSwabbed(true)
                ->where('form->management->np_swab_result', 'Detected')
                ->count();

        return "มีเคสบวกใหม่ รวมเป็น {$labs} ราย";
    }

    protected function labFinished()
    {
        $today = now('asia/bangkok')->format('Y-m-d');
        $labs = DB::table('visits')
                ->whereDateVisit($today)
                ->whereSwabbed(true)
                ->whereStatus(4)
                ->selectRaw("count(JSON_EXTRACT(`form`, '$.management.np_swab_result')) as count_lab, JSON_EXTRACT(`form`, '$.management.np_swab_result') as lab, patient_type")
                ->groupBy('lab', 'patient_type')
                ->get();

        $pendings = $labs->filter(fn ($l) => $l->lab === 'null')->flatten();
        $pub = $pendings->search(fn ($l) => $l->patient_type === 1);
        $pub = $pub === false ? 0 : $pendings[$pub]->count_lab;
        $hcw = $pendings->search(fn ($l) => $l->patient_type === 2);
        $hcw = $hcw === false ? 0 : $pendings[$hcw]->count_lab;

        if (!$hcw && !$pub) {
            return 'ผลแลปวันนี้ออกครบแล้ว';
        }

        if (!$pub) {
            return 'ผลแลปวันนี้ของผู้ป่วยกลุ่มบุคลทั่วไปออกครบแล้ว';
        }

        if (!$hcw) {
            return 'ผลแลปวันนี้ของผู้ป่วยกลุ่มเจ้าหน้าที่ออกครบแล้ว';
        }

        return false;
    }
}
