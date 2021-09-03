<?php

namespace App\Http\Controllers;

use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class OPDCardExportController extends Controller
{
    public function __invoke()
    {
        $dateStr = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));

        $visits = Visit::with('patient')
                       ->whereDateVisit($dateStr)
                       ->whereIn('status', [3, 4])
                       ->orderBy('discharged_at')
                       ->get()
                       ->transform(function ($visit) {
                           return $this->allData($visit);
                       });

        $filename = 'ARI Clinic OPD cards@'.$dateStr.'.xlsx';

        LoadDataRecord::create([
            'export' => true,
            'configs' => [
                'date_visit' => $dateStr,
                'data' => 'opd_card_all_type',
            ],
            'user_id' => Auth::id(),
        ]);

        return FastExcel::data($visits)->download($filename);
    }

    protected function allData(Visit $visit)
    {
        $form = $visit->form;

        return [
            'id' => $visit->id,
            'date_visit' => $visit->date_visit->format('d-M-Y'),
            'HN' => $visit->hn,
            'name' => $visit->patient_name,
            'patient_type' => $visit->patient_type,
            'screen_type' => $visit->screen_type,
            'gender' => $visit->patient->gender,
            'age' => $visit->age_at_visit,
            'age_unit' => $visit->age_at_visit_unit,
            'department' => $visit->patient_department,

            'insurance' => $form['patient']['insurance'],
            'tel_no' => $form['patient']['tel_no'],
            'tel_no_alt' => $form['patient']['tel_no_alt'],

            'sap_id' => $form['patient']['sap_id'],
            'position' => $form['patient']['position'],
            'division' => $form['patient']['division'],
            'service_location' => $form['patient']['service_location'] ?? null,
            'risk' => $form['patient']['risk'],
            'date_latest_expose_by_im' => $this->castDate($form['patient']['date_latest_expose_by_im']),

            'temperature_celsius' => $form['patient']['temperature_celsius'],
            'o2_sat' => $form['patient']['o2_sat'],
            'date_swabbed' => $this->castDate($form['patient']['date_swabbed']),
            'date_reswabbed' => $this->castDate($form['patient']['date_reswabbed']),
            'passport_no' => $form['patient']['passport_no'] ?? null,
            'national_id' => $visit->patient->profile['document_id'],

            'symptoms' => $form['symptoms']['asymptomatic_symptom'] ? 'ไม่มีอาการ' : 'มีอาการ',
            'onset' => $form['symptoms']['date_symptom_start'] ? Carbon::create($form['symptoms']['date_symptom_start'])->format('d-M-Y') : null,
            'fever' => $form['symptoms']['fever'] ? 'YES' : 'NO',
            'cough' => $form['symptoms']['cough'] ? 'YES' : 'NO',
            'sore_throat' => $form['symptoms']['sore_throat'] ? 'YES' : 'NO',
            'rhinorrhoea' => $form['symptoms']['rhinorrhoea'] ? 'YES' : 'NO',
            'sputum' => $form['symptoms']['sputum'] ? 'YES' : 'NO',
            'fatigue' => $form['symptoms']['fatigue'] ? 'YES' : 'NO',
            'anosmia' => $form['symptoms']['anosmia'] ? 'YES' : 'NO',
            'loss_of_taste' => $form['symptoms']['loss_of_taste'] ? 'YES' : 'NO',
            'myalgia' => $form['symptoms']['myalgia'] ? 'YES' : 'NO',
            'diarrhea' => $form['symptoms']['diarrhea'] ? 'YES' : 'NO',
            'other_symptoms' => $form['symptoms']['other_symptoms'] ? 'YES' : 'NO',

            'exposure_evaluation' =>  $form['exposure']['evaluation'],
            'date_latest_expose' => $this->castDate($form['exposure']['date_latest_expose']),
            'contact' =>  $form['exposure']['contact'] ? 'YES' : 'NO',
            'contact_type' =>  $form['exposure']['contact_type'],
            'contact_detail' =>  $form['exposure']['contact_detail'] ? str_replace("\n", ' ', $form['exposure']['contact_detail']) : null,
            'hot_spot' =>  $form['exposure']['hot_spot'] ? 'YES' : 'NO',
            'hot_spot_detail' =>  $form['exposure']['hot_spot_detail'] ? str_replace("\n", ' ', $form['exposure']['hot_spot_detail']) : null,
            'other_detail' =>  $form['exposure']['other_detail'] ? str_replace("\n", ' ', $form['exposure']['other_detail']) : null,

            'no_comorbids' => $form['comorbids']['no_comorbids'] ? 'YES' : 'NO',
            'dm' => $form['comorbids']['dm'] ? 'YES' : 'NO',
            'ht' => $form['comorbids']['ht'] ? 'YES' : 'NO',
            'dlp' => $form['comorbids']['dlp'] ? 'YES' : 'NO',
            'obesity' => $form['comorbids']['obesity'] ? 'YES' : 'NO',
            'weight' => $form['patient']['weight'],
            'height' => $form['patient']['height'],
            'BMI' => $form['patient']['weight'] && $form['patient']['height']
                ? ($form['patient']['weight'] / $form['patient']['height'] / $form['patient']['height'] * 10000)
                : null,
            'other_comorbids' => $form['comorbids']['other_comorbids'],

            'unvaccinated' => $form['vaccination']['unvaccinated'] ? 'YES' : 'NO',
            'Sinovac' => $form['vaccination']['Sinovac'] ? 'YES' : 'NO',
            'Sinopharm' => $form['vaccination']['Sinopharm'] ? 'YES' : 'NO',
            'AstraZeneca' => $form['vaccination']['AstraZeneca'] ? 'YES' : 'NO',
            'Moderna' => $form['vaccination']['Moderna'] ? 'YES' : 'NO',
            'Pfizer' => $form['vaccination']['Pfizer'] ? 'YES' : 'NO',
            'doses' => $form['vaccination']['doses'],
            'date_latest_vacciniated' => $this->castDate($form['vaccination']['date_latest_vacciniated']),

            'no_symptom' => $form['diagnosis']['no_symptom'] ? 'YES' : 'NO',
            'suspected_covid_19' => $form['diagnosis']['suspected_covid_19'] ? 'YES' : 'NO',
            'uri' => $form['diagnosis']['uri'] ? 'YES' : 'NO',
            'suspected_pneumonia' => $form['diagnosis']['suspected_pneumonia'] ? 'YES' : 'NO',
            'other_diagnosis' => $form['diagnosis']['other_diagnosis'] ? 'YES' : 'NO',

            'np_swab' => $form['management']['np_swab'] ? 'YES' : 'NO',
            'tube_no' => $form['management']['specimen_no'],
            'np_swab_result' => $form['management']['np_swab_result'],
            'np_swab_result_note' => $form['management']['np_swab_result_note'],
            'other_tests' => $form['management']['other_tests'] ? str_replace("\n", ' ', $form['management']['other_tests']) : null,
            'home_medication' => $form['management']['home_medication'] ? str_replace("\n", ' ', $form['management']['home_medication']) : null,

            'recommendation' => $this->recommendation($form['recommendation']['choice']),
            'date_isolation_end' => $this->castDate($form['recommendation']['date_isolation_end']),
            'date_reswab' => $this->castDate($form['recommendation']['date_reswab']),
            'date_reswab_next' => $this->castDate($form['recommendation']['date_reswab_next']),

            'note' => $form['note'] ? str_replace("\n", ' ', $form['note']) : null,
        ];
    }

    protected function castDate($dateStr)
    {
        if (! $dateStr) {
            return null;
        }

        return Carbon::create($dateStr)->format('d M Y');
    }

    protected function recommendation($value)
    {
        if (! $value) {
            return null;
        }

        return [
            11  => 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
            12  => 'ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
            13 => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 14 วัน',
        ][$value];
    }

    protected function keyname()
    {
        $binding = [

        ];
    }
}
