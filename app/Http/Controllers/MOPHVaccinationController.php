<?php

namespace App\Http\Controllers;

use App\Managers\MOPHVaccinationManager;

class MOPHVaccinationController extends Controller
{
    public function show($cid)
    {
        $vaccinations = (new MOPHVaccinationManager)->manage($cid);

        if ($vaccinations === false) {
            return [
                'ok' => false
            ];
        }

        return [
            'ok' => true,
            'vaccinations' => $vaccinations
        ];
    }
}
