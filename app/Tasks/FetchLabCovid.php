<?php

namespace App\Tasks;

use App\Events\LabReported;
use App\Events\VisitUpdated;
use App\Managers\LabCovidManager;
use Illuminate\Support\Facades\Cache;

class FetchLabCovid
{
    public static function run()
    {
        $hours = collect([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]);

        if (! $hours->contains(now()->hour)) {
            return;
        }

        $manager = new LabCovidManager;
        $count = $manager->fetchPCR(today()->format('Y-m-d'));
        if ($count === false) {
            return; // lab complete for today
        }

        // notify if too many error
        // notify if too many no lab

        $logs = Cache::get('lis-api-logs', collect([]));
        $logs->push([
            'timestamp' => now(),
            'count' => $count
        ]);
        Cache::put(key: 'lis-api-logs', value: $logs, ttl: now()->addHours(7));
    }
}
