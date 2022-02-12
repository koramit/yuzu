<?php

namespace App\Managers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TotoMedicineManager
{
    public function manage(string $dateReff)
    {
        $all = $this->fetch(dateReff: $dateReff);
        if (!$all) {
            return $all;
        }

        $ari = $all->filter(fn ($p) => $p['source'] === 'ARI');
        return [
            'count' => $ari->count(),
            'detected' => $ari->filter(fn ($p) => $p['result'] === 'Detected')->count(),
            'not_detected' => $ari->filter(fn ($p) => $p['result'] === 'Not Detected')->count(),
            'pending' => $ari->filter(fn ($p) => $p['result'] === 'Pending')->count(),
        ];
    }
    public function fetch(string $dateReff)
    {
        // ['Detected (PCR)', 'Not Detected (PCR)', 'Inconclusive (PCR)', 'รอผลตรวจ']
        try {
            $dateStr = now()->create($dateReff)->addYears(543)->format('d/m/Y');
            $data = [
                'hash' => config('services.toto.token'),
                'method' => config('services.toto.method'),
                'start_date' => $dateStr,
                'end_date' => $dateStr,
            ];
            $res = Http::timeout(4)
                        ->retry(5, 100)
                        ->asForm()
                        ->post(config('services.toto.url'), $data)
                        ->json();

            return collect($res['cargo'])->transform(fn ($p) => [
                'hn' => $p['hn'],
                'name' => $p['patient_name'],
                'gender' => $p['sex'],
                'age' => $p['age'],
                'source' => Str::of($p['case1'])->replace('AR', 'ARI')->__toString(),
                'result' => Str::of($p['case2'])->replace(' (PCR)', '')->replace('รอผลตรวจ', 'Pending')->__toString(),
            ]);
        } catch (Exception $e) {
            Log::error('toto_medicine_error@'.$e->getMessage());

            return false;
        }
    }
}
