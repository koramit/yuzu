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
                        ->whereNotNull('patient_id')
                        ->whereNotIn('patient_id', $patientsId)
                        ->get();

        $manager = new MOPHVaccinationManager();
        $brands = config('services.vaccine_brands');

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
                    'vaccinated_at' => now()->parse($data['immunization_datetime'])->addHours(-7),
                    'brand_id' => $brands[$data['vaccine_manufacturer_id']],
                    'label' => $data['vaccine_name'],
                    'dose_no' => $data['vaccine_plan_no'],
                    'lot_no' => $data['lot_number'],
                    'serial_no' => $data['serial_no'],
                    'expired_at' => now()->parse($data['expiration_date'])->tz(0),
                    'hospital_code' => $data['hospital_code'],
                    'hospital_name' => $data['hospital_name'],
                    'hospital_province' => $data['province_name'],
                ];
            }

            $visit->patient->vaccinations()->createMany($data);
            $patientsId[] = $visit->patient_id;
            Cache::put($keyName, $patientsId, now()->addHours(2));
        }
    }
}
