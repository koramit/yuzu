<?php

namespace App\Actions;

use Exception;
use Illuminate\Support\Facades\DB;

class VisitLabResultVaccinationStatAction
{
    /**
     * @throws Exception
     */
    public function __invoke($start, $end): array
    {
        return cache()->remember("lab-result-vaccination-$start-$end", now()->addDay(), function () use ($start, $end) {
            cache()->increment('lab-result-vaccination-query-count');
            $labelAvailable = ['ไม่เคยได้รับวัคซีน', '1 เข็ม', '2 เข็ม', '3 เข็ม', '4 เข็ม', '5 เข็ม', '6 เข็ม'];
            $results = ['Detected', 'Not detected', 'Inconclusive'];

            $data = DB::table('visits')
                ->selectRaw('
                    patient_type,
                    lab_result_stored,
                    COUNT(*) as cases,
                    (SELECT COUNT(*)
                        FROM patient_vaccinations
                        WHERE patient_vaccinations.patient_id = visits.patient_id
                            AND patient_vaccinations.vaccinated_at < visits.date_visit
                    ) as vac_count
                ')
                ->where('status', 4)
                ->where('date_visit', '>=', $start)
                ->where('date_visit', '<=', $end)
                ->whereIn('lab_result_stored', $results)
                ->groupBy('vac_count')
                ->groupBy('patient_type')
                ->groupBy('lab_result_stored')
                ->get();

            $uniqueVacCount = $data->unique('vac_count')->values()->pluck('vac_count');
            $labels = $uniqueVacCount->map(fn($v) => $labelAvailable[$v]);

            $publicFiltered = $data->filter(fn($row) => $row->patient_type === 1)->values();
            $staffFiltered = $data->filter(fn($row) => $row->patient_type === 2)->values();
            $all = [];
            $public = [];
            $staff = [];
            foreach ($uniqueVacCount as $count) {
                foreach ($results as $result) {
                    $public[$result][] = $publicFiltered->where('vac_count', $count)->where('lab_result_stored', $result)->first()?->cases ?? 0;
                    $staff[$result][] = $staffFiltered->where('vac_count', $count)->where('lab_result_stored', $result)->first()?->cases ?? 0;
                    $all[$result][] = ($publicFiltered->where('vac_count', $count)->where('lab_result_stored', $result)->first()?->cases ?? 0)
                        + ($staffFiltered->where('vac_count', $count)->where('lab_result_stored', $result)->first()?->cases ?? 0);
                }
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    'allPos' => $all['Detected'],
                    'publicPos' => $public['Detected'],
                    'staffPos' => $staff['Detected'],
                    'allNeg' => $all['Not detected'],
                    'publicNeg' => $public['Not detected'],
                    'staffNeg' => $staff['Not detected'],
                ],
                'categories' => collect($labels)->map(fn($d, $i) => ['label' => $d, 'index' => $i, 'show' => true]),
            ];
        });
    }
}
