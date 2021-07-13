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
                'no_sap_id' => false,
                'sap_id' => null,
                'position' => null,
                'division' => null,
                'risk' => null,
                'temperature_celsius' => null,
                'weight' => null,
                'height' => null,
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
                'obesity' => false,
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
            'patient_types' => ['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช'],
            'screen_types' => ['เริ่มตรวจใหม่', 'นัดมา swab', 'นัดมา swab day 7', 'นัดมา swab day 14'],
            'insurances' => ['กรมบัญชีกลาง', 'ประกันสังคม', '30 บาท', 'ชำระเงินเอง'],
            'positions' => ['อาจารย์คณะพยาบาล', 'เจ้าหน้าที่คณะพยาบาล', 'นักศึกษาคณะพยาบาล', 'outsource'],
            'risk_levels' => ['ไม่มีความเสี่ยง', 'ความเสี่ยงต่ำ', 'ความเสี่ยงปานกลาง', 'ความเสี่ยงสูง', 'ต้อง Reswab ก่อนกลับไปทำงาน'],
            'symptoms' => [
                ['label' => 'ไข้', 'name' => 'fever'],
                ['label' => 'ไอ', 'name' => 'cough'],
                ['label' => 'เจ็บคอ', 'name' => 'sore_throat'],
                ['label' => 'มีน้ำมูก', 'name' => 'rhinorrhoea'],
                ['label' => 'มีเสมหะ', 'name' => 'sputum'],
                ['label' => 'เหนื่อย/แน่นอก', 'name' => 'fatigue'],
                ['label' => 'จมูกไม่ได้กลิ่น', 'name' => 'anosmia'],
                ['label' => 'ลิ้นไม่ได้รส', 'name' => 'loss_of_taste'],
                ['label' => 'ปวดเมื่อยกล้ามเนื้อ', 'name' => 'myalgia'],
                ['label' => 'ท้องเสีย', 'name' => 'diarrhea'],
            ],
            'evaluations' => ['ไม่มีความเสี่ยง', 'มีความเสี่ยง', 'อื่นๆ'],
            'appointment_evaluations' => ['ความเสี่ยงเดิม', 'มีความเสี่ยงเพิ่มเติม'],
            'contact_types' => ['สัมผัสใกล้ชิด หรือ household contact', 'สัมผัสเป็นเวลาสั้นๆ'],
            'vaccines' => ['Sinovac', 'Sinopharm', 'AstraZeneca', 'Moderna', 'Pfizer'],
            'vaccination_doses' => [
                ['value' => 1, 'label' => '1 เข็ม'],
                ['value' => 2, 'label' => '2 เข็ม'],
                ['value' => 3, 'label' => '3 เข็ม'],
            ],
            'next_7_days' => $visit->date_visit->addDays(7)->format('Y-m-d'),
            'next_14_days' => $visit->date_visit->addDays(14)->format('Y-m-d'),
            'patchEndpoint' => route('visits.update', $visit),
            'public_recommendations' => [
                [
                    'value' => 11,
                    'label' => 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
                ],
                [
                    'value' => 12,
                    'label' => 'ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
                ],
                [
                    'value' => 13,
                    'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 14 วัน',
                ],
            ],
            'employee_recommendations' => [
                [
                    'value' => 21,
                    'label' => 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
                ],
                [
                    'value' => 22,
                    'label' => 'ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา และ นัด swab ซ้ำ',
                ],
                [
                    'value' => 23,
                    'label' => 'ลางาน 1 - 2 วัน หากอาการดีขึ้น ไปทำงานได้โดยใส่หน้ากากอนามัยตลอดเวลา',
                ],
                [
                    'value' => 24,
                    'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 7 วัน',
                ],
                [
                    'value' => 25,
                    'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นเป็นเวลา 7 - 14 วัน หากผลตรวจที่วันที่ 7 หลังสัมผัสโรคไม่พบเชื้อ ผู้บังคับบัญชาอาจพิจารณาอนุญาตให้กลับมาทำงานได้',
                ],
                [
                    'value' => 26,
                    'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 14 วัน',
                ],
            ],
        ];
    }
}
