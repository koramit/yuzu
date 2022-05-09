<?php

namespace App\Traits;

use App\Models\Visit;
use Carbon\Carbon;

trait OPDCardExportable
{
    protected $daysCriteria;

    protected function allData(Visit $visit)
    {
        $form = $visit->form;
        $profile = $visit->patient->profile;

        $this->daysCriteria = $visit->date_visit->lessThan(Carbon::create('2022-01-24')) ? 14 : 10;

        return [
            'date_visit' => $visit->date_visit->format('d-M-Y'),
            'HN' => $visit->hn,
            'name' => $visit->patient_name,
            'patient_type' => $visit->patient_type,
            'screen_type' => $visit->screen_type,
            'gender' => $visit->patient->gender,
            'menstruation' => $visit->menstruation,
            'age' => $visit->age_at_visit,
            'age_unit' => $visit->age_at_visit_unit,
            'department' => $visit->patient_department,

            'insurance' => $form['patient']['insurance'],
            'tel_no' => $form['patient']['tel_no'],
            'tel_no_alt' => $form['patient']['tel_no_alt'],
            'address' => $profile['address'],
            'subdistrict' => $profile['subdistrict'],
            'district' => $profile['district'],
            'postcode' => $profile['postcode'],
            'province' => $profile['province'],

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
            'weight' => $visit->weight,
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
            'suspected_covid_19' => $this->suspectedCovidDx($visit), // $form['diagnosis']['suspected_covid_19'] ? 'YES' : 'NO', // defect
            'uri' => $form['diagnosis']['uri'] ? 'YES' : 'NO',
            'suspected_pneumonia' => $form['diagnosis']['suspected_pneumonia'] ? 'YES' : 'NO',
            'other_diagnosis' => $form['diagnosis']['other_diagnosis'] ? str_replace("\n", ' ', $form['diagnosis']['other_diagnosis']) : null,
            'covid_19_infection_by_positive_atk' => ($visit->atk_positive_case && !$visit->swabbed) ? 'YES' : 'NO',
            'atk_positive' => ($form['exposure']['atk_positive'] ?? false) ? 'YES' : 'NO',
            'date_atk_positive' => $this->castDate($form['exposure']['date_atk_positive']),

            'np_swab' => ($form['management']['np_swab'] && $visit->swabbed) ? 'YES' : 'NO',
            'tube_no' => $form['management']['specimen_no'],
            'np_swab_result' => $form['management']['np_swab_result'],
            'np_swab_result_note' => $form['management']['np_swab_result_note'],
            'other_tests' => $form['management']['other_tests'] ? str_replace("\n", ' ', $form['management']['other_tests']) : null,
            'home_medication' => $this->homeMedication($visit), // $form['management']['home_medication'] ? str_replace("\n", ' ', $form['management']['home_medication']) : null, // defect

            'recommendation' => $this->recommendation($visit), // defect
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

    protected function recommendation(Visit &$visit)
    {
        if ($visit->atk_positive_case) {
            return 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน';
        }

        $value = $visit->form['recommendation']['choice'];
        if (! $value) {
            return null;
        }

        return [
            11  => 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
            12  => 'ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
            13 => "กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ {$this->daysCriteria} วัน",
        ][$value];
    }

    protected function suspectedCovidDx(Visit &$visit)
    {
        if ($visit->atk_positive_case) {
            return 'NO';
        }

        if ($visit->form['diagnosis']['suspected_covid_19']) {
            return 'YES';
        }

        if (str_starts_with($visit->form['diagnosis']['public_patient_walkin_diagnosis'] ?? '', 'Suspected COVID-19')) {
            return 'YES';
        }

        return 'NO';
    }

    protected function homeMedication(Visit &$visit)
    {
        if (!$visit->atk_positive_case) {
            return $visit->form['management']['home_medication'] ? str_replace("\n", ' ', $visit->form['management']['home_medication']) : null;
        }

        if ($visit->form['management']['atk_positive_without_pcr_medication'] ?? false) {
            return $visit->form['management']['atk_positive_without_pcr_medication'];
        }

        return str_replace("\n", ' ', $visit->form['management']['atk_positive_without_pcr_medication']);
    }
}
