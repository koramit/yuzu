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
            $cid = $patient->profile['document_id'];
            echo "pid: {$patient->id} => cid: {$cid} ; time: ";
            $startAt = now();
            if ($cid) {
                continue;
            }
            $vaccinations = $m->manage($cid);
            $callTime = now()->diffInSeconds($startAt);
            echo "{$callTime}\n";
            if (!$vaccinations) {
                continue;
            }

            foreach ($vaccinations as $vac) {
                $vaccine[] = [
                    'mid' => $vac['manufacturer_id'],
                    'name' => $vac['vaccine'],
                    'time' => $callTime,
                ];
            }
        }

        return $vaccine;
    }
}
