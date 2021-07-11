<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;

class ResourcePatientsController extends Controller
{
    public function __invoke($hn)
    {
        $patient = (new PatientManager())->manage($hn);
        if (! $patient['found']) {
            return $patient;
        }

        return [
            'found' => true,
            'hn' => $patient['patient']->hn,
            'full_name' => $patient['patient']->full_name,
            'dob' => $patient['patient']->dob,
            'gender' => $patient['patient']->gender,
        ];
    }
}
