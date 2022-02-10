<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class CaptainMarvelController extends Controller
{
    public function index()
    {
        // Request::validate([
        //     'token' => [
        //         'required',
        //         function ($attribute, $value, $fail) {
        //             if ($value !== config('app.ww_token')) {
        //                 $fail('The '.$attribute.' is invalid.');
        //             }
        //         },
        //     ],
        // ]);


        $patients = Cache::remember(key: 'today-lab-patients', ttl: now()->addHours(6), callback: function () {
            $todayStr = today()->format('Y-m-d');
            return Visit::with('patient')
                        ->whereDateVisit($todayStr)
                        ->whereStatus(4) // discharged
                        ->where('swabbed', true)
                        // ->whereNull('form->management->np_swab_result')
                        ->orderBy('discharged_at')
                        ->get()
                        ->transform(function ($visit) use ($todayStr) {
                            return [
                                 'finished' => false,
                                 'hn' => $visit->hn,
                                 'slug' => $visit->slug,
                                 'date' => $todayStr,
                                 'result' => null,
                                 'note' => null,
                                 'retry' => 0,
                             ];
                        });
        });

        $patient = $patients->sortBy('retry')->values()->all()[0] ?? null;

        if (!$patient) {
            return [
                'finished' => true
            ];
        }

        $index = $patients->search(fn ($p) => $p['hn'] === $patient['hn']);
        $patient = $patients[$index];
        $patient['retry'] = $patient['retry'] + 1;
        $patients[$index] = $patient;
        Cache::put(key: 'today-lab-patients', value: $patients, ttl: now()->addHours(6));

        return $patient;
    }
}
