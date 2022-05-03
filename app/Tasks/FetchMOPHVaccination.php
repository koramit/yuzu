<?php

namespace App\Tasks;

class FetchMOPHVaccination
{
    public static function run()
    {
        // implement transform data method
        // implement insert patient_vaccinations

        // query today visits those has no patient_vaccinations relationship update today

        // foreach visit
            // fetch MOPH vaccinations
            // insert new vaccination only, maybe distinct by serial_no
    }
}
