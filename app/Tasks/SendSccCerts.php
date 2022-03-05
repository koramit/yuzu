<?php

namespace App\Tasks;

use App\Managers\SiITManager;
use Illuminate\Support\Facades\Cache;

class SendSccCerts
{
    public static function run()
    {
        $dateVisit = Cache::pull('send-scc-certs-job');
        if (! $dateVisit) {
            return;
        }

        (new SiITManager)->sentSccCertificates(dateVisit: $dateVisit);
    }
}
