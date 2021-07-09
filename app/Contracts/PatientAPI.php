<?php

namespace App\Contracts;

interface PatientAPI
{
    public function getPatient($hn);

    public function getAdmission($an);

    public function recentlyAdmission($hn);
}
