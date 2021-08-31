<?php

namespace App\Http\Controllers;

use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class VisitExportController extends Controller
{
    public function __invoke()
    {
        $dateStr = now('asia/bangkok')->format('Y-m-d');
        $visits = Visit::with('patient')
                       ->whereDateVisit($dateStr)
                       ->whereIn('status', [3, 4])
                       ->orderBy('enlisted_screen_at')
                       ->get()
                       ->transform(function ($visit) {
                           return [
                                'วันที่ตรวจ' => $visit->date_visit->format('d-M-Y'),
                                'HN' => $visit->hn,
                                'ชื่อผู้ป่วย' => $visit->patient_name,
                                'ประเภทผู้ป่วย' => $visit->patient_type,
                                'ประเภทการตรวจ' => $visit->screen_type,
                                'สิทธิ์' => $visit->form['patient']['insurance'],
                                'อายุ' => $visit->age_at_visit,
                                'หน่วยอายุ' => $visit->age_at_visit_unit,
                                'แผนก' => $visit->patient_department,
                                'ทำ swab' => $visit->swabbed ? 'ทำ' : '',
                                'หมายเลขหลอด' => $visit->form['management']['specimen_no'] ?? null,
                                'หมายเลขกระติก' => $visit->form['management']['container_no'] ?? null,
                                'กลุ่ม' => $visit->track,
                                'เริ่มคัดกรอง' => $visit->enlisted_screen_at ? $visit->enlisted_screen_at->tz('asia/bangkok')->format('H:i') : null,
                                'จำหน่าย' => $visit->discharged_at ? $visit->discharged_at->tz('asia/bangkok')->format('H:i') : null,
                                'ใช้เวลา' => ($visit->enlisted_screen_at && $visit->discharged_at) ? $visit->discharged_at->diffInMinutes($visit->enlisted_screen_at) : null,
                            ];
                       });

        $reportDateStr = now('asia/bangkok')->format('d-M-Y');
        $filename = "รายงานผู้ป่วย ARI Clinic@{$reportDateStr}.xlsx";

        LoadDataRecord::create([
            'export' => true,
            'configs' => [
                'date_visit' => $dateStr,
                'data' => 'daily_stats',
            ],
            'user_id' => Auth::id(),
        ]);

        return FastExcel::data($visits)->download($filename);
    }
}
