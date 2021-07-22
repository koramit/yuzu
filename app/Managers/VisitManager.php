<?php

namespace App\Managers;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

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
                'date_swabbed' => null,
                'date_reswabbed' => null,
            ],
            'symptoms' => [
                'asymptomatic_symptom' => false,
                'date_symptom_start' => null,
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
                'unvaccinated' => false,
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
            'md_name' => null,
            'note' => null,
        ];
    }

    public function getConfigs(Visit $visit)
    {
        return [
            'patient_types' => ['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช'],
            'screen_types' => ['เริ่มตรวจใหม่', 'นัดมา swab ครั้งแรก', 'นัดมา swab ซ้ำ'],
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
            'id_staffs' => [
                'อ. ณสิกาญจน์',
                'อ. พรพรรณ',
                'อ. ภาคภูมิ',
                'อ. ภิญโญ',
                'อ. เมธี',
                'อ. ยงค์',
                'อ. ยุพิน',
                'อ. รุจิภาส',
                'อ. วลัยพร',
                'อ. วิษณุ',
                'อ. สุสัณห์',
                'อ. อนุภพ',
            ],
        ];
    }

    public function saveVisit(Visit $visit, array $data, User $user)
    {
        $visit->screen_type = $data['visit']['screen_type'];
        $visit->patient_type = $data['visit']['patient_type'];
        unset($data['visit']);

        // check if input hn at edit form
        if (! $visit->patient_id && $data['patient']['hn']) {
            $patient = (new PatientManager)->manage($data['patient']['hn']);
            if ($patient['found']) {
                $visit->patient_id = $patient['patient']->id;
            } else { // fallback
                $data['patient']['hn'] = null;
                $data['patient']['name'] = $visit->form['patient']['name'];
            }
        }

        $visit->form = $data;
        // $visit->updater_id = $user->id;
        $visit->save();

        $visit->actions()->create(['action' => 'update', 'user_id' => $user->id]);
    }

    public function validateScreening(array $data)
    {
        // validation start here
        $rules = [
            'hn' => 'required|digits:8',
            'patient_type' => 'required',
            'screen_type' => 'required',
            'insurance' => 'required',
            'tel_no' => 'required|digits_between:9,10',
            'tel_no_alt' => 'digits_between:9,10|nullable',
            'temperature_celsius' => 'required|numeric',
            'o2_sat' => 'exclude_if:fatigue,false|required|numeric',
            'evaluation' => 'required',
        ];

        $validator = Validator::make(
            $data['patient'] +
            $data['visit'] +
            $data['symptoms'] +
            $data['exposure'] +
            $data['comorbids'], $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
        } else {
            $errors = [];
        }

        // validate employee
        if ($data['visit']['patient_type'] === 'เจ้าหน้าที่ศิริราช') {
            $patient = $data['patient'];
            if ($patient['no_sap_id'] && ! $patient['position']) {
                $errors['position'] = 'จำเป็นต้องลงข้อมูล ปฏิบัติงาน';
            }
            if (! $patient['no_sap_id'] && ! $patient['sap_id']) {
                $errors['sap_id'] = 'จำเป็นต้องลงข้อมูล SAP ID';
            }
            if (! $patient['no_sap_id'] && $patient['sap_id']) {
                $employee = (new EmployeeManager)->manage($patient['sap_id']);
                if (! $employee['found']) {
                    $errors['sap_id'] = 'ไม่มี ID นี้ในระบบ';
                }
            }
        }

        // validate symptoms
        $symptoms = $data['symptoms'];
        if (! $symptoms['asymptomatic_symptom'] &&
            ! ($symptoms['fever'] ||
                $symptoms['cough'] ||
                $symptoms['sore_throat'] ||
                $symptoms['rhinorrhoea'] ||
                $symptoms['sputum'] ||
                $symptoms['fatigue'] ||
                $symptoms['anosmia'] ||
                $symptoms['loss_of_taste'] ||
                $symptoms['myalgia'] ||
                $symptoms['diarrhea'] ||
                $symptoms['other_symptoms'])
        ) { // if not asymptomatic then need some symptoms
            $errors['symptoms'] = 'โปรดระบุอาการแสดง หากไม่มีอาการโปรดเลือก ไม่มีอาการ';
        } elseif (! $symptoms['asymptomatic_symptom'] && ! $symptoms['date_symptom_start']) {
            $errors['date_symptom_start'] = 'จำเป็นต้องลงข้อมูล วันแรกที่มีอาการ';
        }

        // validate comorbids
        $comorbids = $data['comorbids'];
        if (! $comorbids['no_comorbids']) {
            if (! ($comorbids['dm'] ||
                $comorbids['ht'] ||
                $comorbids['dlp'] ||
                $comorbids['obesity'] ||
                $comorbids['other_comorbids'])
            ) {
                $errors['comorbids'] = 'โปรดระบุโรคประจำตัว';
            }
        }

        // validate exposure
        $exposure = $data['exposure'];
        if (strpos($exposure['evaluation'], 'มีความเสี่ยง') === 0) {
            if (! $exposure['date_latest_expose']) {
                $errors['date_latest_expose'] = 'จำเป็นต้องลงข้อมูล วันสุดท้ายที่สัมผัส';
            }
            if (! $exposure['contact'] && ! $exposure['hot_spot']) {
                $errors['exposure_type'] = 'โปรดระบุประเภทการสัมผัส';
            }
            if ($exposure['contact'] && ! $exposure['contact_type']) {
                $errors['contact_type'] = 'จำเป็นต้องลงข้อมูล ลักษณะการสัมผัส';
            }
            if ($exposure['hot_spot'] && ! $exposure['hot_spot_detail']) {
                $errors['hot_spot_detail'] = 'โปรดะบุพื้นที่เสี่ยง';
            }
        }

        if (str_contains($exposure['evaluation'] ?? 'null', 'อื่นๆ')) {
            if (! $exposure['other_detail']) {
                $errors['exposure_other_detail'] = 'จำเป็นต้องระบุความเสี่ยงอื่นๆ';
            }
        }

        return $errors;
    }

    public function validateSwabByNurse(array $data)
    {
        $errors = $this->validateScreening($data);

        if (! $data['md_name']) {
            $errors['md_name'] = 'จำเป็นต้องลง อาจารย์โรคติดเชื้อเวร';
        }

        if (! $data['management']['np_swab']) {
            $errors['np_swab'] = 'ไม่ติ๊ก NP swab สักหน่อยหร๊าาาา';
        }

        return $errors;
    }

    protected function validateDiagnosis(array $data)
    {
        if ($data['no_symptom'] || $data['suspected_covid_19'] || $data['uri'] || $data['suspected_pneumonia'] || $data['other_diagnosis']) {
            return null;
        } else {
            return ['diagnosis' => 'โปรดระบุการวินิจฉัย'];
        }
    }

    public function validateSwabByMD(array $data)
    {
        $errors = $this->validateScreening($data);

        if ($diagErrors = $this->validateDiagnosis($data['diagnosis'])) {
            $errors += $diagErrors;
        }

        if (! $data['management']['np_swab']) {
            $errors['np_swab'] = 'ไม่ติ๊ก NP swab สักหน่อยหร๊าาาา';
        }

        return $errors;
    }

    public function validateDischarge(array $data)
    {
        $errors = $this->validateScreening($data);

        if ($diagErrors = $this->validateDiagnosis($data['diagnosis'])) {
            $errors += $diagErrors;
        }

        return $errors;
    }

    public function getIdStaff($name)
    {
        return [
            'อ. ณสิกาญจน์' => ['name' => 'รศ.พญ.ณสิกาญจน์ อังคเศกวินัย', 'pln' => 19796],
            'อ. พรพรรณ' => ['name' => 'รศ.พญ.พรพรรณ กู้มานะชัย', 'pln' => 21788],
            'อ. ภาคภูมิ' => ['name' => 'อ.นพ.ภาคภูมิ พุ่มพวง', 'pln' => 36989],
            'อ. ภิญโญ' => ['name' => 'รศ.พญ.ภิญโญ รัตนาอัมพวัลย์', 'pln' => 25523],
            'อ. เมธี' => ['name' => 'รศ.นพ.เมธี ชยะกุลคีรี', 'pln' => 19740],
            'อ. ยงค์' => ['name' => 'รศ.นพ.ยงค์ รงค์รุ่งเรือง', 'pln' => 12158],
            'อ. ยุพิน' => ['name' => 'ศ.พญ.ยุพิน ศุพุทธมงคล', 'pln' => 10622],
            'อ. รุจิภาส' => ['name' => 'รศ.นพ.รุจิภาส สิริจตุภัทร', 'pln' => 31928],
            'อ. วลัยพร' => ['name' => 'อ.พญ.วลัยพร วังจินดา', 'pln' => 39388],
            'อ. วิษณุ' => ['name' => 'ศ.เกียรติคุณ นพ.วิษณุ ธรรมลิขิตกุล', 'pln' => 7965],
            'อ. สุสัณห์' => ['name' => 'ผศ.นพ.สุสัณห์ อาศนะเสน', 'pln' => 21852],
            'อ. อนุภพ' => ['name' => 'ผศ.นพ.อนุภพ จิตต์เมือง', 'pln' => 25707],
        ][$name] ?? [];
    }

    public function getFlash($user)
    {
        return [
            'page-title' => 'MISSING',
            'main-menu-links' => [
                ['icon' => 'thermometer', 'label' => 'ห้องคัดกรอง', 'route' => 'visits.screen-list', 'can' => $user->can('view_screen_list')],
                ['icon' => 'stethoscope', 'label' => 'ห้องตรวจ', 'route' => 'visits.exam-list', 'can' => $user->can('view_exam_list')],
                ['icon' => 'virus', 'label' => 'ห้อง Swab', 'route' => 'visits.swab-list', 'can' => $user->can('view_swab_list')],
                ['icon' => 'address-book', 'label' => 'เวชระเบียน', 'route' => 'visits.mr-list', 'can' => $user->can('view_mr_list')],
                ['icon' => 'calculator', 'label' => 'ประเมิน', 'route' => 'visits.evaluation-list', 'can' => $user->can('view_evaluation_list')],
                ['icon' => 'archive', 'label' => 'รายการเคส', 'route' => 'visits', 'can' => $user->can('view_any_visits')],
            ],
            'action-menu' => [
                ['icon' => 'notes-medical', 'label' => 'เพิ่มเคสใหม่', 'action' => 'create-visit', 'can' => $user->can('create_visit')],
            ],
        ];
    }

    public function setFlash(array $flash)
    {
        Request::session()->flash('page-title', $flash['page-title']);
        Request::session()->flash('main-menu-links', $flash['main-menu-links']);
        Request::session()->flash('action-menu', $flash['action-menu']);
        if ($flash['messages'] ?? null) {
            Request::session()->flash('messages', $flash['messages']);
        }
    }
}
