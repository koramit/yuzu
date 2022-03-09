<?php

namespace App\Managers;

use App\Models\Visit;
use App\Traits\MedicalCertifiable;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiITManager
{
    use MedicalCertifiable;

    protected $daysCriteria;

    public function manage(Visit $visit)
    {
        $url = config('services.siit.export_case_endpoint');
        if (!$url) {
            return false;
        }

        $data = $this->formatData($visit);
        $siitLog = Cache::get('siit-log', []);
        $today = now()->tz(7)->format('Y-m-d');
        if (!isset($siitLog[$today])) {
            $siitLog[$today] = [
                'send' => 0,
                'accept' => 0,
                'duplicate_error' => 0,
                'invalid_symp_code_error' => 0,
                'other_validation_error' => 0,
                'request_error' => 0,
                'response_error' => 0,
            ];
        }
        $siitLog[$today]['send'] = $siitLog[$today]['send'] + 1;
        Cache::put('siit-log', $siitLog);
        try {
            $res = Http::timeout(2)
                        ->retry(5, 100)
                        ->post($url, $data)
                        ->json();
        } catch (Exception $e) {
            Log::error('SiIT_EXPORT_REQUEST@'.$visit->slug.'@'.$e->getMessage());
            $siitLog[$today]['request_error'] = $siitLog[$today]['request_error'] + 1;
            Cache::put('siit-log', $siitLog);

            return false;
        }
        if ($res['messageStatus'] === 'Sccuess.') { // not typo, this is actually return value
            $siitLog[$today]['accept'] = $siitLog[$today]['accept'] + 1;
            Cache::put('siit-log', $siitLog);

            return true;
        }

        $resJson = json_decode($res['messageDescription'], true);
        if ($resJson) {
            if (!empty($resJson['dupplicated_hn_visit_date'])) {
                $siitLog[$today]['duplicate_error'] = $siitLog[$today]['duplicate_error'] + 1;
            } elseif (!empty($resJson['invalid_symp_code'])) {
                $siitLog[$today]['invalid_symp_code_error'] = $siitLog[$today]['invalid_symp_code_error'] + 1;
            } elseif (!empty($resJson['no_updated'])) {
                $siitLog[$today]['no_updated_error'] = ($siitLog[$today]['no_updated_error'] ?? 0) + 1;
            } else {
                Log::error('SiIT_OTHER_ERROR@'.$visit->slug.'@'.$res['messageDescription']);
                $siitLog[$today]['other_validation_error'] = $siitLog[$today]['other_validation_error'] + 1;
            }
            Cache::put('siit-log', $siitLog);

            return true;
        }

        Log::error('SiIT_EXPORT_RESPONSE@'.$visit->slug.'@'.$res['messageDescription']);
        $siitLog[$today]['response_error'] = $siitLog[$today]['response_error'] + 1;
        Cache::put('siit-log', $siitLog);

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

    public function manageCertificate(Visit $visit, array $md)
    {
        $tx = $visit->form['management']['np_swab_result_transaction'] ?? null;
        $cert = $visit->form['evaluation'];
        $form = [
            'hn' => $visit->hn,
            'lab_no' => $visit->atk_positive_case ? '' : ($tx ? $tx['lab_no'] : ''),
            'atk_result' => $visit->atk_positive_case ? 'Detected' : '',
            'doctor_code' => $visit->atk_positive_case ? $md['md_pln'] : ($cert['md_pln'] ?? $md['md_pln']),
            'doctor_name' => $visit->atk_positive_case ? $md['md_name'] : ($cert['md_name'] ?? $md['md_name']),
            'doctor_comments' => [
                ['seq' => 1, 'comments' => $this->getRecommendation($cert['recommendation'] ?? null)],
            ]
        ];
        $dateQuarantineEnd = $this->getThaiDate($cert['date_quarantine_end'] ?? null);
        if ($dateQuarantineEnd) {
            $form['doctor_comments'][] = ['seq' => 2, 'comments' => $dateQuarantineEnd];
            $dateReswab = $this->getThaiDate($cert['date_reswab'] ?? null);
            if ($dateReswab) {
                $form['doctor_comments'][] = ['seq' => 3, 'comments' => $dateReswab];
            }
        }

        $log = Cache::get('siit-cert-log', []);
        $today = $visit->date_visit->format('Y-m-d');
        $log[$today]['send_count'] = ($log[$today]['send_count'] ?? 0) + 1;
        try {
            $res = Http::withHeaders(['token' => config('services.siit.export_certificate_token')])
                        ->timeout(2)
                        ->retry(5, 100)
                        ->post(config('services.siit.export_certificate_endpoint'), $form)
                        ->json();
        } catch (Exception $e) {
            $log[$today]['error_count'] = ($log[$today]['error_count'] ?? 0) + 1;
            $message = preg_replace('/\d{8}/', '********', str_replace("\n", '', $e->getMessage()));
            if (! collect($log[$today]['error_log'] ?? [])->contains($message)) {
                $log[$today]['error_log'][] = $message;
            }
            Cache::put('siit-cert-log', $log);

            return false;
        }

        if ($res['messageCode'] === '0') {
            $log[$today]['success_count'] = ($log[$today]['success_count'] ?? 0) + 1;
            Cache::put('siit-cert-log', $log);
            $visit->forceFill(['form->scc_cert_sent_hash' => $visit->scc_cert_sent_hash])->save();

            return true;
        }

        $log[$today]['failed_count'] = ($log[$today]['failed_count'] ?? 0) + 1;
        $message = preg_replace('/\d{8}/', '********', str_replace("\n", '', $res['messageStatus']));
        if (! collect($log[$today]['failed_log'] ?? null)->contains($message)) {
            $log[$today]['failed_log'][] = $message;
        }
        Cache::put('siit-cert-log', $log);

        return false;
    }

    public function sentSccCertificates(string $dateVisit)
    {
        $certificates = Visit::where('swabbed', true)
                             ->wherePatientType(1)
                             ->whereDateVisit($dateVisit)
                             ->whereNotNull('form->evaluation->recommendation')
                             ->whereNotNull('form->management->np_swab_result_transaction->lab_no')
                             ->withPublicPatientWalkinATKPosWithoutPCR($dateVisit)
                             ->get();

        $md = $certificates->filter(fn ($cert) => $cert->form['evaluation']['md_name'] ?? null)->first();
        if (!$md) {
            return;
        }
        $md = [
            'md_name' => $md->form['evaluation']['md_name'],
            'md_pln' => $md->form['evaluation']['md_pln'],
        ];

        $this->daysCriteria = Carbon::create($dateVisit)->lessThan(Carbon::create('2022-01-24')) ? 14 : 10;

        foreach ($certificates as $cert) {
            if (($cert->form['scc_cert_sent_hash'] ?? null) && $cert->form['scc_cert_sent_hash'] === $cert->scc_cert_sent_hash) {
                continue;
            }
            $this->manageCertificate($cert, $md);
        }
    }
}
