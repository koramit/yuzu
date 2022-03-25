<?php

namespace App\Managers;

use App\APIs\SiMOPHVaccinationAPI;

class MOPHVaccinationManager
{
    public function manage($cid)
    {
        $brands = [
            1 => 'AstraZeneca',
            5 => 'Moderna',
            6 => 'Pfizer',
            7 => 'Sinovac',
            8 => 'Sinopharm'
        ];

        $result = (new SiMOPHVaccinationAPI)->getVaccination($cid);


        if (!$result['ok']) {
            // error
            return false;
        }

        if (!$result['found']) {
            // most likely invalid cid
            return false;
        }

        $result = $result['data'];

        if (($result['vaccine_history_count'] ?? null) === 0) {
            // unvac
            return 0;
        }

        $vaccinations = [];
        foreach ($result['vaccine_history'] as $vac) {
            $vaccinations[] = [
                'brand' => $brands[$vac['vaccine_manufacturer_id']],
                'label' => $vac['vaccine_name'],
                'date' => explode('T', $vac['immunization_datetime'])[0],
                'place' => $vac['hospital_name'],
                'manufacturer_id' => $vac['vaccine_manufacturer_id'],
            ];
        }

        return $vaccinations;
    }
}
