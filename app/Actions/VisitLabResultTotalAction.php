<?php

namespace App\Actions;

use Exception;
use Illuminate\Support\Facades\DB;

class VisitLabResultTotalAction
{
    /**
     * @throws Exception
     */
    public function __invoke($start, $end): array
    {
        return cache()->remember("lab-result-total-$start-$end", now()->addDay(), function () use ($start, $end) {
            cache()->increment('lab-result-total-query-count');
            $labels = ['Detected', 'Not detected', 'Inconclusive'];
            $data = DB::table('visits')
                ->selectRaw('COUNT(*) as cases, patient_type, lab_result_stored')
                ->where('status', 4)
                ->where('swabbed', true)
                ->where('date_visit', '>=', $start)
                ->where('date_visit', '<=', $end)
                ->whereIn('lab_result_stored', $labels)
                ->groupBy('patient_type')
                ->groupBy('lab_result_stored')
                ->get();

            $publicFiltered = $data->filter(fn($row) => $row->patient_type === 1)->values();
            $staffFiltered = $data->filter(fn($row) => $row->patient_type === 2)->values();

            $all = [];
            $public = [];
            $staff = [];
            foreach ($labels as $result) {
                $public[] = $publicFiltered->where('lab_result_stored', $result)->first()?->cases ?? 0;
                $staff[] = $staffFiltered->where('lab_result_stored', $result)->first()?->cases ?? 0;
                $all[] = ($publicFiltered->where('lab_result_stored', $result)->first()?->cases ?? 0)
                    + ($staffFiltered->where('lab_result_stored', $result)->first()?->cases ?? 0);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    'all' => $all,
                    'public' => $public,
                    'staff' => $staff,
                ],
            ];
        });
    }
}
