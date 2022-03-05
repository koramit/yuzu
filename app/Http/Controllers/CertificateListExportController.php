<?php

namespace App\Http\Controllers;

use App\Models\LoadDataRecord;
use App\Models\Visit;
use App\Traits\MedicalCertifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class CertificateListExportController extends Controller
{
    use MedicalCertifiable;

    protected $daysCriteria;

    public function __invoke()
    {
        $dateVisit = Session::get('certificate-list-export-date', now('asia/bangkok')->format('Y-m-d'));
        $this->daysCriteria = Carbon::create($dateVisit)->lessThan(Carbon::create('2022-01-24')) ? 14 : 10;
        $user = Auth::user();
        $certificates = Visit::with('patient')
                             ->where('swabbed', true)
                             ->wherePatientType(1)
                             ->whereDateVisit($dateVisit)
                             ->whereNotNull('form->management->np_swab_result')
                             ->where('form->management->np_swab_result', '<>', 'Detected')
                             ->withPublicPatientWalkinATKPosWithoutPCR($dateVisit)
                             ->get()
                             ->transform(function ($visit) {
                                 return [
                                      'HN' => $visit->hn,
                                      'name' => $visit->patient_name,
                                      'patient_type' => $visit->patient_type,
                                      'age' => $visit->age_at_visit,
                                      'tel_no' => $visit->form['patient']['tel_no'],
                                      'tel_no_alt' => $visit->form['patient']['tel_no_alt'],
                                      'ใบรับรองแพทย์' => $this->getRecommendation($visit->form['evaluation']['recommendation'] ?? null),// $this->getRecommendation($visit, $atkPos),
                                      'กักตัวถึง' => $this->getThaiDate($visit->form['evaluation']['date_quarantine_end'] ?? null), //$this->quarantineUltil($visit, $atkPos),
                                      'นัดสวอบซ้ำ' => $this->getThaiDate($visit->form['evaluation']['date_reswab'] ?? null),
                                      'np_swab_result' => $visit->atk_positive_case ? 'ผู้มาขอใบรับรองทำการตรวจการติดเชื้อด้วยตนเอง และพบว่าผลบวก' : $visit->form['management']['np_swab_result'],
                                      'swab_at' => $visit->form['management']['container_swab_at'],
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

        $filtered = $certificates->filter(fn ($v) => $v['age'] >= 18)
                                ->filter(fn ($v) => !(!$v['ใบรับรองแพทย์'] && $v['swab_at'] === 'Sky Walk'))
                                ->map(function ($v) {
                                    unset($v['swab_at']);
                                    return $v;
                                })
                                ->sortBy([
                                    ['ใบรับรองแพทย์', 'asc'],
                                    ['np_swab_result', 'asc']
                                ]);

        Cache::put(key: 'send-scc-certs-job', value: $dateVisit, ttl: now()->addMinutes(3));
        return FastExcel::data($filtered)->download($filename);
    }
}
