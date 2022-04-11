<?php

namespace App\Managers;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
                'tel_no_confirmed' => null,
                'no_sap_id' => false,
                'sap_id' => null,
                'position' => null,
                'division' => null,
                'service_location' =>null,
                'risk' => null,
                'date_latest_expose_by_im' => null,
                'temperature_celsius' => null,
                'o2_sat' => null,
                'weight' => null,
                'height' => null,
                'date_swabbed' => null,
                'date_reswabbed' => null,
                'track' => null,
                'passport_no' => null,
                'mobility' => null,
                'menstruation' => null,
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
                'other_symptoms' => null,
            ],
            'exposure' => [
                'atk_positive' => false,
                'date_atk_positive' => null,
                'evaluation' => null,
                'date_latest_expose' => null,
                'contact' => false,
                'contact_type' => null,
                'contact_detail' => null,
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
                'Sinovac' => false,
                'Sinopharm' => false,
                'AstraZeneca' => false,
                'Moderna' => false,
                'Pfizer' => false,
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
                'specimen_no' => null,
                'container_no' => null,
                'container_swab_at' => null,
                'swab_at' => null,
                'on_hold' => false,
                'np_swab_result' => null,
                'np_swab_result_note' => null,
                'screenshot' => null,
                'other_tests' => null,
                'home_medication' => null,
            ],
            'recommendation' => [
                'choice' => null,
                'date_isolation_end' => null,
                'date_reswab' => null,
                'date_reswab_next' => null,
            ],
            'evaluation' => [
                'consultation' => null,
                'outcome' => null,
                'recommendation' => null,
                'note' => null,
            ],
            'md_name' => null,
            'note' => null,
        ];
    }

    public function getConfigs(Visit $visit)
    {
        return [
            'tracks' => ['Walk-in', 'นัด หรือ staff หรือ ญาติเจ้าหน้าที่'],
            'patient_types' => ['บุคคลทั่วไป', 'เจ้าหน้าที่ศิริราช'],
            'screen_types' => ['เริ่มตรวจใหม่', 'นัดมา swab ครั้งแรก', 'นัดมา swab ซ้ำ'],
            'insurances' => ['กรมบัญชีกลาง', 'ประกันสังคมศิริราช', 'ประกันสังคมที่อื่น', '30 บาท ศิริาช', '30 บาท ที่อื่น', 'ชำระเงินเอง'],
            'positions' => ['อาจารย์คณะพยาบาล', 'เจ้าหน้าที่คณะพยาบาล', 'นักศึกษาคณะพยาบาล', 'outsource'],
            'risk_levels' => ['ไม่มีความเสี่ยง', 'ความเสี่ยงต่ำ', 'ความเสี่ยงปานกลาง', 'ความเสี่ยงสูง', 'ต้อง Reswab ก่อนกลับไปทำงาน', 'ไม่ทราบ'],
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
            'menstruations' => ['กำลังตั้งครรภ์', 'ประจำเดือนมาปรกติ', 'ประจำเดือนมาไม่ปรกติ', 'ภาวะวัยหมดประจำเดือน'],
            'evaluations' => ['ไม่มีความเสี่ยง', 'มีความเสี่ยง', 'อื่นๆ'],
            'appointment_evaluations' => ['ความเสี่ยงเดิม', 'มีความเสี่ยงเพิ่มเติม', 'ก่อนไป elective', 'ก่อนไปต่างประเทศ', 'ก่อนไปตามเสด็จ'],
            'appointment_evaluations_public' => ['ความเสี่ยงเดิม', 'มีความเสี่ยงเพิ่มเติม', 'ผ่านการบริหารความเสี่ยง'],
            'contact_types' => ['สัมผัสใกล้ชิด หรือ household contact', 'สัมผัสเป็นเวลาสั้นๆ'],
            'vaccines' => ['Sinovac', 'Sinopharm', 'AstraZeneca', 'Moderna', 'Pfizer'],
            'vaccination_doses' => [
                ['value' => 1, 'label' => '1 เข็ม'],
                ['value' => 2, 'label' => '2 เข็ม'],
                ['value' => 3, 'label' => '3 เข็ม'],
                ['value' => 4, 'label' => '4 เข็ม'],
                ['value' => 5, 'label' => '5 เข็ม'],
            ],
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
                    'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน',
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
            'swab_units' => ['SCG', 'Sky Walk'],
            'mobilities' => ['เดินได้', 'รถนั่ง', 'เปลนอน'],
            'public_patient_walkin_diagnosis_atk_positive' => ['Suspected COVID-19 infection (Pending for PCR)', 'COVID-19 infection by positive ATK'],
            'public_patient_walkin_managment_atk_positive' => ['NP swab for PCR test of SARS-CoV-2', 'ไม่ต้องการยืนยันผลด้วยวิธี PCR แพทย์พิจารณาให้ยาเลย (หากต้องการเข้าระบบ ให้ติดต่อ 1330 เอง)'],
            'public_patient_walkin_managment_atk_positive_with_pcr' => ['NP swab for PCR test of SARS-CoV-2'],
            'public_patient_walkin_managment_atk_positive_without_pcr' => ['ไม่ต้องการยืนยันผลด้วยวิธี PCR แพทย์พิจารณาให้ยาเลย (หากต้องการเข้าระบบ ให้ติดต่อ 1330 เอง)'],
            'atk_positive_without_pcr_medications' => ['ไม่รับยา', 'Set A', 'Set B', 'Set C'],
            'atk_positive_without_pcr_recommendation' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน จะส่งใบรับรองแพทย์ไปทาง sms ด้วยหมายเลขโทรศัพท์ที่ให้ไว้'
        ];
    }

    public function saveVisit(Visit $visit, array $data, User $user)
    {
        $visit->screen_type = $data['visit']['screen_type'];
        $visit->patient_type = $data['visit']['patient_type'];
        unset($data['visit']);
        foreach (array_keys($data) as $key) {
            if (str_contains($key, $visit->slug)) {
                unset($data[$key]);
            }
        }
        // check if input hn at edit form
        if (! $visit->patient_id && $data['patient']['hn']) {
            $patient = (new PatientManager)->manage($data['patient']['hn']);
            if ($patient['found']) {
                $visit->patient_id = $patient['patient']->id;
                $data['patient']['hn'] = $patient['patient']->hn;
            } else { // fallback
                $data['patient']['hn'] = null;
                $data['patient']['name'] = $visit->form['patient']['name'];
            }
        }
        $visit->form = $data;
        $visit->save();

        $visit->actions()->create(['action' => 'update', 'user_id' => $user->id]);
    }

    public function validateScreening(array $data)
    {
        // validation start here
        $rules = [
            'name' => 'required|string',
            'track' => 'required',
            'mobility' => 'required',
            'patient_type' => 'required',
            'screen_type' => 'required',
            'insurance' => 'required',
            'tel_no' => 'required|digits_between:9,10',
            'tel_no_alt' => 'digits_between:9,10|nullable',
            'tel_no_confirmed' => ['required', 'boolean', Rule::in([true])],
            'temperature_celsius' => 'required|numeric',
            'o2_sat' => 'exclude_if:fatigue,false|required|numeric',
            'evaluation' => 'required',
        ];

        $validator = Validator::make(
            $data['patient'] +
            $data['visit'] +
            $data['symptoms'] +
            $data['exposure'] +
            $data['comorbids'],
            $rules,
            [
                'name.required' => 'จำเป็นต้องลง ชื่อผู้ป่วย',
                'tel_no_confirmed.required' => 'โปรดยืนยันหมายเลขโทรศัพท์ของผู้ป่วย',
                'tel_no_confirmed.in' => 'โปรดยืนยันหมายเลขโทรศัพท์ของผู้ป่วย',
            ]
        );

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
        if ($exposure['atk_positive'] && !$exposure['date_atk_positive']) {
            $errors['date_atk_positive'] = 'จำเป็นต้องลงวันที่ตรวจ ATK ได้ผลบวก';
        }

        return $errors;
    }

    public function validateSwabByNurse(array $data)
    {
        $errors = $this->validateScreening($data);
        if ($diagErrors = $this->validateDiagnosis($data['diagnosis'])) {
            $errors += $diagErrors;
        }
        if (! $data['md_name']) {
            $errors['md_name'] = 'จำเป็นต้องลง อาจารย์โรคติดเชื้อเวร';
        }
        if (! $data['management']['np_swab']) {
            $errors['np_swab'] = 'จำเป็นต้องเลือก NP swab';
        }

        return $errors;
    }

    public function validateSwabByMD(array $data)
    {
        $errors = $this->validateScreening($data);
        if ($diagErrors = $this->validateDiagnosis($data['diagnosis'])) {
            $errors += $diagErrors;
        }
        if (! $data['management']['np_swab']) {
            $errors['np_swab'] = 'จำเป็นต้องเลือก NP swab';
        }

        return $errors;
    }

    public function validateDischarge(array $data)
    {
        $errors = $this->validateScreening($data);
        if ($diagErrors = $this->validateDiagnosis($data['diagnosis'])) {
            $errors += $diagErrors;
        }
        if (isset($data['management']['manage_atk_positive']) && str_starts_with($data['management']['manage_atk_positive'], 'ไม่ต้องการยืนยันผลด้วยวิธี PCR')) {
            if (!($data['management']['atk_positive_without_pcr_medication'] ?? null) && !($data['management']['atk_positive_without_pcr_medication_other'] ?? null)) {
                $errors += ['atk_positive_without_pcr_medication' => 'โปรดระบุการรับยา'];
            }
        }

        return $errors;
    }

    protected function validateDiagnosis(array $data)
    {
        if ($data['no_symptom'] || $data['suspected_covid_19'] || $data['uri'] || $data['suspected_pneumonia'] || $data['other_diagnosis'] || ($data['public_patient_walkin_diagnosis'] ?? null)) {
            return null;
        } else {
            return ['diagnosis' => 'โปรดระบุการวินิจฉัย'];
        }
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
                ['icon' => 'box', 'label' => 'จัดกระติก', 'route' => 'visits.enqueue-swab-list', 'can' => $user->can('view_enqueue_swab_list')],
                ['icon' => 'virus', 'label' => 'ห้อง Swab', 'route' => 'visits.swab-list', 'can' => $user->can('view_swab_list')],
                // ['icon' => 'bullhorn', 'label' => 'เรียกคิว Swab', 'route' => 'visits.swab-notification-list', 'can' => $user->can('view_swab_notification_list')],
                ['icon' => 'address-book', 'label' => 'เวชระเบียน', 'route' => 'visits.mr-list', 'can' => $user->can('view_mr_list')],
                ['icon' => 'list-ol', 'label' => 'ธุรการ', 'route' => 'visits.queue-list', 'can' => $user->can('view_queue_list')],
                ['icon' => 'inbox', 'label' => 'รายการเคสวันนี้', 'route' => 'visits.today-list', 'can' => $user->can('view_today_list')],
                ['icon' => 'satellite-dish', 'label' => 'ผลแลปวันนี้', 'route' => 'visits.lab-list', 'can' => $user->can('view_lab_list')],
                ['icon' => 'archive', 'label' => 'รายการเคสทั้งหมด', 'route' => 'visits', 'can' => $user->can('view_any_visits')],
                ['icon' => 'key', 'label' => 'รหัสปฏิบัติงาน', 'route' => 'duty-token.show', 'can' => $user->can('view_active_duty_token')],
                ['icon' => 'procedure', 'label' => 'Decision', 'route' => 'decisions', 'can' => $user->can('view_decision_list')],
                ['icon' => 'certificate', 'label' => 'Certification', 'route' => 'certifications', 'can' => $user->can('view_certification_list')],
            ],
            'action-menu' => [
                ['icon' => 'notes-medical', 'label' => 'เพิ่มเคสใหม่', 'action' => 'create-visit', 'can' => $user->can('create_visit')],
                ['icon' => 'calendar-alt', 'label' => 'เพิ่มเคสล่วงหน้า', 'action' => 'create-appointment', 'can' => $user->hasRole('nurse') || $user->hasRole('admin') || $user->hasRole('root')],
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

    public function getReportContent(Visit $visit)
    {
        $contentVisit = [
            'hn' => $visit->hn,
            'ชื่อ' => $visit->patient_name,
            'เพศ/อายุ' => trim($visit->gender.' '.$visit->age_at_visit_label),
            'สิทธิ์การรักษา' => $visit->form['patient']['insurance'],
            'วันที่ตรวจ' => $visit->date_visit->format('d M Y'),
            'ประเภทการตรวจ' => $visit->screen_type,
        ];

        if ($visit->screen_type === 'นัดมา swab ซ้ำ') {
            if ($visit->form['patient']['date_swabbed']) {
                $contentVisit['เคย swab ครั้งที่ 1 เมื่อวันที่'] = Carbon::create($visit->form['patient']['date_swabbed'])->format('d M Y');
            }
            if ($visit->form['patient']['date_reswabbed']) {
                $contentVisit['เคย swab ครั้งที่ 2 เมื่อวันที่'] = Carbon::create($visit->form['patient']['date_reswabbed'])->format('d M Y');
            }
        }

        $contentVisit['ประเภทผู้ป่วย'] = $visit->patient_type;
        if ($visit->patient_type === 'เจ้าหน้าที่ศิริราช') {
            $contentVisit['sap id'] = $visit->form['patient']['sap_id'];
            $contentVisit['ปฏิบัติงาน'] = $visit->form['patient']['position'];
            $contentVisit['พื้นที่ปฏิบัติงาน'] = $visit->form['patient']['service_location'] ?? null;
            $contentVisit['ความเสี่ยง'] = $visit->form['patient']['risk'];
        }
        $contentVisit['หมายเลขโทรศัพท์'] = $visit->tel_no;

        // symptoms
        $symptoms = $visit->form['symptoms'];
        $symptomHeaders = ['อุณหภูมิ (℃)' => $visit->form['patient']['temperature_celsius']];
        if ($visit->form['patient']['o2_sat']) {
            $symptomHeaders['O₂ sat (% RA)'] = $visit->form['patient']['o2_sat'];
        }
        if ($symptoms['date_symptom_start']) {
            $symptomHeaders['วันแรกที่มีอาการ'] = Carbon::create($symptoms['date_symptom_start'])->format('d M Y');
        }
        if ($symptoms['asymptomatic_symptom']) {
            $symptoms = 'ไม่มีอาการ';
        } else {
            $symptomsList = $this->getConfigs($visit)['symptoms'];
            $text = '';
            foreach ($symptomsList as $symptom) {
                if ($symptoms[$symptom['name']]) {
                    $text .= "{$symptom['label']} ";
                }
            }
            $text .= $symptoms['other_symptoms'];
            $symptoms = $text;
        }

        // atk_positive
        if ($visit->form['exposure']['atk_positive'] ?? false) {
            $atkPositive = 'ตรวจ Antigen test kit (ATK) ได้ผลบวก';
            if ($visit->form['exposure']['date_atk_positive'] ?? null) {
                $atkPositive .= (' เมื่อ '.Carbon::create($visit->form['exposure']['date_atk_positive'])->format('d M Y'));
            }
        } else {
            $atkPositive = null;
        }

        // other conditions
        $otherConditions = '';
        if ($visit->form['patient']['weight'] ?? null) {
            $otherConditions .= 'น้ำหนัก ' . $visit->form['patient']['weight'] . ' กก.<br>';
        }

        if (
            ($visit->form['patient']['menstruation'] ?? null) &&
            (($visit->patient->profile['gender'] ?? null) === 'female')
        ) {
            $otherConditions .= $visit->form['patient']['menstruation'];
        }

        // exposure
        $exposure = $visit->form['exposure'];
        if ($exposure['evaluation'] === 'อื่นๆ') {
            $exposure = $exposure['other_detail'];
            $lines = explode("\n", $exposure);
            if (count($lines) > 1) {
                $exposure = collect($lines)->map(function ($line) {
                    return "<p>{$line}</p>";
                })->join('');
            }
        } elseif (str_starts_with($exposure['evaluation'], 'มีความเสี่ยง')) {
            $text = $exposure['evaluation'].'<br>';
            $text .= ('วันสุดท้ายที่สัมผัส - '.Carbon::create($exposure['date_latest_expose'])->format('d M Y').'<br>');
            if ($exposure['contact']) {
                $text .= ('สัมผัสผู้ติดเชื้อยืนยัน - '.$exposure['contact_type'].' '.($exposure['contact_detail'] ?? '').'<br>');
            }
            if ($exposure['hot_spot']) {
                $text .= ('ไปพื้นที่เสี่ยง - '.$exposure['hot_spot_detail'].'<br>');
            }
            $exposure = $text;
        } else {
            $exposure = $exposure['evaluation'];
        }

        // comordibs
        $comorbids = $visit->form['comorbids'];
        if ($comorbids['no_comorbids']) {
            $comorbids = 'ไม่มี';
        } else {
            $text = '';
            if ($comorbids['dm']) {
                $text .= 'เบาหวาน ';
            }
            if ($comorbids['ht']) {
                $text .= 'ความดันโลหิตสูง ';
            }
            if ($comorbids['dlp']) {
                $text .= 'ไขมันในเลือดสูง ';
            }
            if ($comorbids['obesity']) {
                $text .= 'ภาวะอ้วน ';
                if ($visit->form['patient']['weight'] && $visit->form['patient']['height']) {
                    $bmi = number_format(((float) $visit->form['patient']['weight']) /
                        ((float) $visit->form['patient']['height']) /
                        ((float) $visit->form['patient']['height']) *
                        10000, 2);
                    $text .= "(W {$visit->form['patient']['weight']}/H {$visit->form['patient']['height']}/BMI {$bmi}) ";
                } elseif ($visit->form['patient']['weight'] || $visit->form['patient']['height']) {
                    if ($visit->form['patient']['weight']) {
                        $text .= "(W{$visit->form['patient']['weight']}) ";
                    } else {
                        $text .= "(H{$visit->form['patient']['height']}) ";
                    }
                }
            }
            $text .= $comorbids['other_comorbids'];
            $comorbids = $text;
        }

        // vaccination
        $vaccination = $visit->form['vaccination'];
        if ($vaccination['unvaccinated']) {
            $vaccination = 'ไม่เคยฉีด';
        } else {
            $vaccines = $this->getConfigs($visit)['vaccines'];
            $text = '';
            foreach ($vaccines as $vaccine) {
                if ($vaccination[$vaccine] ?? false) {
                    $text .= " {$vaccine},";
                }
            }
            $text = trim(trim($text, ','));
            $text = 'เคยฉีด '.$text;
            if ($vaccination['doses']) {
                $text .= ' รวม '.$vaccination['doses'].' เข็ม';
            }
            if ($vaccination['date_latest_vacciniated']) {
                $text .= ' เมื่อ '.Carbon::create($vaccination['date_latest_vacciniated'])->format('d M Y');
            }
            $vaccination = $text;
        }

        // diagnosis
        $diagnosis = $visit->form['diagnosis'];
        if ($diagnosis['no_symptom']) {
            $diagnosis = 'ไม่มีอาการ';
        } elseif ($diagnosis['public_patient_walkin_diagnosis'] ?? null) {
            $diagnosis = $diagnosis['public_patient_walkin_diagnosis'];
        } else {
            $text = '';
            if ($diagnosis['suspected_covid_19']) {
                $text .= 'Suspected COVID-19 infection<br>';
            }
            if ($diagnosis['uri']) {
                $text .= 'Upper respiratory tract infection (URI)<br>';
            }
            if ($diagnosis['suspected_pneumonia']) {
                $text .= 'Suspected pneumonia<br>';
            }
            $text .= $diagnosis['other_diagnosis'];
            $diagnosis = $text;
        }

        // management
        $management = $visit->form['management'];
        $specimenNo = null;
        if ($management['np_swab'] && $management['specimen_no']) {
            $specimenNo = $management['specimen_no'];
        }
        $text = null;
        if ($management['np_swab']) {
            $text .= 'NP swab for PCR test of SARS-CoV-2';
            if (! $visit->swabbed && $visit->discharged_at) {
                $text .= ' ** ไม่ได้ทำ swab';
            }
        }
        if ($management['other_tests']) {
            $text .= (', '.$management['other_tests']);
        }
        $text = $text ? trim($text) : null;
        if ($management['home_medication']) {
            if ($text) {
                $text .= '<p class="underline">home medication</p>';
            }
            $lines = explode("\n", $management['home_medication']);
            if (count($lines)) {
                $text .= collect($lines)->map(function ($line) {
                    return "<p>{$line}</p>";
                })->join('');
            }
        } elseif (($management['atk_positive_without_pcr_medication'] ?? null) || ($management['atk_positive_without_pcr_medication_other'] ?? null)) {
            if ($text) {
                $text .= '<p class="underline">home medication</p>';
            } else {
                $text = '<p>home medication</p>';
            }
            $meds = ($management['atk_positive_without_pcr_medication'] ?? null) ?? ($management['atk_positive_without_pcr_medication_other'] ?? null);
            $lines = explode("\n", $meds);
            if (count($lines)) {
                $text .= collect($lines)->map(function ($line) {
                    return "<p>{$line}</p>";
                })->join('');
            }
        }
        $management = $text;

        // recommendation
        $recommendation = $visit->form['recommendation'];
        if ($recommendation['choice'] && ! $visit->form['management']['np_swab']) {
            if ($visit->date_visit->lessThan(Carbon::create('2022-01-25'))) { // CR affected on this date
                $choices = collect([
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
                ]);
            } else { // CR 20220124
                $choices = collect([
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
                        'label' => 'ลางาน กักตัวเองที่บ้าน ห้ามพบปะผู้อื่นจนครบ 10 วัน',
                    ],
                ]);
            }
            // $choices = collect($this->getConfigs($visit)['public_recommendations']);
            $text = $choices->firstWhere('value', $recommendation['choice'])['label'];
            if ($recommendation['date_isolation_end']) {
                $text .= ('<br>กักตัวถึงวันที่ - '.Carbon::create($recommendation['date_isolation_end'])->format('d M Y'));
            }
            if ($recommendation['date_reswab']) {
                $text .= ('<br>นัดทำ swab - '.Carbon::create($recommendation['date_reswab'])->format('d M Y'));
            }
            if ($recommendation['date_reswab_next']) {
                $text .= ('<br>นัดทำ reswab ครั้งที่ 2 - '.Carbon::create($recommendation['date_reswab_next'])->format('d M Y'));
            }
            $recommendation = $text;
        } elseif (($visit->form['management']['manage_atk_positive'] ?? null) && str_starts_with($visit->form['management']['manage_atk_positive'], 'ไม่ต้องการยืนยันผลด้วยวิธี PCR')) {
            $recommendation = $this->getConfigs($visit)['atk_positive_without_pcr_recommendation'];
        } else {
            $recommendation = null;
        }

        // note
        $note = $visit->form['note'];
        $lines = explode("\n", $note);
        if (count($lines) > 1) {
            $note = collect($lines)->map(function ($line) {
                return "<p>{$line}</p>";
            })->join('');
        }

        return [
            'slug' => $visit->slug,
            'visit' => $contentVisit,
            'symptom_headers' => $symptomHeaders,
            'symptoms' => $symptoms,
            'ATK' => $atkPositive,
            'ประวัติเสี่ยง' => $exposure,
            'ประวัติอื่น' => $otherConditions,
            'โรคประจำตัว' => $comorbids,
            'ประวัติการฉีดวัคซีน COVID-19' => $vaccination,
            'วินิจฉัย' => $diagnosis,
            'การจัดการ' => $management,
            'คำแนะนำสำหรับผู้ป่วย' => $recommendation,
            'note' => $note,
            'evaluation' => $visit->form['evaluation'] ?? [],
            'specimen_no' => $specimenNo,
        ];
    }

    public function getPrintConent(Visit $visit)
    {
        $content = $this->getReportContent($visit);
        if ($visit->patient->profile['document_id']) {
            $content['visit']['เลขประจำตัวประชาชน'] = $visit->patient->profile['document_id'];
        } else {
            $content['visit']['เลขหนังสือเดินทาง'] = $visit->form['patient']['passport_no'] ?? null;
        }
        $content['อาการแสดง'] = '';
        foreach ($content['symptom_headers'] as $key => $value) {
            if ($key !== 'วันแรกที่มีอาการ') {
                $content['อาการแสดง'] .= " {$key} {$value},";
            }
        }
        $content['อาการแสดง'] = trim($content['อาการแสดง'], ',');
        if ($content['symptom_headers']['วันแรกที่มีอาการ'] ?? false) {
            $content['อาการแสดง'] .= '<br>วันแรกที่มีอาการ '.$content['symptom_headers']['วันแรกที่มีอาการ'];
        }
        $content['อาการแสดง'] = trim($content['อาการแสดง'].'<br>'.$content['symptoms']);
        if (!isset($visit->form['md'])) {
            \Log::error($visit->slug);
        }
        $content['md'] = $visit->form['md'];
        $content['md']['signed_at'] = Carbon::create($visit->form['md']['signed_at'])->tz('asia/bangkok')->format('d M Y H:i');
        $content['t_barcode'] = 'T'.$content['visit']['hn'].'$'.($visit->date_visit->year + 543).$visit->date_visit->format('md').'$1403$';

        return $content;
    }
}
