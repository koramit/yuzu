<?php

namespace App\APIs;

use Exception;
use Illuminate\Support\Facades\Http;

class SiMOPHVaccinationAPI
{
    public function getVaccination(int $pid)
    {
        try {
            $result = Http::asJson()
                        ->timeout(2)
                        ->retry(5, 100)
                        ->get(config('services.sivaccination_api_url'), ['cid' => $pid])
                        ->json();
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }
}
