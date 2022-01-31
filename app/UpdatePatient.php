<?php

namespace App;

class UpdatePatient
{
    public static function run()
    {
        $errors = [];
        $manager = new \App\Managers\PatientManager;
        foreach (\App\Models\Patient::whereBetween('id', [1, 15413])->orderByDesc('id')->lazy() as $patient) {
            try {
                $manager->manage($patient->hn, true);
            } catch (\Exception $e) {
                $errors[] = $patient->id;
            }
            if ($patient->id % 1000 === 0) {
                echo $patient->id."\n";
            }
        }
        return $errors;
    }
}
