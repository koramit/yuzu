<?php

namespace App\Managers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TotoMedicineManager
{
    public function mange(string $dateReff)
    {
        $data = $this->fetch(dateReff: $dateReff);
        if (!$data) {
            return $data;
        }
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
            $res = Http::timeout(2)
                        ->retry(5, 100)
                        ->asForm()
                        ->post(config('services.toto.url'), $data)
                        ->json();

            return collect($res['cargo'])->transform(fn ($p) => [
                'hn' => $p['hn'],
                'name' => $p['patient_name'],
                'gender' => $p['sex'],
                'age' => $p['age'],
                'source' => $p['case1'],
                'result' => $p['case2'],
            ]);
        } catch (Exception $e) {
            Log::error('toto_medicine_error@'.$e->getMessage());

            return false;
        }
    }
}
