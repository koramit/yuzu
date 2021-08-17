<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class ImportAppointmentsController extends Controller
{
    public function __invoke()
    {
        $path = Request::file('file')->store('temp');

        $appointments = FastExcel::import(storage_path('app/'.$path))->transform(function ($case) {
            return [
                'patient_name' => trim($case['ชื่อ'].' '.$case['นามสกุล']),
                'hn' => str_replace('-', '', $case['HN']),
                'tel_no' => str_replace('-', '', $case['เบอร์มือถือ']),
                'date_latest_expose' => $case["วันที่สัมผัสใกล้ชิด\nกับผู้ติดเชื้อ"],
                'risk' => $case['ระดับความเสี่ยงที่ประเมินได้'],
                'date_latest_vacciniated' => $case["วันที่ฉีดวัคซีน\nเข็มล่าสุด"],
                'vaccine' => $case["ประวัติการรับวัคซีน COVID-19\n(AZ = AstraZeneca, SV = Sinovac)"],
            ];
        });

        $user = Auth::user();
        $tomorrowStr = now('asia/bangkok')->addDay()->format('Y-m-d');
        $hnNotFoundCount = 0;
        $noHnCount = 0;
        $appointedCount = 0;
        $createdCount = 0;
        $patientManager = new PatientManager();
        $visitManger = new VisitManager();
        foreach ($appointments as $appointment) {
            if (! $appointment['patient_name'] && ! $appointment['hn']) {
                continue;
            }

            $patient = null;
            if ($appointment['hn']) {
                $patient = $patientManager->manage($appointment['hn']);
                if (! $patient['found']) {
                    $appointment['hn'] = null;
                    $hnNotFoundCount++;
                }
            } else {
                $noHnCount++;
            }

            $form = $visitManger->initForm();
            if ($appointment['hn']) {
                $visit = Visit::whereDateVisit($tomorrowStr)
                            ->wherePatientId($patient['patient']->id)
                            ->first();
                if ($visit) {
                    $appointedCount++;
                    continue;
                }
                $visit = new Visit();
                $visit->patient_id = $patient['patient']->id;
                $form['patient']['track'] = 'นัด-staff';
                $form['patient']['hn'] = $patient['patient']->hn;
                $form['patient']['name'] = $patient['patient']->full_name;
                $form['patient']['tel_no'] = $appointment['tel_no'];
            } else {
                $visit = Visit::whereDateVisit($tomorrowStr)
                            ->where('form->patient->name', $appointment['patient_name'])
                            ->first();
                if ($visit) {
                    $appointedCount++;
                    continue;
                }
                $visit = new Visit();
                $form['patient']['name'] = $appointment['patient_name'];
            }

            $visit->slug = Str::uuid()->toString();
            $visit->date_visit = $tomorrowStr;
            $visit->patient_type = 'เจ้าหน้าที่ศิริราช';
            $visit->screen_type = 'นัดมา swab ครั้งแรก';
            $form['patient']['risk'] = $this->getRisk($appointment['risk']);
            $form['patient']['date_latest_expose_by_im'] = $this->getDateStr($appointment['date_latest_expose']);
            $form['vaccination'] = $this->getVaccine($appointment);
            $form['management']['np_swab'] = true;
            $visit->form = $form;
            $visit->status = 'appointment';
            $visit->enlisted_screen_at = now();
            $visit->save();
            $visit->actions()->create(['action' => 'create', 'user_id' => $user->id]);
            $createdCount++;
        }

        LoadDataRecord::create([
            'export' => false,
            'configs' => ['data' => 'appointments'],
            'user_id' => $user->id,
        ]);

        Storage::delete($path);

        return Redirect::route('visits.screen-list')->with('messages', [
            'status' => 'success',
            'messages' => [
                'นำเข้าข้อมูลนัดหมายสำเร็จ',
                "สร้างใหม่ {$createdCount} เคส",
                "มีอยู่แล้ว {$appointedCount} เคส",
                "ไม่มี HN {$noHnCount} เคส",
                "HN ไม่อยู่ในระบบ {$hnNotFoundCount} เคส",
            ],
        ]);
    }

    protected function getRisk($value)
    {
        if (! $value) {
            return null;
        } elseif ($value === 'ตรวจสอบ') {
            return null;
        } elseif ($value === 'ไม่เสี่ยง') {
            return 'ไม่มีความเสี่ยง';
        } else {
            return 'ความเสี่ยง'.$value;
        }
    }

    protected function getDateStr($value)
    {
        if (! $value) {
            return null;
        }

        if (gettype($value) === 'object') {
            return $value->format('Y-m-d');
        }

        if (gettype($value) === 'integer') {
            $unixDate = ($value - 25569) * 86400; // from excel date

            return date('Y-m-d', $unixDate);
        }

        return null;
    }

    protected function getVaccine($data)
    {
        $vaccine = [
            'unvaccinated' => false,
            'Sinovac' => false,
            'Sinopharm' => false,
            'AstraZeneca' => false,
            'Moderna' => false,
            'Pfizer' => false,
            'doses' => null,
            'date_latest_vacciniated' => null,
        ];

        if (! $data['vaccine']) {
            return $vaccine;
        }

        if (Str::contains($data['vaccine'], 'ยังไม่เคย')) {
            $vaccine['unvaccinated'] = true;

            return $vaccine;
        }

        $vaccine['date_latest_vacciniated'] = $this->getDateStr($data['date_latest_vacciniated']);

        if (Str::contains(Str::lower($data['vaccine']), 'sinovac')) {
            $vaccine['Sinovac'] = true;
        }

        if (Str::contains(Str::lower($data['vaccine']), 'astra')) {
            $vaccine['AstraZeneca'] = true;
        }

        if (Str::contains($data['vaccine'], '1')) {
            $vaccine['doses'] = 1;

            return $vaccine;
        }

        if (Str::contains($data['vaccine'], '2')) {
            $vaccine['doses'] = 2;
        }

        if (Str::contains($data['vaccine'], '+')) {
            $vaccine['doses'] = 3;
        }

        return $vaccine;
    }
}
