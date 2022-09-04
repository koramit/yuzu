<?php

namespace App\Actions;

use App\Models\VisitStat;
use App\Traits\VisitStatQueryable;
use Exception;

class VisitServiceStatAction
{
    use VisitStatQueryable;
    /**
     * @throws Exception
     */
    public function __invoke($start, $end)
    {
        return cache()->remember("service-chart-$start-$end", now()->addDay(), function () use($start, $end) {
            $all = $this->query($start, $end, true);
            $staff = $this->query($start, $end, false, 2)->toArray();
            $public = $this->query($start, $end, false, 1)->toArray();
            $publicSwab = $this->query($start, $end, false, 1, 1)->toArray();
            $labels = array_keys($all->toArray());
            $all = array_values($all->toArray());

            return [
                'labels' => $labels,
                'datasets' => [
                    'all' => $all,
                    'staff' => $staff,
                    'public' => $public,
                    'publicSwab' => $publicSwab,
                ],
            ];
        });
    }

    protected function useModel($start, $end)
    {
        // low query more memory
        $stat = VisitStat::query()
            ->selectRaw('COUNT(*) as cases, date_visit, patient_type, swabbed')
            ->where('status', 4)
            ->where('date_visit', '>=', $start)
            ->where('date_visit', '<=', $end)
            ->orderBy('date_visit')
            ->groupBy('date_visit')
            ->groupBy('patient_type')
            ->groupBy('swabbed')
            ->get();

        $data = [];
        foreach ($stat as $item) {
            $day = $item['date_visit']->format('d M y');
            if (! isset($data[$day])) {
                $data[$day]['all'] = 0;
                $data[$day]['public'] = 0;
                $data[$day]['public_swab'] = 0;
                $data[$day]['staff'] = 0;
                $data[$day]['staff_swab'] = 0;
            }
            if ($item['patient_type'] === 1 ) {
                if ($item['swabbed']) {
                    $data[$day]['public_swab'] = $data[$day]['public_swab'] + $item['cases'];
                } else {
                    $data[$day]['public'] = $data[$day]['public'] + $item['cases'];
                }
            } else {
                if ($item['swabbed']) {
                    $data[$day]['staff_swab'] = $data[$day]['staff_swab'] + $item['cases'];
                } else {
                    $data[$day]['staff'] = $data[$day]['staff'] + $item['cases'];
                }
            }
            $data[$day]['all'] = $data[$day]['all'] + $item['cases'];
        }
        $labels = array_keys($data);
        $all = [];
        $staff = [];
        $public = [];
        $publicSwab = [];
        foreach ($data as $day => $group) {
            $all[] = $group['all'];
            $staff[] = $group['staff'] + $group['staff_swab'];
            $public[] = $group['public'] + $group['public_swab'];
            $publicSwab[] = $group['public_swab'];
        }
        return [
            'labels' => $labels,
            'datasets' => [
                'all' => $all,
                'staff' => $staff,
                'public' => $public,
                'publicSwab' => $publicSwab,
            ],
        ];
    }
}