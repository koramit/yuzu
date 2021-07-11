<?php

namespace App\Managers;

use App\Models\Visit;

class VisitManager
{
    public function initForm()
    {
        return [
            'patient' => [
                'hn' => null,
                'name' => null,
                'insurance' => null,
                'tel_no' => null,
                'tel_no_alt' => null,
                'sap_id' => null,
                'position' => null,
                'division' => null,
                'risk' => null,
            ],
            'symptoms' => [
                'asymptomatic_symptom' => false,
                'fever' => false,
                'cough' => false,
                'sore_throat' => false,
                'rhinorrhoea' => false,
                'sputum' => false,
                'fatigue' => false,
                'anosmia' => false,
                'loss_of_taste' => false,
                'myalgia' => false,
                'diarrhea' => false,
                'o2_sat' => null,
                'other_symptoms' => null,
            ],
            'exposure' => [
                'evaluation' => null,
                'date_latest_expose' => null,
                'contact' => false,
                'contact_type' => null,
                'contact_name' => null,
                'hot_spot' => false,
                'hot_spot_detail' => null,
                'other_detail' => null,
            ],
            'comorbids' => [
                'no_comorbids' => false,
                'dm' => false,
                'ht' => false,
                'dlp' => false,
                'other_comorbids' => null,
            ],
            'vaccination' => [
                'vaccinated' => false,
                'brand' => null,
                'doses' => null,
                'date_latest_vacciniated' => null,
            ],
            'diagnosis' => [
                'no_symptom' => false,
                'suspected_covid_19' => false,
                'uri' => false,
                'suspected_pneumonia' => false,
                'other_diagnosis' => null,
            ],
            'management' => [
                'np_swab' => false,
                'other_tests' => null,
                'home_medication' => null,
            ],
            'recommendation' => [
                'choice' => null,
                'date_isolation_end' => null,
                'date_reswab' => null,
                'date_reswab_next' => null,
            ],
        ];
    }

    public function getConfigs(Visit $visit)
    {
        return [
            'insurances' => ['กรมบัญชีกลาง', 'ประกันสังคม', '30 บาท', 'ชำระเงินเอง'],
            'risk_levels' => ['ไม่มี', 'ต่ำ', 'ปานกลาง', 'สูง', 'ต้อง Reswab ก่อนกลับไปทำงาน'],
            'symptoms' => [
                ['label' => 'ไข้', 'name' => 'fever'],
                ['label' => 'ไอ', 'name' => 'cough'],
                ['label' => 'เจ็บคอ', 'name' => 'sore_throat'],
                ['label' => 'มีน้ำมูก', 'name' => 'rhinorrhoea'],
                ['label' => 'มีเสมหะ', 'name' => 'sputum'],
                ['label' => 'เหนื่อย/รักต้องเปิด', 'name' => 'fatigue'],
                ['label' => 'จมูกไม่ได้กลิ่น', 'name' => 'anosmia'],
                ['label' => 'ลิ้นไม่ได้รส', 'name' => 'loss_of_taste'],
                ['label' => 'ปวดเมื่อยกล้ามเนื้อ', 'name' => 'myalgia'],
                ['label' => 'ท้องเสีย', 'name' => 'diarrhea'],
            ],
            'evaluations' => ['ไม่มีความเสี่ยง', 'มีความเสี่ยง', 'อื่นๆ'],
            'appointment_evaluations' => ['ความเสี่ยงเดิม', 'มีความเสี่ยงเพิ่มเติม'],
            'contact_types' => ['สัมผัสใกล้ชิด หรือ household contact', 'สัมผัสเป็นเวลาสั้นๆ'],
            'vaccines' => ['Sinovac', 'Sinopharm', 'AstraZeneca', 'Moderna', 'Pfizer'],
            'next_7_days' => $visit->date_visit->addDays(7)->format('Y-m-d'),
            'next_14_days' => $visit->date_visit->addDays(14)->format('Y-m-d'),
            'patchEndpoint' => route('visits.update', $visit),
        ];
    }
}
