<?php

namespace App\Http\Controllers;

use App\Managers\MOPHVaccinationManager;

class MOPHVaccinationController extends Controller
{
    public function show($cid)
    {
        // return [
        //     'ok' => true,
        //     'vaccinations' => [
        //         [
        //         "brand" => "Sinovac",
        //         "label" => "Covid-19 Vaccine - Sinovac",
        //         "date" => "2021-04-27",
        //         "date_label" => "27/04/2021",
        //         "place" => "โรงพยาบาลศิริราช",
        //         "manufacturer_id" => 7,
        //         ],
        //         [
        //         "brand" => "Sinovac",
        //         "label" => "Covid-19 Vaccine - Sinovac",
        //         "date" => "2021-05-19",
        //         "date_label" => "19/05/2021",
        //         "place" => "โรงพยาบาลศิริราช",
        //         "manufacturer_id" => 7,
        //         ],
        //         [
        //         "brand" => "Pfizer",
        //         "label" => "Covid-19 Vaccine - Pfizer",
        //         "date" => "2021-08-13",
        //         "date_label" => "13/08/2021",
        //         "place" => "โรงพยาบาลศิริราช",
        //         "manufacturer_id" => 6,
        //         ],
        //         [
        //         "brand" => "Pfizer",
        //         "label" => "Covid-19 Vaccine - Pfizer",
        //         "date" => "2022-01-13",
        //         "date_label" => "13/01/2022",
        //         "place" => "โรงพยาบาลศิริราช",
        //         "manufacturer_id" => 6,
        //         ],
        //     ]
        // ];

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
