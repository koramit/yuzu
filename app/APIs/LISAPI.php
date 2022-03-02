<?php

namespace App\APIs;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class LISAPI
{
    protected $token;

    public function __construct()
    {
        try {
            $this->token = Http::asForm()
                            ->timeout(2)
                            ->retry(5, 100)
                            ->post(config('services.lisapi.auth_url'), [
                                'client_id' => config('services.lisapi.id'),
                                'client_secret' => config('services.lisapi.secret'),
                                'grant_type' => 'client_credentials'
                            ])->json();
        } catch (Exception $e) {
            $this->token === false;
        }
    }

    public function getCovidLabs(string $hn, string $dateLab)
    {
        $form = [
            'HN' => $hn,
            'GROUP' => true,
            'GROUP_SERVICE_ID' => ['204592', '204593'],
            'START_DATE' => $dateLab,
            'END_DATE' => Carbon::create($dateLab)->addDay()->format('Y-m-d')
        ];

        try {
            $result = Http::withToken($this->token)
                        ->timeout(2)
                        ->retry(5, 100)
                        ->post(config('services.lisapi.service_url'), $form)
                        ->json();
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }
}
