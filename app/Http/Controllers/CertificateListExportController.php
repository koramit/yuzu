<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class CertificateListExportController extends Controller
{
    protected $daysCriteria;

    public function __invoke()
    {
        $dateVisit = Session::get('certificate-list-export-date', now('asia/bangkok')->format('Y-m-d'));
        $this->daysCriteria = Carbon::create($dateVisit)->lessThan(Carbon::create('2022-01-24')) ? 14 : 10;
        $user = Auth::user();
        $manager = new PatientManager();
        $certificates = Visit::with('patient')
                             ->where('swabbed', true)
                             ->wherePatientType(1)
                             ->whereDateVisit($dateVisit)
                             ->where('form->management->np_swab_result', '<>', 'Detected')
                             ->orWhere(function ($query) use ($dateVisit) {
                                 $query->whereDateVisit($dateVisit)
                                   ->wherePatientType(1)
                                   ->whereScreenType(1)
                                   ->where('form->exposure->atk_positive', true)
                                   ->where('form->management->manage_atk_positive', 'ไม่ต้องการยืนยันผลด้วยวิธี PCR แพทย์พิจารณาให้ยาเลย (หากต้องการเข้าระบบ ให้ติดต่อ 1330 เอง)');
                             })
                             ->get()
                             ->transform(function ($visit) use ($manager) {
                                 $atkPos = $this->getResult($visit) === 'ATK+';
                                 return [
                                      'HN' => $visit->hn,
                                      'name' => $this->getPatientName($visit, $manager),
                                      'patient_type' => $visit->patient_type,
                                      'age' => $visit->age_at_visit,
                                      'tel_no' => $visit->form['patient']['tel_no'],
                                      'tel_no_alt' => $visit->form['patient']['tel_no_alt'],
                                      'ใบรับรองแพทย์' => $this->getRecommendation($visit, $atkPos),
                                      'กักตัวถึง' => $this->quarantineUltil($visit, $atkPos),
                                      'นัดสวอบซ้ำ' => $this->getThaiDate($visit->form['evaluation']['date_reswab'] ?? null),
                                      'np_swab_result' => $atkPos ? 'ผู้มาขอใบรับรองทำการตรวจการติดเชื้อด้วยตนเอง และพบว่าผลบวก' : $visit->form['management']['np_swab_result'],
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

    protected function getRecommendation(Visit &$visit, bool $atkPos)
    {
        $recommendation = $atkPos ? 'ATK+' : ($visit->form['evaluation']['recommendation'] ?? null);
        if (! $recommendation) {
            return null;
        }

        if ($recommendation === 'ไปทำงานได้') {
            return 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลาทุกวัน';
        } elseif ($recommendation === 'ATK+') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน";
        } elseif ($recommendation === 'กักตัว') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน"; // CR 220124 change 14 => 10 days
        } elseif ($recommendation === 'กักตัวนัดสวอบซ้ำ') {
            return "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน และนัดมาตรวจซ้ำ"; // CR 220124 change 14 => 10 days
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

    protected function quarantineUltil(Visit &$visit, bool $atkPos)
    {
        $dateReff = $atkPos ? ($visit->form['exposure']['date_atk_positive'] ?? null) : ($visit->form['evaluation']['date_quarantine_end'] ?? null);
        if ($dateReff && $atkPos) {
            $dateReff = Carbon::create($dateReff)->addDays($this->daysCriteria);
        }
        return $this->getThaiDate($dateReff);
    }

    protected function getResult(Visit &$visit)
    {
        if (
            $visit->patient_type === 'บุคคลทั่วไป'
            && $visit->screen_type === 'เริ่มตรวจใหม่'
            && $visit->form['exposure']['atk_positive']
            && str_starts_with(($visit->form['management']['manage_atk_positive'] ?? ''), 'ไม่ต้องการยืนยันผลด้วยวิธี PCR')
        ) {
            return 'ATK+';
        } else {
            return $visit->form['management']['np_swab_result'];
        }
    }
}
