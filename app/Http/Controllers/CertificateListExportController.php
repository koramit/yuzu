<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class CertificateListExportController extends Controller
{
    public function __invoke()
    {
        $dateVisit = Session::get('certificate-list-export-date', now('asia/bangkok')->format('Y-m-d'));
        $user = Auth::user();
        $manager = new PatientManager();
        $certificates = Visit::with('patient')
                             ->where('swabbed', true)
                             ->wherePatientType(1)
                             ->whereDateVisit($dateVisit)
                             ->where('form->management->np_swab_result', '<>', 'Detected')
                             ->get()
                             ->transform(function ($visit) use ($manager) {
                                 return [
                                      'HN' => $visit->hn,
                                      'name' => $this->getPatientName($visit, $manager),
                                      'patient_type' => $visit->patient_type,
                                      'age' => $visit->age_at_visit,
                                      'tel_no' => $visit->form['patient']['tel_no'],
                                      'tel_no_alt' => $visit->form['patient']['tel_no_alt'],
                                      'ใบรับรองแพทย์' => $this->getRecommendation($visit->form['evaluation']['recommendation'] ?? null),
                                      'กักตัวถึง' => $this->getThaiDate($visit->form['evaluation']['date_quarantine_end'] ?? null),
                                      'นัดสวอบซ้ำ' => $this->getThaiDate($visit->form['evaluation']['date_reswab'] ?? null),
                                      'np_swab_result' => $visit->form['management']['np_swab_result'],
                                  ];
                             });

        $filename = 'ARI Medical Certificate Public Patient@'.$dateVisit.'.xlsx';
        LoadDataRecord::create([
            'export' => true,
            'configs' => [
                'date_visit' => $dateVisit,
                'data' => 'medical_certificate_list',
                'patient_type' => 'public',
            ],
            'user_id' => $user->id,
        ]);

        return FastExcel::data($certificates->filter(fn ($v) => $v['age'] >= 18))->download($filename);
    }

    protected function getThaiDate($dateStr)
    {
        if (! $dateStr) {
            return null;
        }

        $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        $ymd = explode('-', $dateStr);

        return ((int) $ymd[2]).' '.($thaiMonths[(int) $ymd[1]]).' '.(((int) $ymd[0]) + 543);
    }

    protected function getRecommendation($recommendation)
    {
        if (! $recommendation) {
            return null;
        }

        if ($recommendation === 'ไปทำงานได้') {
            return 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลาทุกวัน';
        } elseif ($recommendation === 'กักตัว') {
            return 'กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน'; // CR 220124 change 14 => 10 days
        } elseif ($recommendation === 'กักตัวนัดสวอบซ้ำ') {
            return 'กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน และนัดมาตรวจซ้ำ'; // CR 220124 change 14 => 10 days
        } else {
            return '!!!';
        }
    }

    protected function getPatientName(Visit $visit, $manager)
    {
        $patient = $manager->manage($visit->hn, true);
        if ($patient['found'] && $visit->patient_name !== $patient['patient']->full_name) {
            $visit->patient_name = $patient['patient']->full_name;
            $visit->save();
        }

        return $visit->patient_name;
    }
}
