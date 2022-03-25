<?php
namespace App;

use App\Managers\MOPHVaccinationManager;
use App\Models\Patient;

class GenVaccine
{
    public static function run($start, $stop)
    {
        $m = new MOPHVaccinationManager;

        $vaccine = [];
        for ($i = $start; $i <= $stop; $i++) {
            $patient = Patient::find($i);
            $vaccinations = $m->manage($patient->profile['document_id']);
            if (!$vaccinations) {
                continue;
            }

            foreach ($vaccinations as $vac) {
                $vaccine[] = [
                    'mid' => $vac['manufacturer_id'],
                    'name' => $vac['vaccine'],
                ];
            }
        }

        return $vaccine;
    }
}
