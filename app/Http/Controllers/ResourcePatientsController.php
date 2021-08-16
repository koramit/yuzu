<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use Illuminate\Support\Facades\Session;

class ResourcePatientsController extends Controller
{
    public function __invoke($hn)
    {
        $patient = (new PatientManager())->manage($hn);
        if (! $patient['found']) {
            return $patient;
        }
        Session::put('last-search-hn', $hn);

        return [
            'found' => true,
            'hn' => $patient['patient']->hn,
            'full_name' => $patient['patient']->full_name,
            'dob' => $patient['patient']->dob,
            'gender' => $patient['patient']->gender,
            'tel_no' => $patient['patient']->profile['tel_no'],
        ];
    }
}
