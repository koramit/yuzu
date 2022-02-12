<?php

namespace App\Managers;

use App\Models\Visit;
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

        $scc = $all->filter(fn ($p) => $p['source'] === 'ARI');

        $yuzu = Visit::whereDateVisit($dateReff)
                    ->whereSwabbed(true)
                    ->whereStatus(4)
                    ->get()
                    ->transform(fn ($v) => [
                        'result' => $v->form['management']['np_swab_result'] ?? 'Pending'
                    ]);

        return [
            'scc' => [
                'count' => $scc->count(),
                'detected' => $scc->filter(fn ($p) => $p['result'] === 'Detected')->count(),
                'not_detected' => $scc->filter(fn ($p) => $p['result'] === 'Not Detected')->count(),
                'inconclusive' => $scc->filter(fn ($p) => $p['result'] === 'Inconclusive')->count(),
                'pending' => $scc->filter(fn ($p) => $p['result'] === 'Pending')->count(),
            ],
            'yuzu' => [
                'count' => $yuzu->count(),
                'detected' => $yuzu->filter(fn ($p) => $p['result'] == 'Detected')->count(),
                'not_detected' => $yuzu->filter(fn ($p) => $p['result'] == 'Not detected')->count(),
                'inconclusive' => $yuzu->filter(fn ($p) => $p['result'] == 'Inconclusive')->count(),
                'pending' => $yuzu->filter(fn ($p) => $p['result'] == 'Pending')->count(),
            ]
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

            return collect($res['cargo'] ?? [])->transform(fn ($p) => [
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
