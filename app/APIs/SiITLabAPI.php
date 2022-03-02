<?php

namespace App\APIs;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class SiITLabAPI
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
                            ])->json()['access_token'];
        } catch (Exception $e) {
            $this->token === false;
        }
    }

    public function getLabs(string $hn, string $dateLab, array $labs = ['204592'])
    {
        $form = [
            'HN' => $hn,
            'GROUP' => true,
            'GROUP_SERVICE_ID' => $labs,
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
