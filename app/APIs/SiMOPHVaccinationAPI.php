<?php

namespace App\APIs;

use Exception;
use Illuminate\Support\Facades\Http;

class SiMOPHVaccinationAPI
{
    public function getVaccination($cid)
    {
        try {
            $result = Http::asJson()
                        ->timeout(30)
                        ->retry(2, 100)
                        ->post(config('services.sivaccination_api_url'), ['cid' => $cid])
                        ->json();
        } catch (Exception $e) {
            return ['ok' => false, 'serverError' => false];
        }

        if (!isset($result['MessageCode'])) {
            return ['ok' => false, 'serverError' => true];
        }

        if ($result['MessageCode'] !== 200) {
            return ['ok' => true, 'found' => false, 'message' => $result['Message']];
        }

        return [
            'ok' => true,
            'found' => true,
            'data' => $result['result'],
        ];
    }
}
