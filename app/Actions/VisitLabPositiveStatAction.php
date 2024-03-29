<?php

namespace App\Actions;

use App\Traits\VisitStatQueryable;
use Exception;

class VisitLabPositiveStatAction
{
    use VisitStatQueryable;

    /**
     * @throws Exception
     */
    public function __invoke($start, $end): array
    {
        return cache()->remember("lab-positive-$start-$end", now()->addDay(), function () use ($start, $end) {
            cache()->increment('lab-positive-query-count');
            $all = $this->query($start, $end, true, null, 1, 'Detected');
            $labels = array_keys($all->toArray());
            $all = array_values($all->toArray());

            $data = $this->query($start, $end, true, null, 1, 'Detected', false);
            $allSymptom = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, null, 1, 'Detected', null, true);
            $allVaccinated = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 1, 1, 'Detected');
            $public = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 2, 1, 'Detected');
            $staff = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 1, 1, 'Detected', true);
            $publicAsymptom = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 1, 1, 'Detected', null, false);
            $publicUnvaccinated = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 2, 1, 'Detected', true);
            $staffAsymptom = $this->fillZero($data, $labels);

            $map = [
                'all' => collect($all),
                'allVaccinated' => collect($allVaccinated),
                'allSymptom' => collect($allSymptom),
                'public' => collect($public),
                'publicAsymptom' => collect($publicAsymptom),
                'publicUnvaccinated' => collect($publicUnvaccinated),
                'staff' => collect($staff),
                'staffAsymptom' => collect($staffAsymptom),
            ];

            $aggregates = [];
            $datasets = [];
            $this->calStats($map,$datasets, $aggregates);

            return [
                'labels' => $labels,
                'datasets' => $datasets,
                'aggregates' => $aggregates,
            ];
        });
    }
}
