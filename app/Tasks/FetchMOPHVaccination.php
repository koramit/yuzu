<?php

namespace App\Tasks;

use App\Managers\MOPHVaccinationManager;
use App\Models\PatientVaccination;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;

class FetchMOPHVaccination
{
    public static function run()
    {
        $keyName = 'today-patient-vaccinations-fetched';
        $patientsId = Cache::get($keyName, []);

        $visits = Visit::with('patient')
                        ->whereDateVisit(today()->format('Y-m-d'))
                        ->whereNotNull('patient_id')
                        ->whereNotIn('patient_id', $patientsId)
                        ->get();

        $manager = new MOPHVaccinationManager();

        foreach ($visits as $visit) {
            if (!$visit->patient->profile['document_id']) {
                $patientsId[] = $visit->patient_id;
                Cache::put($keyName, $patientsId, now()->addHours(2));
                continue;
            }

            $vacs = $manager->manage($visit->patient->profile['document_id'], true);

            if ($vacs === []) {
                $patientsId[] = $visit->patient_id;
                Cache::put($keyName, $patientsId, now()->addHours(2));
                continue;
            }

            if ($vacs === false) {
                continue;
            }

            $serials = $visit->patient->vaccinations()->select('serial_no')->get();
            $vacs = collect($vacs)->whereNotIn('serial_no', $serials);

            $data = [];
            foreach ($vacs as $vac) {
                $data[] = [
                    'vaccinated_at' => now()->parse($vac['immunization_datetime'])->addHours(-7),
                    'brand_id' => $vac['vaccine_manufacturer_id'],
                    'label' => $vac['vaccine_name'],
                    'dose_no' => $vac['vaccine_plan_no'],
                    'lot_no' => $vac['lot_number'],
                    'serial_no' => $vac['serial_no'],
                    'expired_at' => now()->parse($vac['expiration_date'])->tz(0),
                    'hospital_code' => $vac['hospital_code'],
                    'hospital_name' => $vac['hospital_name'],
                    'hospital_province' => $vac['province_name'],
                ];
            }

            $visit->patient->vaccinations()->createMany($data);
            $patientsId[] = $visit->patient_id;
            Cache::put($keyName, $patientsId, now()->addHours(2));
        }
    }
}
