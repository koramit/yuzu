<?php

namespace App\Managers;

use App\APIs\SiMOPHVaccinationAPI;

class MOPHVaccinationManager
{
    public function manage($cid)
    {
        $brands = config('services.vaccine_brands');

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
            return [];
        }

        $vaccinations = [];
        foreach ($result['vaccine_history'] as $vac) {
            $date = explode('T', $vac['immunization_datetime'])[0];
            $vaccinations[] = [
                'brand' => $brands[$vac['vaccine_manufacturer_id']],
                'label' => $vac['vaccine_name'],
                'date' => $date,
                'date_label' => now()->create($date)->format('d/m/Y'),
                'place' => $vac['hospital_name'],
                'manufacturer_id' => $vac['vaccine_manufacturer_id'],
            ];
        }

        return $vaccinations;
    }
}
