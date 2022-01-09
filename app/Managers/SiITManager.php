<?php

namespace App\Managers;

use App\Models\Visit;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiITManager
{
    public function manage(Visit $visit)
    {
        $data = $this->formatData($visit);
        try {
            $res = Http::timeout(2)
                        ->retry(5, 100)
                        ->post(config('services.siit.export_case_endpoint'), $data)
                        ->json();
        } catch (Exception $e) {
            Log::error('SiIT_EXPORT@'.$visit->slug.'@'.$e->getMessage());

            return false;
        }
        if ($res['messageStatus'] === 'Sccuess.') {
            return true;
        }
        Log::error('SiIT_EXPORT@'.$visit->slug.'@'.$res['messageDescription']);

        return false;
    }

    public function formatData($visit)
    {
        $data['hn'] = $visit->form['patient']['hn'];
        $data['weight'] = $visit->form['patient']['weight'] ?? 0;
        $data['gender'] = $visit->patient->gender === 'ชาย' ? 1:2;
        $data['patient_type'] = $visit->patient_type === 'เจ้าหน้าที่ศิริราช' ? 1:2;
        $data['onset'] = $visit->form['symptoms']['date_symptom_start'];
        $data['tel_no'] = $visit->form['patient']['tel_no'];
        $data['date_visit'] = $visit->date_visit->format('Y-m-d H:i:s');
        $data['care_type'] = 'AR';

        $symptoms = ['', 'fever', 'cough', 'sore_throat', 'rhinorrhoea', 'sputum', 'fatigue', 'anosmia', 'loss_of_taste', 'foo', 'myalgia', 'diarrhea'];
        if ($visit->form['symptoms']['asymptomatic_symptom']) {
            $data['symptoms_list'] = [];
        } else {
            foreach ($symptoms as $key => $symptom) {
                if (!($key === 0 || $key === 9) && $visit->form['symptoms'][$symptom]) {
                    $data['symptoms_list'][] = [
                        'symptoms_code' => $key,
                        'other_symstoms_desc' => null,
                    ];
                }
            }

            if ($visit->form['symptoms']['other_symptoms']) {
                $data['symptoms_list'][] = [
                    'symptoms_code' => 999,
                    'other_symstoms_desc' => $visit->form['symptoms']['other_symptoms'],
                ];
            }
        }

        $comorbids = ['', 'dm', 'ht'];
        if ($visit->form['comorbids']['no_comorbids']) {
            $data['ud_list'] = [];
        } else {
            foreach ($comorbids as $key => $comorbid) {
                if ($key !== 0 && $visit->form['comorbids'][$comorbid]) {
                    $data['ud_list'][] = [
                        'u_d_code' => $key,
                        'other_u_d_desc' => null,
                    ];
                }
            }

            foreach (['dlp', 'obesity'] as $comorbid) {
                if ($visit->form['comorbids'][$comorbid]) {
                    $data['ud_list'][] = [
                        'u_d_code' => 999,
                        'other_u_d_desc' => $comorbid,
                    ];
                }
            }

            if ($visit->form['comorbids']['other_symptoms'] ?? null) {
                $data['ud_list'][] = [
                    'u_d_code' => 999,
                    'other_u_d_desc' => $visit->form['comorbids']['other_symptoms'],
                ];
            }
        }
        $dataArray[] = $data;

        return $dataArray;
    }
}
