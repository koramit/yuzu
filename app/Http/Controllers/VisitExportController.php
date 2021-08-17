<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class VisitExportController extends Controller
{
    public function __invoke()
    {
        $visits = Visit::with('patient')
                       ->whereDateVisit(now('asia/bangkok')->format('Y-m-d'))
                    //    ->whereIn('status', [3, 4])
                       ->where('status', 1)
                       ->whereNotNull('form->management->np_swab_result')
                       ->orderBy('enlisted_screen_at')
                       ->get()
                       ->transform(function ($visit) {
                           return [
                                'HN' => $visit->hn,
                                'ชื่อผู้ป่วย' => $visit->patient_name,
                                'วันที่ตรวจ' => $visit->date_visit->format('d-M-Y'),
                                'ประเภทผู้ป่วย' => $visit->patient_type,
                                'ผล' => $visit->form['management']['np_swab_result'],
                                'หมายเหตุ' => $visit->form['management']['np_swab_result_note'],
                           ];
                           //    return [
                        //         'วันที่ตรวจ' => $visit->date_visit->format('d-M-Y'),
                        //         'HN' => $visit->hn,
                        //         'ชื่อผู้ป่วย' => $visit->patient_name,
                        //         'ประเภทผู้ป่วย' => $visit->patient_type,
                        //         'ประเภทการตรวจ' => $visit->screen_type,
                        //         'สิทธิ์' => $visit->form['patient']['insurance'],
                        //         'อายุ' => $visit->age_at_visit,
                        //         'หน่วยอายุ' => $visit->age_at_visit_unit,
                        //         'แผนก' => $visit->patient_department,
                        //         'ทำ swab' => $visit->swabbed ? 'ทำ' : '',
                        //         'หมายเลขหลอด' => $visit->form['management']['specimen_no'] ?? null,
                        //         'หมายเลขกระติก' => $visit->form['management']['container_no'] ?? null,
                        //         'กลุ่ม' => $visit->track,
                        //         'เริ่มคัดกรอง' => $visit->enlisted_screen_at ? $visit->enlisted_screen_at->tz('asia/bangkok')->format('H:i') : null,
                        //         'จำหน่าย' => $visit->discharged_at ? $visit->discharged_at->tz('asia/bangkok')->format('H:i') : null,
                        //         'ใช้เวลา' => ($visit->enlisted_screen_at && $visit->discharged_at) ? $visit->discharged_at->diffInMinutes($visit->enlisted_screen_at) : null,
                        //     ];
                       });

        $filename = 'รายงานผู้ป่วย ARI Clinic.xlsx';

        return FastExcel::data($visits)->download($filename);
    }
}
