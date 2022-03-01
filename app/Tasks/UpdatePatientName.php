<?php

namespace App\Tasks;

use App\Managers\PatientManager;
use App\Models\Visit;

class UpdatePatientName
{
    public static function run()
    {
        $manager = new PatientManager();
        Visit::whereDateVisit(now()->format('Y-m-d'))
            ->whereStatus(4)
            ->get()
            ->each(function ($visit) use ($manager) {
                $patient = $manager->manage($visit->hn, true);
                if ($patient['found'] && $visit->patient_name !== $patient['patient']->full_name) {
                    $visit->patient_name = $patient['patient']->full_name;
                    $visit->save();
                }
            });
    }
}
