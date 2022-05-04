<?php

namespace App\Tasks;

use App\Managers\MOPHVaccinationManager;
use App\Models\Patient;
use App\Models\PatientVaccination;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

            $doses = $visit->patient->vaccinations()->select('dose_no')->pluck('dose_no')->all();
            $vacs = collect($vacs)->whereNotIn('vaccine_plan_no', $doses);

            $data = [];
            foreach ($vacs as $vac) {
                try {
                    $year = (int) explode('-', explode("T", $vac['immunization_datetime'])[0])[0];
                    if ($year > 2500) {
                        $vac['immunization_datetime'] = str_replace($year, $year - 543, $vac['immunization_datetime']);
                    } elseif ($year < 2000) {
                        $vac['immunization_datetime'] = str_replace($year, $year + 543, $vac['immunization_datetime']);
                    }
                    $year = (int) explode('-', explode("T", $vac['expiration_date'])[0])[0];
                    if ($year > 2500) {
                        $vac['expiration_date'] = str_replace($year, $year - 543, $vac['expiration_date']);
                    } elseif ($year < 2000) {
                        $vac['expiration_date'] = str_replace($year, $year + 543, $vac['expiration_date']);
                    }
                } catch (\Exception $e) {
                    Log::error($visit->patient_id."\n".$e->getMessage());
                    continue;
                }

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

            try {
                $visit->patient->vaccinations()->createMany($data);
            } catch (\Exception $e) {
                Log::error($visit->patient_id."\n".$e->getMessage());
                continue;
            }
            $patientsId[] = $visit->patient_id;
            Cache::put($keyName, $patientsId, now()->addHours(2));
        }
    }

    public static function gen()
    {
        $patients = Patient::whereDoesntHave('vaccinations')
                            ->orderBy('updated_at')
                            ->limit(450)
                            ->get();

        $manager = new MOPHVaccinationManager();

        $insertCount = 0;
        foreach ($patients as $patient) {
            $patient->touch();
            if (!$patient->profile['document_id']) {
                continue;
            }

            $vacs = $manager->manage($patient->profile['document_id'], true);

            if ($vacs === []) {
                continue;
            }

            if ($vacs === false) {
                continue;
            }

            $vacs = collect($vacs);

            $data = [];
            foreach ($vacs as $vac) {
                try {
                    $year = (int) explode('-', explode("T", $vac['immunization_datetime'])[0])[0];
                    if ($year > 2500) {
                        $vac['immunization_datetime'] = str_replace($year, $year - 543, $vac['immunization_datetime']);
                    } elseif ($year < 2000) {
                        $vac['immunization_datetime'] = str_replace($year, $year + 543, $vac['immunization_datetime']);
                    }
                    $year = (int) explode('-', explode("T", $vac['expiration_date'])[0])[0];
                    if ($year > 2500) {
                        $vac['expiration_date'] = str_replace($year, $year - 543, $vac['expiration_date']);
                    } elseif ($year < 2000) {
                        $vac['expiration_date'] = str_replace($year, $year + 543, $vac['expiration_date']);
                    }
                } catch (\Exception $e) {
                    Log::error($patient->id."\n".$e->getMessage());
                    continue;
                }

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

            try {
                $patient->vaccinations()->createMany($data);
                $insertCount = $insertCount + 1;
            } catch (\Exception $e) {
                Log::error($patient->id."\n".$e->getMessage());
                continue;
            }
        }

        if ($insertCount === 0) {
            Log::notice('no vac insert');
        }
    }
}
