<?php

namespace App\Managers;

use App\Models\Visit;
use Illuminate\Support\Carbon;

class MocktailManager
{
    public function getReferCase(Visit $visit)
    {
        if (! $visit->swabbed || $visit->form['management']['np_swab_result'] !== 'Detected') {
            return [];
        }

        $insurance = $visit->form['patient']['insurance'];
        $insuranceShow = $insurance;
        if (str_starts_with($insurance, 'ประกันสังคม')) {
            $insurance = 'ประกันสังคม';
        } elseif (str_starts_with($insurance, '30 บาท')) {
            $insurance = '30 บาท';
        }

        $insuranceLookup = [
            'กรมบัญชีกลาง' => 'เบิกจ่ายตรง',
            'ประกันสังคมศิริราช' => 'ปกส SI',
            'ประกันสังคมที่อื่น' => 'ปกส อื่น',
            '30 บาท ศิริาช' => 'UC SI',
            '30 บาท ที่อื่น' => 'UC อื่น',
            'ชำระเงินเอง' => 'จ่ายเอง',
        ];
        $insuranceShow = $insuranceLookup[$insuranceShow] ?? $insuranceShow;
        $content = (new VisitManager)->getReportContent($visit);

        return [
            'slug' => $visit->slug,
            'hn' => $visit->hn,
            'patient_name' => $visit->patient_name,
            'patient_type' => $visit->patient_type === 'บุคคลทั่วไป' ? 'public' : 'staff',
            'tel_no' => $visit->form['patient']['tel_no'],
            'tel_no_alt' => $visit->form['patient']['tel_no_alt'],
            'insurance' => $insurance,
            'insuranceShow' => $insuranceShow,
            'ward' => 'ARI คลินิก',
            'age' => $visit->age_at_visit,
            'weight' => $visit->form['patient']['weight'],
            'date_symptom_start' => $visit->form['symptoms']['date_symptom_start'] ?? $visit->date_visit->format('Y-m-d'),
            'date_covid_infected' => $visit->date_visit->format('Y-m-d'),
            'date_admit_origin' => $visit->form['decision']['date_refer'] ?? $visit->date_visit->addDay()->format('Y-m-d'),
            'date_refer' => $visit->form['decision']['date_refer'] ?? $visit->date_visit->addDay()->format('Y-m-d'),
            'temperature_celsius' =>  $visit->form['patient']['temperature_celsius'],
            'o2_sat' =>  $visit->form['patient']['o2_sat'],
            'symptoms' => $content['symptoms'],
            'asymptomatic_symptom' => $visit->form['symptoms']['asymptomatic_symptom'],
            'onset' => $visit->form['symptoms']['date_symptom_start'] ? Carbon::create($visit->form['symptoms']['date_symptom_start'])->format('M d') : null,
            'date_symptom_start' => $visit->form['symptoms']['date_symptom_start'],
            'fever' => $visit->form['symptoms']['fever'],
            'cough' => $visit->form['symptoms']['cough'],
            'sore_throat' => $visit->form['symptoms']['sore_throat'],
            'rhinorrhoea' => $visit->form['symptoms']['rhinorrhoea'],
            'sputum' => $visit->form['symptoms']['sputum'],
            'fatigue' => $visit->form['symptoms']['fatigue'],
            'anosmia' => $visit->form['symptoms']['anosmia'],
            'loss_of_taste' => $visit->form['symptoms']['loss_of_taste'],
            'myalgia' => $visit->form['symptoms']['myalgia'],
            'diarrhea' => $visit->form['symptoms']['diarrhea'],
            'other_symptoms' => $visit->form['symptoms']['other_symptoms'],
            'comorbids' => $content['โรคประจำตัว'],
            'no_comorbids' => $visit->form['comorbids']['no_comorbids'],
            'dm' => $visit->form['comorbids']['dm'],
            'ht' => $visit->form['comorbids']['ht'],
            'dlp' => $visit->form['comorbids']['dlp'],
            'obesity' => $visit->form['comorbids']['obesity'],
            'other_comorbids' => $visit->form['comorbids']['other_comorbids'],
            'asymptomatic_diagnosis' => $visit->form['diagnosis']['no_symptom'],
            'uri' => $visit->form['diagnosis']['uri'],
            'date_uri' => $visit->form['symptoms']['date_symptom_start'],
            'pneumonia' => $visit->form['diagnosis']['suspected_pneumonia'],
            'date_pneumonia' => $visit->form['symptoms']['date_symptom_start'],
            'gastroenteritis' => $visit->form['symptoms']['diarrhea'],
            'other_diagnosis' => $visit->form['diagnosis']['other_diagnosis'],
            'note' => $visit->form['note'],
            'refer_to' => $visit->form['decision']['refer_to'] ?? null,
            'remark' => $visit->form['decision']['remark'] ?? null,
            'linked' => $visit->form['decision']['linked'] ?? false,
        ];
    }
}
